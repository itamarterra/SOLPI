<?php

declare(strict_types=1);

namespace SOLPI\Modules\Infrastructure\Services;

use Throwable;

/**
 * Serviço de Automação de Tickets Proativos.
 */
final class TicketAutomationService
{
    /**
     * Abre um chamado automático no GLPI.
     */
    public function createIncidentTicket(string $assetName, string $ip, string $class): int
    {
        // Usando as classes globais do GLPI com backslash
        $ticket = new \Ticket();

        $input = [
            'name'             => "SOLPI [INCIDENTE] - Falha em {$assetName}",
            'content'          => "O sistema SOLPI detectou que o ativo {$assetName} ({$ip}) [{$class}] está OFFLINE.",
            'type'             => 1, // 1 = INCIDENT (Padrão GLPI)
            'requesttypes_id'  => 1, // 1 = Direto / Manual
            'priority'         => 4, // 4 = Alta
            'urgency'          => 4,
            'impact'           => 4,
            'entities_id'      => 0,
        ];

        if (isset($_SESSION['glpiID'])) {
            $input['_users_id_recipient'] = $_SESSION['glpiID'];
        }

        // Tenta adicionar o ticket usando a API interna do GLPI
        $ticketId = $ticket->add($input);

        if (!$ticketId) {
            error_log("SOLPI ERROR: Falha ao abrir ticket automático para $assetName.");
        }

        return (int)($ticketId ?: 0);
    }
}
