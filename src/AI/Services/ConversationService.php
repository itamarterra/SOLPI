<?php
declare(strict_types=1);

namespace SOLPI\AI\Services;

use SOLPI\AI\Conversation;
use SOLPI\AI\Memory\ConversationMemory;

final class ConversationService
{
    private ConversationMemory $memory;

    public function __construct()
    {
        $this->memory = new ConversationMemory();
    }

    /**
     * @param array<string,mixed> $metadata
     * @return Conversation
     */
    public function startConversation(array $metadata = []): Conversation
    {
        return $this->memory->createConversation($metadata);
    }

    /**
     * @return array<string,mixed>
     */
    public function getHistory(int $limit = 20): array
    {
        return $this->memory->getConversationHistory($limit);
    }

    /**
     * @return bool
     */
    public function clearHistory(): bool
    {
        return $this->memory->clear();
    }
}

