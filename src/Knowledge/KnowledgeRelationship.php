<?php

declare(strict_types=1);

namespace SOLPI\Knowledge;

final class KnowledgeRelationship
{
    public function __construct(
        private string $from,
        private string $relation,
        private string $to,
        private float $confidence = 100
    ) {
    }

    public function from(): string
    {
        return $this->from;
    }

    public function relation(): string
    {
        return $this->relation;
    }

    public function to(): string
    {
        return $this->to;
    }

    public function confidence(): float
    {
        return $this->confidence;
    }

    public function toArray(): array
    {
        return [

            'from' => $this->from,

            'relation' => $this->relation,

            'to' => $this->to,

            'confidence' => $this->confidence

        ];
    }
}