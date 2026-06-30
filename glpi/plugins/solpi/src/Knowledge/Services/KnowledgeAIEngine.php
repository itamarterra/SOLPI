<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

use SOLPI\Knowledge\Repositories\KnowledgeRepository;

final class KnowledgeAIEngine
{
    private KnowledgeRepository $repository;

    public function __construct()
    {
        $this->repository = new KnowledgeRepository();
    }

    public function ask(
        string $question
    ): array {

        $question = strtoupper(trim($question));

        if (str_contains($question, 'NOTEBOOK')) {
            return $this->findByType('NOTEBOOK');
        }

        if (str_contains($question, 'DESKTOP')) {
            return $this->findByType('DESKTOP');
        }

        if (str_contains($question, 'IMPRESSORA')) {
            return $this->findByType('PRINTER');
        }

        if (str_contains($question, 'MONITOR')) {
            return $this->findByType('MONITOR');
        }

        return $this->repository->entities();
    }

    private function findByType(
        string $type
    ): array {

        $result = [];

        foreach ($this->repository->entities() as $entity) {

            $payload = json_decode(
                $entity['payload'],
                true
            );

            if (
                strtoupper($entity['entity_type']) === $type
            ) {

                $result[] = $payload;

            }

        }

        return $result;

    }
}
