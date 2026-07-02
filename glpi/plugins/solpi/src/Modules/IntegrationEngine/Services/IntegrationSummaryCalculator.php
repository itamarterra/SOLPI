<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Services;

final class IntegrationSummaryCalculator
{
    /**
     * @param array<int,array<string,mixed>> $jobs
     * @return array<string,mixed>
     */
    public function summarizeJobs(array $jobs): array
    {
        $withMeta = 0;
        $truncatedJobs = 0;
        $checkpointJobs = 0;
        $recordsTotal = 0;
        $recordsQueued = 0;
        $recordsDuplicate = 0;
        $batchCountMax = 0;
        $batchTotalMax = 0;
        $batchSizeMax = 0;

        foreach ($jobs as $job) {
            $payload = is_array($job['payload'] ?? null) ? $job['payload'] : [];
            $meta = is_array($payload['_queue_meta'] ?? null) ? $payload['_queue_meta'] : [];

            if ($meta === []) {
                continue;
            }

            $withMeta++;
            $batchCountMax = max($batchCountMax, (int)($meta['batch_count'] ?? 0));
            $batchTotalMax = max($batchTotalMax, (int)($meta['batch_total'] ?? 0));
            $batchSizeMax = max($batchSizeMax, (int)($meta['batch_size'] ?? 0));
            $recordsTotal += (int)($meta['records_total'] ?? 0);
            $recordsQueued += (int)($meta['records_queued'] ?? 0);
            $recordsDuplicate += (int)($meta['records_duplicate'] ?? 0);

            if (!empty($meta['truncated'])) {
                $truncatedJobs++;
            }

            if (!empty($meta['checkpoint_enabled'])) {
                $checkpointJobs++;
            }
        }

        return [
            'jobs_with_meta' => $withMeta,
            'truncated_jobs' => $truncatedJobs,
            'checkpoint_jobs' => $checkpointJobs,
            'records_total' => $recordsTotal,
            'records_queued' => $recordsQueued,
            'records_duplicate' => $recordsDuplicate,
            'batch_count_max' => $batchCountMax,
            'batch_total_max' => $batchTotalMax,
            'batch_size_max' => $batchSizeMax,
        ];
    }
}