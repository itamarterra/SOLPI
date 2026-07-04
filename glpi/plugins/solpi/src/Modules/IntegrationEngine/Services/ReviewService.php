<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Services;

use SOLPI\Modules\IntegrationEngine\Repositories\ReviewQueueRepository;

final class ReviewService
{
    private ReviewQueueRepository $reviews;

    public function __construct()
    {
        $this->reviews = new ReviewQueueRepository();
    }

    /**
     * @param array<string,mixed> $payload
     * @param array<string,mixed> $resolution
     * @param array<int,array<string,mixed>> $conflicts
     */
    public function enqueue(string $entityType, float $confidence, array $payload, array $resolution, array $conflicts, ?string $correlationId = null): int
    {
        return $this->reviews->enqueue($entityType, $confidence, $payload, $resolution, $conflicts, $correlationId);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function pending(int $limit = 50): array
    {
        return $this->reviews->pending($limit);
    }

    public function approve(int $id, ?string $reason = null): void
    {
        $this->reviews->markReviewed($id, 'APPROVED', $reason);
    }

    public function reject(int $id, ?string $reason = null): void
    {
        $this->reviews->markReviewed($id, 'REJECTED', $reason);
    }
}
