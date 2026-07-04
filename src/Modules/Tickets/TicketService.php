<?php

declare(strict_types=1);

namespace SOLPI\Modules\Tickets;

use SOLPI\Modules\WhatsApp\WhatsAppRepository;
use SOLPI\Integrations\Evolution\EvolutionClient;
use SOLPI\Core\Config;

/**
 * Orquestra a criação de tickets a partir de mensagens WhatsApp.
 *
 * Fluxo:
 *   1. Usuário envia mensagem no WhatsApp
 *   2. Evolution API chama o webhook SOLPI
 *   3. TicketService cria o chamado no GLPI
 *   4. Salva a mensagem e o registro de sincronia
 *   5. Envia confirmação de volta ao usuário
 */
final class TicketService
{
    private TicketRepository   $tickets;
    private WhatsAppRepository $whatsapp;
    private EvolutionClient    $evolution;

    public function __construct()
    {
        $this->tickets  = new TicketRepository();
        $this->whatsapp = new WhatsAppRepository();

        $config = new Config();
        $config->load();

        $this->evolution = new EvolutionClient(
            $config->get('evolution', [])
        );
    }

    /**
     * Processa uma mensagem recebida do WhatsApp e cria o ticket no GLPI.
     *
     * @param string $phone   Número do remetente (5519981584722)
     * @param string $message Texto da mensagem
     * @param string $name    Nome do contato (pushName)
     *
     * @return array{glpi_ticket_id: int, solpi_ticket_id: int}
     */
    public function createTicketFromWhatsApp(
        string $phone,
        string $message,
        string $name = 'Usuário WhatsApp'
    ): array {

        // Título gerado automaticamente a partir do nome e início do texto
        $title = sprintf(
            '[WhatsApp] %s: %s',
            $name,
            mb_strimwidth(strip_tags($message), 0, 80, '...')
        );

        // Cria o chamado no GLPI
        $glpiTicketId = $this->tickets->createGLPITicket(
            title:   $title,
            content: $message,
            priority: 3
        );

        // Registra a sincronia no SOLPI
        $solpiTicketId = $this->tickets->createSyncRecord($glpiTicketId);

        // Salva a mensagem recebida vinculada ao ticket
        $this->whatsapp->saveMessage(
            phone:     $phone,
            direction: 'INBOUND',
            message:   $message,
            status:    'RECEIVED',
            ticketId:  $solpiTicketId
        );

        // Envia confirmação ao usuário via WhatsApp
        $confirmation = sprintf(
            "✅ *Chamado #%d aberto com sucesso!*\n\n" .
            "📋 *Problema:* %s\n\n" .
            "Nosso suporte irá analisá-lo em breve. " .
            "Você será notificado aqui quando houver atualizações.",
            $glpiTicketId,
            mb_strimwidth($message, 0, 120, '...')
        );

        $sent = $this->evolution->sendText($phone, $confirmation);

        // Salva a confirmação enviada
        $this->whatsapp->saveMessage(
            phone:     $phone,
            direction: 'OUTBOUND',
            message:   $confirmation,
            status:    ($sent['success'] ?? false) ? 'SENT' : 'FAILED',
            ticketId:  $solpiTicketId
        );

        return [
            'glpi_ticket_id'  => $glpiTicketId,
            'solpi_ticket_id' => $solpiTicketId,
        ];
    }
}