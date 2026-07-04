<?php
declare(strict_types=1);

namespace SOLPI\Knowledge\Memory;

final class ConversationMemory
{
    private array $history = [];

    public function add(string $role, string $text): void
    {
        $this->history[] = [
            'role' => $role,
            'text' => $text,
            'time' => date('Y-m-d H:i:s')
        ];
    }

    public function getAll(): array
    {
        return $this->history;
    }

    public function getLast(): ?array
    {
        return empty($this->history) ? null : end($this->history);
    }
}

