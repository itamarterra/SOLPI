<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Services;

use RuntimeException;
use SOLPI\Modules\IntegrationEngine\Repositories\DataQualityReportRepository;

final class GovernanceService
{
    private object $db;
    private DataQualityReportRepository $qualityReports;

    public function __construct()
    {
        global $DB;
        if (!is_object($DB)) {
            throw new RuntimeException('Conexao com o banco do GLPI nao encontrada.');
        }

        $this->db = $DB;
        $this->qualityReports = new DataQualityReportRepository();
    }

    /**
     * @return array<string,mixed>
     */
    public function generateQualityReport(): array
    {
        $total = $this->count('glpi_plugin_solpi_jobs');
        $valid = $this->count('glpi_plugin_solpi_jobs', ['status' => 'DONE']);
        $review = $this->count('glpi_plugin_solpi_review_queue', ['status' => 'PENDING']);
        $dead = $this->count('glpi_plugin_solpi_dead_letter', ['status' => 'DEAD']);

        $denominator = max(1, $total + $review + $dead);
        $quality = max(0.0, min(100.0, (($valid - ($review * 0.5) - $dead) / $denominator) * 100));

        $reportId = $this->qualityReports->create(
            'integration_engine',
            $total,
            $valid,
            $review,
            $dead,
            round($quality, 2),
            [
                'formula' => '(valid - review*0.5 - dead) / (total+review+dead) * 100',
            ]
        );

        return [
            'report_id' => $reportId,
            'records_total' => $total,
            'records_valid' => $valid,
            'records_review' => $review,
            'records_dead' => $dead,
            'quality_score' => round($quality, 2),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function runRetention(int $days = 90): array
    {
        $days = max(7, min(3650, $days));

        $deletedLogs = $this->deleteOlderThan('glpi_plugin_solpi_logs', $days);
        $deletedWebhooks = $this->deleteOlderThan('glpi_plugin_solpi_webhooks', $days);
        $deletedReports = $this->deleteOlderThan('glpi_plugin_solpi_data_quality_reports', $days);

        return [
            'retention_days' => $days,
            'deleted' => [
                'logs' => $deletedLogs,
                'webhooks' => $deletedWebhooks,
                'quality_reports' => $deletedReports,
            ],
        ];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function recentQualityReports(int $limit = 20): array
    {
        return $this->qualityReports->recent($limit);
    }

    private function count(string $table, array $where = []): int
    {
        foreach ($this->db->request([
            'COUNT' => 'c',
            'FROM' => $table,
            'WHERE' => $where,
        ]) as $row) {
            return (int)$row['c'];
        }

        return 0;
    }

    private function deleteOlderThan(string $table, int $days): int
    {
        $threshold = date('Y-m-d H:i:s', time() - ($days * 86400));
        $this->db->delete($table, [
            'created_at' => ['<', $threshold],
        ]);

        if (method_exists($this->db, 'affectedRows')) {
            return (int)$this->db->affectedRows();
        }

        return 0;
    }
}
