<?php

// Export of modified file: src/Core/BaseEntity.php

declare(strict_types=1);

namespace SOLPI\Core;

use DateTime;

abstract class BaseEntity
{
    protected ?int $id = null;

    protected string $uuid;

    protected bool $active = true;

    /**
     * Entity metadata bag.
     *
     * @var array<string,mixed>
     */
    protected array $metadata = [];

    protected DateTime $createdAt;

    protected DateTime $updatedAt;

    public function __construct(
        string $uuid
    ){

        $this->uuid = $uuid;

        $this->createdAt = new DateTime();

        $this->updatedAt = new DateTime();

    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function setId(
        int $id
    ): static{

        $this->id=$id;

        return $this;

    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function active(): bool
    {
        return $this->active;
    }

    public function activate(): static
    {
        $this->active=true;

        return $this;
    }

    public function deactivate(): static
    {
        $this->active=false;

        return $this;
    }

    /**
     * @return array<string,mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(
        string $key,
        mixed $value
    ): static{

        $this->metadata[$key]=$value;

        return $this;

    }

    public function createdAt(): DateTime
    {
        return $this->createdAt;
    }

    public function updatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function touch(): void
    {
        $this->updatedAt=new DateTime();
    }

    /**
     * @return array<string,mixed>
     */
    abstract public function toArray(): array;
}
