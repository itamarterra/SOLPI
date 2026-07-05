<?php

declare(strict_types=1);

namespace SOLPI\Modules\DigitalTwin\Entities;

use JsonSerializable;

/**
 * Representa um Snapshot (foto) do estado completo da infraestrutura em um momento.
 */
final class Snapshot implements JsonSerializable
{
    private ?int $id;
    private string $name;
    private array $data; // Conteúdo serializado (nós e arestas)
    private string $createdAt;

    public function __construct(string $name, array $data = [], ?int $id = null, ?string $createdAt = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->data = $data;
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }

    public function id(): ?int { return $this->id; }
    public function name(): string { return $this->name; }
    public function data(): array { return $this->data; }
    public function createdAt(): string { return $this->createdAt; }

    public function jsonSerialize(): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'data'       => $this->data,
            'created_at' => $this->createdAt,
        ];
    }
}
