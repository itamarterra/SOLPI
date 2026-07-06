<?php

declare(strict_types=1);

if (!defined('GLPI_ROOT')) {
    exit;
}

/**
 * Carrega o autoload do SOLPI de forma segura (sem conflito com GLPI_ROOT).
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
 */
function plugin_solpi_on_solution_added($item): bool
{
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
        error_log("SOLPI Hook Error: " . $e->getMessage());
    }

    return true;
}

/**
 * Hook: Indexa o ticket no Relationship Engine do SOLPI
 */
function plugin_solpi_index_ticket($item): void
{
    if (!($item instanceof Ticket)) {
        return;
    }

    $ticketId = (int)$item->getID();
    if ($ticketId <= 0) {
        return;
    }

    solpi_load_autoload();

    try {
        $manager = new \SOLPI\Modules\Intelligence\Services\RelationshipManager();
        $manager->indexTicket($ticketId);
    } catch (Throwable $e) {
        // Log silencioso
        error_log("SOLPI Indexing Error: " . $e->getMessage());
    }
}
