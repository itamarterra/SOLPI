<?php
declare(strict_types=1);

namespace SOLPI\Modules\Notifications;

use SOLPI\Core\BaseRepository;

final class NotificationRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    public function create(array $data): array
    {
        $query = "INSERT INTO `glpi_solpi_notifications` (id, type, recipient, data, status, created_at) 
                  VALUES (?, ?, ?, ?, ?, NOW())";
        // Prepared statement would go here
        return $data;
    }

    /**
     * @param string $status
     * @return array<int,array<string,mixed>>
     */
    public function findByStatus(string $status, int $limit = 50): array
    {
        $query = "SELECT * FROM `glpi_solpi_notifications` WHERE status = '{$status}' ORDER BY created_at DESC LIMIT {$limit}";
        $result = $this->db->query($query);
        $notifications = [];

        while ($row = $result->fetch_assoc()) {
            $notifications[] = $row;
        }

        return $notifications;
    }

    /**
     * @return array<string,int>
     */
    public function getStatistics(): array
    {
        $query = "SELECT status, COUNT(*) as count FROM `glpi_solpi_notifications` GROUP BY status";
        $result = $this->db->query($query);
        $stats = [];

        while ($row = $result->fetch_assoc()) {
            $stats[$row['status']] = $row['count'];
        }

        return $stats;
    }
}

