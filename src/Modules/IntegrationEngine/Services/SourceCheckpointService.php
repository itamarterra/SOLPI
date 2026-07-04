<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Services;

use SOLPI\Modules\IntegrationEngine\Repositories\SourceCheckpointRepository;

final class SourceCheckpointService
{
    private SourceCheckpointRepository $repository;

    public function __construct()
    {
        $this->repository = new SourceCheckpointRepository();
    }

    /**
     * @return array<string,mixed>|null
     */
    public function get(string $source, string $adapter, string $name = 'default'): ?array
    {
        return $this->repository->find($this->normalize($source), $this->normalize($adapter), $this->normalizeName($name));
    }

    /**
     * @param array<string,mixed> $metadata
     * @return array<string,mixed>
     */
    public function set(string $source, string $adapter, string $name, string $lastValue, array $metadata = []): array
    {
        $source = $this->normalize($source);
        $adapter = $this->normalize($adapter);
        $name = $this->normalizeName($name);

        $id = $this->repository->upsert($source, $adapter, $name, $lastValue, $metadata);
        $item = $this->repository->find($source, $adapter, $name);

        return [
            'status' => 'saved',
            'id' => $id,
            'item' => $item,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function reset(string $source, string $adapter, string $name = 'default'): array
    {
        $source = $this->normalize($source);
        $adapter = $this->normalize($adapter);
        $name = $this->normalizeName($name);

        $this->repository->delete($source, $adapter, $name);

        return [
            'status' => 'deleted',
            'source' => $source,
            'adapter' => $adapter,
            'name' => $name,
        ];
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function list(string $source, ?string $adapter = null, int $limit = 100): array
    {
        $adapter = is_string($adapter) && trim($adapter) !== '' ? $this->normalize($adapter) : null;
        return $this->repository->list($this->normalize($source), $adapter, $limit);
    }

    private function normalize(string $value): string
    {
        return strtolower(trim($value));
    }

    private function normalizeName(string $value): string
    {
        $value = trim($value);
        return $value !== '' ? $value : 'default';
    }
}
