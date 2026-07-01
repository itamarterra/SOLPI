<?php

// Export of modified file: src/AI/AIKernel.php

declare(strict_types=1);

namespace SOLPI\AI;

use SOLPI\AI\Services\RAGService;
use SOLPI\AI\Services\IntentDetector;
use SOLPI\AI\Services\EntityResolver;
use SOLPI\AI\Services\ResponseFormatter;

final class AIKernel
{
    private RAGService $rag;

    private IntentDetector $intentDetector;

    private EntityResolver $entityResolver;

    private ResponseFormatter $formatter;

    public function __construct()
    {
        $this->rag = new RAGService();

        $this->intentDetector = new IntentDetector();

        $this->entityResolver = new EntityResolver();

        $this->formatter = new ResponseFormatter();
    }

    public function ask(
        string $question
    ): string {

        $intent = $this->intentDetector->detect(
            $question
        );

        /**
         * Resolver may return either a list of entity names
         * `array<int,string>` or an associative map
         * `array<string,mixed>` — normalize below.
         *
         * @var array<int,string>|array<string,mixed> $entities
         */
        $entities = $this->entityResolver->resolve(
            $question
        );

        // Normalize entities: if resolver returned a list of strings,
        // convert to an associative map to satisfy downstream callers.
        if ($entities !== [] && array_values($entities) === $entities) {
            $entities = array_combine($entities, array_fill(0, count($entities), true)) ?: [];
        }

        /** @var array<string,mixed> $entities */

        $answer = $this->rag->answer(
            $question,
            $intent,
            $entities
        );

        return $this->formatter->format(
            $answer
        );

    }
}
