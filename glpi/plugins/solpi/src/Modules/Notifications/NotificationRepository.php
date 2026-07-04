<?php
declare(strict_types=1);

namespace SOLPI\Modules\Notifications;

use SOLPI\Core\BaseRepository;
use SOLPI\Core\Database\QueryBuilder;

final class NotificationRepository extends BaseRepository
{
    protected string $table = 'glpi_solpi_notifications';

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
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        $id = $this->insert($data);
        $data['id'] = $id;

        return $data;
    }

    /**
     * @param string $status
     * @param int $limit
     * @return array<int,array<string,mixed>>
     */
    public function findByStatus(string $status, int $limit = 50): array
    {
        $qb = new QueryBuilder($this->db);
        return $qb->from($this->table)
                  ->where(['status' => $status])
                  ->orderBy('created_at', 'DESC')
                  ->limit($limit)
                  ->execute();
    }

    /**
     * @return array<string,int>
     */
    public function getStatistics(): array
    {
        $query = "SELECT status, COUNT(*) as count FROM `{$this->table}` GROUP BY status";
        $stats = [];
        
        foreach ($this->db->request($query) as $row) {
            $stats[$row['status']] = (int)$row['count'];
        }

        return $stats;
    }
}

