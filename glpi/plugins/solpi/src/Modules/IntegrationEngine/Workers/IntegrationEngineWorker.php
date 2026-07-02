<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Workers;

use SOLPI\Modules\IntegrationEngine\EntityResolver\EntityResolverService;
use SOLPI\Modules\IntegrationEngine\MergeEngine\FieldMergeEngine;
use SOLPI\Modules\IntegrationEngine\Repositories\DeadLetterRepository;
use SOLPI\Modules\IntegrationEngine\Repositories\JobRepository;
use SOLPI\Modules\IntegrationEngine\Services\AuditService;
use SOLPI\Modules\IntegrationEngine\Services\DomainPersistenceService;
use SOLPI\Modules\IntegrationEngine\Services\KnowledgeGraphProjector;
use SOLPI\Modules\IntegrationEngine\Services\ReviewService;

final class IntegrationEngineWorker
{
    private JobRepository $jobs;
    private AuditService $audit;
    private EntityResolverService $resolver;
    private FieldMergeEngine $mergeEngine;
    private DomainPersistenceService $persistence;
    private ReviewService $reviews;
    private DeadLetterRepository $dead;
    private KnowledgeGraphProjector $graph;

    public function __construct()
    {
        $this->jobs = new JobRepository();
        $this->audit = new AuditService();
        $this->resolver = new EntityResolverService();
        $this->mergeEngine = new FieldMergeEngine();
        $this->persistence = new DomainPersistenceService();
        $this->reviews = new ReviewService();
        $this->dead = new DeadLetterRepository();
        $this->graph = new KnowledgeGraphProjector();
    }

    public function runOnce(int $limit = 20): int
    {
        $processed = 0;

        foreach ($this->jobs->pending($limit) as $job) {
            $jobId = (int)($job['id'] ?? 0);
            if ($jobId <= 0) {
                continue;
            }

            $this->jobs->markRunning($jobId);

            try {
                $payload = is_array($job['payload'] ?? null) ? $job['payload'] : [];
                $incoming = is_array($payload['payload'] ?? null) ? $payload['payload'] : [];

                if (isset($incoming['record']) && is_array($incoming['record'])) {
                    $incoming = $incoming['record'];
                }

                $entityType = (string)($incoming['entity_type'] ?? $payload['entity_type'] ?? '');
                if ($entityType !== '') {
                    $incoming['entity_type'] = $entityType;
                }

                $resolution = $this->resolver->resolve($incoming);

                $current = [];
                if (isset($payload['current']) && is_array($payload['current'])) {
                    $current = $payload['current'];
                }

                $merge = $this->mergeEngine->merge($current, $incoming, [
                    'entity_type' => $resolution['entity_type'] ?? 'unknown',
                    'canonical_id' => $resolution['canonical_id'] ?? null,
                    'correlation_id' => $payload['correlation_id'] ?? null,
                ]);

                $confidence = (float)($resolution['confidence'] ?? 0.0);
                $entityType = (string)($resolution['entity_type'] ?? 'unknown');

                if ($confidence < 65.0) {
                    $reviewId = $this->reviews->enqueue(
                        $entityType,
                        $confidence,
                        $incoming,
                        $resolution,
                        $merge['conflicts'] ?? [],
                        $payload['correlation_id'] ?? null
                    );

                    $this->audit->warning('Integration job routed to review queue.', [
                        'job_id' => $jobId,
                        'entity_type' => $entityType,
                        'confidence' => $confidence,
                        'review_id' => $reviewId,
                    ]);

                    $this->jobs->markSuccess($jobId);
                    $processed++;
                    continue;
                }

                $persisted = $this->persistence->persist(
                    $entityType,
                    is_array($merge['merged'] ?? null) ? $merge['merged'] : $incoming
                );

                $mergedPayload = is_array($merge['merged'] ?? null) ? $merge['merged'] : $incoming;
                $this->graph->project(
                    $entityType,
                    (string)($resolution['canonical_id'] ?? ''),
                    isset($persisted['id']) ? (int)$persisted['id'] : null,
                    $mergedPayload
                );

                $this->audit->info('Integration job processed.', [
                    'job_id' => $jobId,
                    'name' => $job['name'] ?? '',
                    'canonical_id' => $resolution['canonical_id'] ?? null,
                    'entity_type' => $resolution['entity_type'] ?? null,
                    'confidence' => $resolution['confidence'] ?? null,
                    'matched' => $resolution['matched'] ?? false,
                    'changes' => count($merge['changes'] ?? []),
                    'conflicts' => count($merge['conflicts'] ?? []),
                    'persist_action' => $persisted['action'] ?? null,
                    'persist_id' => $persisted['id'] ?? null,
                ]);

                $this->jobs->markSuccess($jobId);
                $processed++;
            } catch (\Throwable $e) {
                $this->jobs->markFailed($jobId, $e->getMessage());

                $nextAttempts = (int)($job['attempts'] ?? 0) + 1;
                $maxAttempts = (int)($job['max_attempts'] ?? 1);

                if ($nextAttempts >= $maxAttempts) {
                    $this->dead->create($job, $e->getMessage());
                }

                $this->audit->error('Integration job failed.', [
                    'job_id' => $jobId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $processed;
    }
}
