<?php

declare(strict_types=1);

namespace SOLPI\Modules\Infrastructure\Services;

use SOLPI\Core\Database\DatabaseManager;
use SOLPI\Modules\Discovery\Adapters\ICMPAdapter;
use SOLPI\Modules\Infrastructure\Services\TicketAutomationService;
use SOLPI\Modules\Infrastructure\Services\InfraProactiveAlertService;
use Throwable;

/**
 * Monitor de Saúde do Digital Twin.
 */
final class InfraHealthMonitor
{
    private DatabaseManager $db;
    private ICMPAdapter $icmp;
    private TicketAutomationService $ticketing;
    private InfraProactiveAlertService $alerts;

    public function __construct()
    {
        $this->db = DatabaseManager::getInstance();
        $this->icmp = new ICMPAdapter();
        $this->ticketing = new TicketAutomationService();
        $this->alerts = new InfraProactiveAlertService();
    }

    public function refreshGlobalStatus(): array
    {
        $nodes = $this->db->table('glpi_plugin_solpi_inframap_nodes')->get();
        $conn = $this->db->getConnection();
        $stats = ['online' => 0, 'offline' => 0, 'tickets_opened' => 0];

        foreach ($nodes as $node) {
            $meta = json_decode((string)$node['metadata'], true) ?: [];
            $ip = $meta['ip'] ?? null;

            if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
                $isOnline = (bool)$this->icmp->discover($ip);
                $oldStatus = $meta['status'] ?? 'UNKNOWN';

                // Lógica de Alerta Crítico: Transição ONLINE -> OFFLINE
                if (!$isOnline && $oldStatus === 'ONLINE') {
                    // 1. Abre Ticket no GLPI
                    $ticketId = $this->ticketing->createIncidentTicket($node['label'], $ip, $node['class']);

                    if ($ticketId > 0) {
                        $stats['tickets_opened']++;

                        // 2. Envia Alerta Urgente no WhatsApp
                        try {
                            $this->alerts->sendExecutiveAlert(null); // Usa o número salvo nas configurações
                        } catch (Throwable $e) {
                            error_log("SOLPI WA Error: " . $e->getMessage());
                        }
                    }
                }

                $meta['status'] = $isOnline ? 'ONLINE' : 'OFFLINE';
                $meta['last_health_check'] = date('Y-m-d H:i:s');

                $conn->update('glpi_plugin_solpi_inframap_nodes', [
                    'metadata' => json_encode($meta)
                ], ['uuid' => $node['uuid']]);

                $isOnline ? $stats['online']++ : $stats['offline']++;
            }
        }

        return $stats;
    }
}
