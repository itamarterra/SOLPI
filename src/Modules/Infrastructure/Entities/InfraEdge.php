<?php

declare(strict_types=1);

namespace SOLPI\Modules\Infrastructure\Entities;

use JsonSerializable;

/**
 * Representa uma conexão (aresta) entre entidades da infraestrutura.
 */
final class InfraEdge implements JsonSerializable
{
    private string $sourceUuid;
    private string $targetUuid;
    private string $relationType; // PHYSICAL_LINK, DEPENDS_ON, RUNS_ON, etc.
    private float $confidence; // 0.00 a 1.00
    private string $sourceProtocol; // SNMP, Agent, manual, etc.
    private array $metadata;
    private string $createdAt;

    public function __construct(
        string $sourceUuid,
        string $targetUuid,
        string $relationType,
        float $confidence = 1.0,
        string $sourceProtocol = 'manual',
        array $metadata = [],
        ?string $createdAt = null
    ) {
        $this->sourceUuid = $sourceUuid;
        $this->targetUuid = $targetUuid;
        $this->relationType = $relationType;
        $this->confidence = $confidence;
        $this->sourceProtocol = $sourceProtocol;
        $this->metadata = $metadata;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    public function sourceUuid(): string { return $this->sourceUuid; }
    public function targetUuid(): string { return $this->targetUuid; }
    public function relationType(): string { return $this->relationType; }
    public function confidence(): float { return $this->confidence; }
    public function sourceProtocol(): string { return $this->sourceProtocol; }
    public function metadata(): array { return $this->metadata; }
    public function createdAt(): string { return $this->createdAt; }

    public function jsonSerialize(): array
    {
        return [
            'source_uuid'     => $this->sourceUuid,
            'target_uuid'     => $this->targetUuid,
            'relation_type'   => $this->relationType,
            'confidence'      => $this->confidence,
            'source_protocol' => $this->sourceProtocol,
            'metadata'        => $this->metadata,
            'created_at'      => $this->createdAt,
        ];
    }
}
