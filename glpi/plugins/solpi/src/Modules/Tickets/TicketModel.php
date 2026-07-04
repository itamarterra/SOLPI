<?php
declare(strict_types=1);

namespace SOLPI\Modules\Tickets;

final class TicketModel
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $content,
        public readonly int $status,
        public readonly string $date
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            (int)($data['id'] ?? 0),
            (string)($data['name'] ?? ''),
            (string)($data['content'] ?? ''),
            (int)($data['status'] ?? 1),
            (string)($data['date'] ?? '')
        );
    }
}

