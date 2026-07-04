<?php

declare(strict_types=1);

namespace SOLPI\AI;

final class AIService
{
    public function __construct(
        private AIRepository $repository
    ) {
    }

    /**
     * @param array<string,mixed> $memory
     */
    public function remember(
        array $memory
    ): int {

        return $this->repository->saveMemory(
            $memory
        );

    }

    /**
     * @param array<string,mixed> $entity
     */
    public function registerEntity(
        array $entity
    ): int {

        return $this->repository->saveEntity(
            $entity
        );

    }

    /**
     * @param array<string,mixed> $relationship
     */
    public function registerRelationship(
        array $relationship
    ): int {

        return $this->repository->saveRelationship(
            $relationship
        );

    }

    /**
     * @param array<string,mixed> $conversation
     */
    public function saveConversation(
        array $conversation
    ): int {

        return $this->repository->saveConversation(
            $conversation
        );

    }

    /**
     * @param array<string,mixed> $message
     */
    public function saveMessage(
        array $message
    ): int {

        return $this->repository->saveMessage(
            $message
        );

    }
}