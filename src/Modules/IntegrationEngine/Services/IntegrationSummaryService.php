<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Services;

use RuntimeException;

final class IntegrationSummaryService
{
    private object $db;
    private GovernanceService $governance;
    private QueueService $queue;
    private IntegrationSummaryCalculator $calculator;

    public function __construct()
    {
        global $DB;

        if (!is_object($DB)) {
            throw new RuntimeException('Conexao com o banco do GLPI nao encontrada.');
        }

        $this->db = $DB;
        $this->governance = new GovernanceService();
        $this->queue = new QueueService();
        $this->calculator = new IntegrationSummaryCalculator();
    }

    /**
     * @return array<string,mixed>
     */
    public function summary(): array
    {
        $jobsTotal = $this->count('glpi_plugin_solpi_jobs');
        $jobsPending = $this->count('glpi_plugin_solpi_jobs', ['status' => 'PENDING']);
        $jobsDone = $this->count('glpi_plugin_solpi_jobs', ['status' => 'DONE']);
        $jobsRunning = $this->count('glpi_plugin_solpi_jobs', ['status' => 'RUNNING']);
        $jobsDead = $this->count('glpi_plugin_solpi_jobs', ['status' => 'DEAD']);

        $reviewPending = $this->count('glpi_plugin_solpi_review_queue', ['status' => 'PENDING']);
        $reviewTotal = $this->count('glpi_plugin_solpi_review_queue');

        $deadLetterTotal = $this->count('glpi_plugin_solpi_dead_letter');
        $deadLetterDead = $this->count('glpi_plugin_solpi_dead_letter', ['status' => 'DEAD']);
        $deadLetterReplayed = $this->count('glpi_plugin_solpi_dead_letter', ['status' => 'REPLAYED']);

        $checkpointTotal = $this->count('glpi_plugin_solpi_source_checkpoints');
        $qualityReports = $this->governance->recentQualityReports(1);
        $latestQualityReport = $qualityReports[0] ?? null;
        $batchSummary = $this->calculator->summarizeJobs($this->queue->recent(200));

        return [
            'status' => 'ok',
            'generated_at' => date(DATE_ATOM),
            'runtime' => [
                'php_version' => PHP_VERSION,
            ],
            'jobs' => [
                'total' => $jobsTotal,
                'pending' => $jobsPending,
                'running' => $jobsRunning,
                'done' => $jobsDone,
                'dead' => $jobsDead,
            ],
            'batches' => $batchSummary,
            'review_queue' => [
                'total' => $reviewTotal,
                'pending' => $reviewPending,
            ],
            'dead_letter' => [
                'total' => $deadLetterTotal,
                'dead' => $deadLetterDead,
                'replayed' => $deadLetterReplayed,
            ],
            'checkpoints' => [
                'total' => $checkpointTotal,
            ],
            'quality' => [
                'latest_report' => $latestQualityReport,
            ],
        ];
    }

    private function count(string $table, array $where = []): int
    {
        foreach ($this->db->request([
            'COUNT' => 'c',
            'FROM' => $table,
            'WHERE' => $where,
        ]) as $row) {
            return (int)($row['c'] ?? 0);
        }

        return 0;
    }
}