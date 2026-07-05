<?php

declare(strict_types=1);

if (!defined('GLPI_ROOT')) {
    exit;
}

/**
 * Carrega o autoload do SOLPI de forma segura (sem conflito com GLPI_ROOT).
 * Chamado dentro de cada função de hook para garantir as classes SOLPI.
 */
function solpi_load_autoload(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $autoload = __DIR__ . '/vendor/autoload.php';
    if (is_file($autoload)) {
        require_once $autoload;
    }
    $loaded = true;
}

/**
 * Hook: disparado quando o técnico adiciona uma solução ao ticket.
 * Equivale ao "Adicionar Solução / Pedir Aprovação" no GLPI.
 * Envia mensagem WhatsApp SIM/NAO ao solicitante.
 */
function plugin_solpi_on_solution_added(array $params): bool
{
    $item = $params['item'] ?? null;

    if (!($item instanceof ITILSolution)) {
        return true;
    }

    if (($item->fields['itemtype'] ?? '') !== 'Ticket') {
        return true;
    }

    $glpiTicketId = (int)($item->fields['items_id'] ?? 0);

    if ($glpiTicketId === 0) {
        return true;
    }

    solpi_load_autoload();

    try {
        $ticketRepo  = new SOLPI\Modules\Tickets\TicketRepository();
        $solpiTicket = $ticketRepo->findByGLPITicketId($glpiTicketId);

        if ($solpiTicket === null) {
            return true;
        }

        if (in_array($solpiTicket['status'], ['AWAITING_CONFIRMATION', 'AWAITING_RATING', 'RATED'], true)) {
            return true;
        }

        $phone = $ticketRepo->getPhoneForTicket((int)$solpiTicket['id']);

        if ($phone === null) {
            return true;
        }

        $ticketRepo->updateStatus((int)$solpiTicket['id'], 'AWAITING_CONFIRMATION');

        $config   = new SOLPI\Core\Config();
        $config->load();
        $evolution = new SOLPI\Integrations\Evolution\EvolutionClient(
            $config->get('evolution', [])
        );

        $solution = trim(strip_tags($item->fields['content'] ?? 'Problema resolvido pelo suporte.'));
        $solution = mb_strimwidth($solution, 0, 200, '...');

        $msg = "Olá! O seu chamado *#{$glpiTicketId}* foi resolvido pela equipe de suporte.\n\n"
             . "*Solução:* {$solution}\n\n"
             . "O problema foi resolvido?\n\n"
             . "*SIM* — confirmar e encerrar o chamado\n"
             . "*NÃO* — continuar o atendimento";

        $evolution->sendText($phone, $msg);

        $whatsappRepo = new SOLPI\Modules\WhatsApp\WhatsAppRepository();
        $whatsappRepo->saveMessage($phone, 'OUTBOUND', $msg, 'SENT', (int)$solpiTicket['id']);

    } catch (Throwable $e) {
        file_put_contents(
            GLPI_LOG_DIR . '/solpi_hook_error.log',
            date('[Y-m-d H:i:s] ') . $e->getMessage() . ' in ' . basename($e->getFile()) . ':' . $e->getLine() . "\n",
            FILE_APPEND
        );
    }

    return true;
}

/**
 * Fallback: mudança manual de status para Resolvido (sem solução via ITILSolution).
 */
function plugin_solpi_on_ticket_update(array $params): bool
{
    $item = $params['item'] ?? null;

    if (!($item instanceof Ticket)) {
        return true;
    }

    $newStatus = (int)($item->fields['status'] ?? 0);
    if ($newStatus !== Ticket::SOLVED) {
        return true;
    }

    $glpiTicketId = (int)$item->getID();

    solpi_load_autoload();

    try {
        $ticketRepo  = new SOLPI\Modules\Tickets\TicketRepository();
        $solpiTicket = $ticketRepo->findByGLPITicketId($glpiTicketId);

        if ($solpiTicket === null) {
            return true;
        }

        if (in_array($solpiTicket['status'], ['AWAITING_CONFIRMATION', 'AWAITING_RATING', 'RATED'], true)) {
            return true;
        }

        $phone = $ticketRepo->getPhoneForTicket((int)$solpiTicket['id']);

        if ($phone === null) {
            return true;
        }

        $ticketRepo->updateStatus((int)$solpiTicket['id'], 'AWAITING_CONFIRMATION');

        $config   = new SOLPI\Core\Config();
        $config->load();
        $evolution = new SOLPI\Integrations\Evolution\EvolutionClient(
            $config->get('evolution', [])
        );

        $msg = "Olá! O seu chamado *#{$glpiTicketId}* foi resolvido pela equipe de suporte.\n\n"
             . "O problema foi resolvido?\n\n"
             . "*SIM* — confirmar e encerrar o chamado\n"
             . "*NÃO* — continuar o atendimento";

        $evolution->sendText($phone, $msg);

        $whatsappRepo = new SOLPI\Modules\WhatsApp\WhatsAppRepository();
        $whatsappRepo->saveMessage($phone, 'OUTBOUND', $msg, 'SENT', (int)$solpiTicket['id']);

    } catch (Throwable $e) {
        file_put_contents(
            GLPI_LOG_DIR . '/solpi_hook_error.log',
            date('[Y-m-d H:i:s] ') . $e->getMessage() . "\n",
            FILE_APPEND
        );
    }

    return true;
}