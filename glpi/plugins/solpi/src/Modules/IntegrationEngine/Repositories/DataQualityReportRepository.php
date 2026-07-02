<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Repositories;

use DBmysql;
use RuntimeException;

final class DataQualityReportRepository
{
    private DBmysql $db;

    public function __construct()
    {
        global $DB;
        if (!$DB instanceof DBmysql) {
            throw new RuntimeException('Conexao com o banco do GLPI nao encontrada.');
        }
        $this->db = $DB;
    }

    /**
     * @param array<string,mixed> $details
     */
    public function create(string $scope, int $total, int $valid, int $review, int $dead, float $qualityScore, array $details = []): int
    {
        $this->db->insert('glpi_plugin_solpi_data_quality_reports', [
            'scope' => $scope,
            'records_total' => $total,
            'records_valid' => $valid,
            'records_review' => $review,
            'records_dead' => $dead,
            'quality_score' => $qualityScore,
            'details' => json_encode($details, JSON_UNESCAPED_UNICODE),
        ]);

        return (int)$this->db->insertId();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function recent(int $limit = 30): array
    {
        $items = [];
        foreach ($this->db->request([
            'FROM' => 'glpi_plugin_solpi_data_quality_reports',
            'ORDER' => 'id DESC',
            'LIMIT' => max(1, min(200, $limit)),
        ]) as $row) {
            $items[] = $row;
        }

        return $items;
    }
}
