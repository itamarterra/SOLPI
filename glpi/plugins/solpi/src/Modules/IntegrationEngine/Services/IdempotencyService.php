<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Services;

use SOLPI\Modules\IntegrationEngine\Repositories\WebhookRepository;

final class IdempotencyService
{
    private WebhookRepository $webhooks;

    public function __construct()
    {
        $this->webhooks = new WebhookRepository();
    }

    public function isDuplicate(string $source, string $idempotencyKey): bool
    {
        return $this->webhooks->alreadyProcessed($source, $idempotencyKey);
    }

    /**
     * @param array<string,mixed> $payload
     */
    public function markReceived(string $source, string $idempotencyKey, array $payload): int
    {
        return $this->webhooks->register($source, $idempotencyKey, $payload, 'RECEIVED');
    }

    /**
     * @param array<string,mixed> $payload
     */
    public function markDuplicate(string $source, string $idempotencyKey, array $payload): int
    {
        return $this->webhooks->register($source, $idempotencyKey, $payload, 'DUPLICATE');
    }
}
