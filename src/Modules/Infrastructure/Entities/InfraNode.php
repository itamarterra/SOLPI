<?php

declare(strict_types=1);

namespace SOLPI\Modules\Infrastructure\Entities;

use JsonSerializable;

/**
 * Representa uma entidade (nó) dentro do Digital Twin da infraestrutura.
 */
final class InfraNode implements JsonSerializable
{
    private string $uuid;
    private ?string $externalId;
    private string $class; // Asset, Service, User, NetworkNode, etc.
    private string $label;
    private array $metadata;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(
        string $uuid,
        string $class,
        string $label,
        ?string $externalId = null,
        array $metadata = [],
        ?string $createdAt = null,
        ?string $updatedAt = null
    ) {
        $this->uuid = $uuid;
        $this->class = $class;
        $this->label = $label;
        $this->externalId = $externalId;
        $this->metadata = $metadata;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
        $this->updatedAt = $updatedAt ?? date('Y-m-d H:i:s');
    }

    public function uuid(): string { return $this->uuid; }
    public function class(): string { return $this->class; }
    public function label(): string { return $this->label; }
    public function externalId(): ?string { return $this->externalId; }
    public function metadata(): array { return $this->metadata; }
    public function createdAt(): string { return $this->createdAt; }
    public function updatedAt(): string { return $this->updatedAt; }

    public function jsonSerialize(): array
    {
        return [
            'uuid'        => $this->uuid,
            'class'       => $this->class,
            'label'       => $this->label,
            'external_id' => $this->externalId,
            'metadata'    => $this->metadata,
            'created_at'  => $this->createdAt,
            'updated_at'  => $this->updatedAt,
        ];
    }
}
