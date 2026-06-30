<?php

declare(strict_types=1);

namespace SOLPI\Knowledge;

final class KnowledgeEntity
{
    private array $attributes = [];

    public function __construct(
        private string $uuid,
        private string $type,
        private string $name
    ) {
    }

    public function uuid(): string
    {
        return $this->uuid;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function attribute(
        string $key,
        mixed $value
    ): self {

        $this->attributes[$key] = $value;

        return $this;
    }

    public function get(
        string $key,
        mixed $default = null
    ): mixed {

        return $this->attributes[$key]
            ?? $default;

    }

    public function attributes(): array
    {
        return $this->attributes;
    }

    public function has(
        string $key
    ): bool {

        return array_key_exists(
            $key,
            $this->attributes
        );

    }

    public function toArray(): array
    {
        return [

            'uuid' => $this->uuid,

            'type' => $this->type,

            'name' => $this->name,

            'attributes' => $this->attributes

        ];
    }
}