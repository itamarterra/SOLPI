<?php
declare(strict_types=1);

namespace SOLPI\Modules\AI;

use SOLPI\AI\AIKernel;
use SOLPI\AI\Services\ConversationService;

final class AIController
{
    private AIKernel $kernel;
    private ConversationService $conversation;

    public function __construct()
    {
        $this->kernel = new AIKernel();
        $this->conversation = new ConversationService();
    }

    /**
     * @param array<string,mixed> $context
     * @return array<string,mixed>
     */
    public function chat(string $message, array $context = []): array
    {
        return $this->kernel->chat($message, $context);
    }

    /**
     * @return array<string,mixed>
     */
    public function availableProviders(): array
    {
        return $this->kernel->availableProviders();
    }

    /**
     * @return array<string,mixed>
     */
    public function conversationHistory(int $limit = 20): array
    {
        return $this->conversation->getHistory($limit);
    }
}

