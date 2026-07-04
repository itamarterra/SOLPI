<?php
declare(strict_types=1);

namespace SOLPI\Modules\AI;

use SOLPI\AI\AIKernel;
use SOLPI\AI\AIRepository;

final class AIService
{
    private AIKernel $kernel;
    private AIRepository $repository;

    public function __construct()
    {
        $this->kernel = new AIKernel();
        $this->repository = new AIRepository();
    }

    /**
     * @param array<string,mixed> $context
     * @return array<string,mixed>
     */
    public function processQuery(string $query, array $context = []): array
    {
        return $this->kernel->chat($query, $context);
    }

    /**
     * @return array<string,mixed>
     */
    public function getProviderStats(): array
    {
        return $this->repository->getProviderStats();
    }

    /**
     * @param array<string,mixed> $config
     * @return bool
     */
    public function updateConfiguration(array $config): bool
    {
        return $this->repository->saveConfiguration($config);
    }
}

