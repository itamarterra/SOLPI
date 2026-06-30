<?php

declare(strict_types=1);

namespace SOLPI\Modules\WhatsApp;

use DBmysql;
use RuntimeException;

/**
 * Repositório de mensagens WhatsApp.
 */
final class WhatsAppRepository
{
    private DBmysql $db;

    public function __construct()
    {
        global $DB;

        if (!$DB instanceof DBmysql) {
            throw new RuntimeException('Conexão com o banco do GLPI não encontrada.');
        }

        $this->db = $DB;
    }

    /**
     * Salva uma mensagem recebida ou enviada.
     *
     * @param string   $phone     Número no formato 5519981584722
     * @param string   $direction INBOUND | OUTBOUND
     * @param string   $message   Conteúdo da mensagem
     * @param string   $status    RECEIVED | SENT | FAILED
     * @param int|null $ticketId  ID do ticket SOLPI (se vinculado)
     */
    public function saveMessage(
        string  $phone,
        string  $direction,
        string  $message,
        string  $status   = 'RECEIVED',
        ?int    $ticketId = null
    ): int {

        $this->db->insert('glpi_plugin_solpi_whatsapp', [
            'ticket_id' => $ticketId,
            'phone'     => $phone,
            'direction' => $direction,
            'message'   => $this->db->escape($message),
            'status'    => $status,
            'sent_at'   => $direction === 'OUTBOUND' ? date('Y-m-d H:i:s') : null,
        ]);

        return (int)$this->db->insertId();
    }
}