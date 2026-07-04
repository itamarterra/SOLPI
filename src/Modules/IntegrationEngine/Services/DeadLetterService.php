<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Services;

use RuntimeException;
use SOLPI\Modules\IntegrationEngine\Repositories\DeadLetterRepository;

final class DeadLetterService
{
    private DeadLetterRepository $dead;
    private QueueService $queue;

    public function __construct()
    {
        $this->dead = new DeadLetterRepository();
        $this->queue = new QueueService();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function list(int $limit = 50): array
    {
        return $this->dead->list($limit);
    }

    /**
     * @return array<string,mixed>
     */
    public function replay(int $id): array
    {
        $item = $this->dead->find($id);
        if (!is_array($item)) {
            throw new RuntimeException('Dead letter item not found.');
        }

        $payload = [];
        if (isset($item['payload']) && is_string($item['payload'])) {
            $decoded = json_decode($item['payload'], true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        $jobId = $this->queue->push(
            'replay:' . (string)($item['name'] ?? 'integration'),
            $payload,
            (string)($item['handler'] ?? 'IntegrationEngineWorker@process')
        );

        $this->dead->markReplayed($id);

        return [
            'status' => 'replayed',
            'dead_letter_id' => $id,
            'new_job_id' => $jobId,
        ];
    }
}
