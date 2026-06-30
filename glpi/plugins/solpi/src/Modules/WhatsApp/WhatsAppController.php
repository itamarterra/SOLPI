<?php

declare(strict_types=1);

namespace SOLPI\Modules\WhatsApp;

use SOLPI\Modules\Tickets\TicketRepository;
use SOLPI\Modules\Tickets\TicketService;
use SOLPI\Integrations\Evolution\EvolutionClient;
use SOLPI\Core\Config;

final class WhatsAppController
{
    private TicketService    $ticketService;
    private TicketRepository $ticketRepo;
    private WhatsAppRepository $whatsappRepo;
    private EvolutionClient  $evolution;

    public function __construct()
    {
        $this->ticketService  = new TicketService();
        $this->ticketRepo     = new TicketRepository();
        $this->whatsappRepo   = new WhatsAppRepository();

        $config = new Config();
        $config->load();
        $this->evolution = new EvolutionClient($config->get('evolution', []));
    }

    public function handleWebhook(array $payload): array
    {
        $event = $payload['event'] ?? '';

        if ($event !== 'messages.upsert') {
            return ['status' => 'ignored', 'reason' => "event={$event}"];
        }

        $data = $payload['data'] ?? [];
        $key  = $data['key'] ?? [];

        if (($key['fromMe'] ?? false) === true) {
            return ['status' => 'ignored', 'reason' => 'fromMe=true'];
        }

        $remoteJid = $key['remoteJid'] ?? '';
        $phone     = preg_replace('/@.*/', '', $remoteJid);

        if (empty($phone) || str_contains($remoteJid, '@g.us')) {
            return ['status' => 'ignored', 'reason' => 'invalid or group'];
        }

        $text = $this->extractText($data['message'] ?? []);
        if (empty($text)) {
            return ['status' => 'ignored', 'reason' => 'no text'];
        }

        $name  = $data['pushName'] ?? 'Usuario WhatsApp';
        $reply = mb_strtoupper(trim($text));
        if (preg_match('/^[1-5]$/', trim($text))) {
            $pending = $this->ticketRepo->findByPhone($phone);
            if ($pending !== null && $pending['status'] === 'AWAITING_RATING') {
                return $this->handleRatingReply((int)trim($text), $phone, $pending);
            }
        }

        // Detecta SIM/NAO — independente do status do ticket
        $isSim = ($reply === 'SIM' || str_starts_with($reply, 'SIM'));
        $isNao = ($reply === 'NAO' || $reply === 'NÃO'
               || str_starts_with($reply, 'NAO')
               || str_starts_with($reply, 'NÃO'));

        if ($isSim || $isNao) {
            $pending = $this->ticketRepo->findByPhone($phone);

            if ($pending !== null && in_array($pending['status'], ['OPEN', 'AWAITING_CONFIRMATION'], true)) {
                return $this->handleResolutionReply($text, $phone, $pending);
            }

            $this->evolution->sendText(
                $phone,
                'Nao ha chamados abertos para o seu numero. Descreva o seu problema para abrir um novo chamado.'
            );
            return ['status' => 'ignored', 'reason' => 'no open ticket for sim/nao reply'];
        }

        // Qualquer outra mensagem: abre novo chamado
        $result = $this->ticketService->createTicketFromWhatsApp($phone, $text, $name);

        return [
            'status'          => 'ticket_created',
            'glpi_ticket_id'  => $result['glpi_ticket_id'],
            'solpi_ticket_id' => $result['solpi_ticket_id'],
        ];
    }

    private function handleResolutionReply(string $text, string $phone, array $ticket): array
    {
        $reply   = mb_strtoupper(trim($text));
        $glpiId  = (int)$ticket['glpi_ticket_id'];
        $solpiId = (int)$ticket['id'];

        if ($reply === 'SIM' || str_starts_with($reply, 'SIM')) {

            $this->ticketRepo->closeGLPITicket($glpiId);
            $this->ticketRepo->updateStatus($solpiId, 'AWAITING_RATING');

            // Confirmacao de fechamento
            $confirm = "Otimo! Chamado *#{$glpiId}* encerrado com sucesso!\n\n"
                     . "Agora, como voce avalia o nosso atendimento?\n"
                     . "Toque em uma das opcoes abaixo:";

            $this->evolution->sendText($phone, $confirm);
            $this->whatsappRepo->saveMessage($phone, 'OUTBOUND', $confirm, 'SENT', $solpiId);

            // Envia os botoes de avaliacao (2 mensagens: notas 1-3 e 4-5)
            $this->evolution->sendRatingButtons($phone);

            return ['status' => 'ticket_closed_awaiting_rating', 'glpi_ticket_id' => $glpiId];
        }

        if ($reply === 'NAO' || $reply === 'NÃO' || str_starts_with($reply, 'NAO') || str_starts_with($reply, 'NÃO')) {

            $this->ticketRepo->reopenGLPITicket($glpiId);
            $this->ticketRepo->updateStatus($solpiId, 'OPEN');

            $msg = "Entendido! Chamado *#{$glpiId}* reaberto. Nossa equipe continuara trabalhando para resolver o seu problema. Em breve entraremos em contato!";

            $this->evolution->sendText($phone, $msg);
            $this->whatsappRepo->saveMessage($phone, 'OUTBOUND', $msg, 'SENT', $solpiId);

            return ['status' => 'ticket_reopened', 'glpi_ticket_id' => $glpiId];
        }

        $msg = "Por favor, responda apenas:\n*SIM* — confirmar que o problema foi resolvido\n*NAO* — continuar o atendimento";
        $this->evolution->sendText($phone, $msg);

        return ['status' => 'awaiting_valid_reply'];
    }

    private function handleRatingReply(int $rating, string $phone, array $ticket): array
    {
        $solpiId = (int)$ticket['id'];
        $glpiId  = (int)$ticket['glpi_ticket_id'];

        $this->ticketRepo->saveRating($solpiId, $rating);

        $stars = str_repeat('⭐', $rating);
        $labels = [1 => 'Pessimo', 2 => 'Ruim', 3 => 'Regular', 4 => 'Bom', 5 => 'Excelente'];
        $label  = $labels[$rating] ?? '';

        $msg = "Obrigado pela avaliacao! {$stars} *{$label}*\n\n"
             . "Sua opiniao e muito importante para nos melhorarmos continuamente.\n"
             . "Se precisar de suporte novamente, e so nos enviar uma mensagem. Ate logo!";

        $this->evolution->sendText($phone, $msg);
        $this->whatsappRepo->saveMessage($phone, 'OUTBOUND', $msg, 'SENT', $solpiId);

        return ['status' => 'rated', 'rating' => $rating, 'glpi_ticket_id' => $glpiId];
    }

    private function extractText(array $message): string
    {
        // Texto simples
        if (!empty($message['conversation'])) {
            return trim($message['conversation']);
        }

        // Texto estendido
        if (!empty($message['extendedTextMessage']['text'])) {
            return trim($message['extendedTextMessage']['text']);
        }

        // Resposta de botao clicavel (buttonId = "1"-"5")
        if (!empty($message['buttonsResponseMessage']['selectedButtonId'])) {
            return trim($message['buttonsResponseMessage']['selectedButtonId']);
        }

        // Resposta de mensagem interativa (Evolution API v2)
        if (!empty($message['interactiveResponseMessage'])) {
            $native = $message['interactiveResponseMessage']['nativeFlowResponseMessage'] ?? [];
            if (!empty($native['paramsJson'])) {
                $params = json_decode($native['paramsJson'], true);
                $id = $params['id'] ?? $params['rowId'] ?? '';
                if ($id !== '') {
                    return trim((string)$id);
                }
            }
        }

        // Legenda de midia
        foreach (['imageMessage', 'videoMessage', 'documentMessage'] as $type) {
            if (!empty($message[$type]['caption'])) {
                return trim($message[$type]['caption']);
            }
        }

        return '';
    }
}