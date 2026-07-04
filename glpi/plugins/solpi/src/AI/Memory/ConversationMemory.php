<?php

declare(strict_types=1);

namespace SOLPI\AI\Memory;

final class ConversationMemory
{
    /**
     * @var array<int,array{role:string,content:string,time:string}>
     */
    private array $messages = [];

    public function add(string $role, string $content): void
    {
        $this->messages[] = [
            'role'    => $role,
            'content' => $content,
            'time'    => date('Y-m-d H:i:s')
        ];
    }

    public function createConversation(array $metadata = []): \SOLPI\AI\Conversation
    {
        $this->clear();
        // Em uma implementação real, poderíamos persistir isso no banco
        return new \SOLPI\AI\Conversation($metadata);
    }

    public function getConversationHistory(int $limit = 20): array
    {
        return array_slice($this->messages, -$limit);
    }

    public function history(): array
    {
        return $this->messages;
    }

    public function clear(): void
    {
        $this->messages = [];
    }

}
