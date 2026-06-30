<?php

declare(strict_types=1);

namespace SOLPI\AI;

final class AIService
{
    public function __construct(
        private AIRepository $repository
    ) {
    }

    public function remember(
        array $memory
    ): int {

        return $this->repository->saveMemory(
            $memory
        );

    }

    public function registerEntity(
        array $entity
    ): int {

        return $this->repository->saveEntity(
            $entity
        );

    }

    public function registerRelationship(
        array $relationship
    ): int {

        return $this->repository->saveRelationship(
            $relationship
        );

    }

    public function saveConversation(
        array $conversation
    ): int {

        return $this->repository->saveConversation(
            $conversation
        );

    }

    public function saveMessage(
        array $message
    ): int {

        return $this->repository->saveMessage(
            $message
        );

    }
}