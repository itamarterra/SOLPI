<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\DTO;

final class ResolutionResult
{
    /**
     * @param array<int,array<string,mixed>> $keys
     */
    public function __construct(
        public string $entityType,
        public string $canonicalId,
        public bool $matched,
        public float $confidence,
        public int $candidateCount,
        public array $keys
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'entity_type' => $this->entityType,
            'canonical_id' => $this->canonicalId,
            'matched' => $this->matched,
            'confidence' => $this->confidence,
            'candidate_count' => $this->candidateCount,
            'keys' => $this->keys,
        ];
    }
}
