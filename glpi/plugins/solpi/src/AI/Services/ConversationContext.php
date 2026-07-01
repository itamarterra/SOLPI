<?php

declare(strict_types=1);

namespace SOLPI\AI\Services;

use SOLPI\AI\Memory\ConversationMemory;

final class ConversationContext
{
    private ConversationMemory $memory;

    public function __construct()
    {
        $this->memory = new ConversationMemory();
    }

    public function addUser(
        string $message
    ): void {

        $this->memory->add(

            'user',

            $message

        );

    }

    public function addAssistant(
        string $message
    ): void {

        $this->memory->add(

            'assistant',

            $message

        );

    }

    /**
     * @return array<int,array{role:string,content:string,time:string}>
     */
    public function history(): array
    {
        return $this->memory->history();
    }
}
