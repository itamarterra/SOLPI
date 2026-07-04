<?php

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

    public function ask(string $question): string
    {
        $intent = $this->intentDetector->detect($question);
        $entities = $this->entityResolver->resolve($question);

        if ($entities !== [] && array_values($entities) === $entities) {
            $entities = array_combine($entities, array_fill(0, count($entities), true));
        }

        $answer = $this->rag->answer($question, $intent, $entities);

        return $this->formatter->format($answer);
    }

    public function chat(string $message, array $context = []): array
    {
        $answer = $this->ask($message);
        return [
            'answer'    => $answer,
            'timestamp' => date('Y-m-d H:i:s'),
            'context'   => $context
        ];
    }

    public function availableProviders(): array
    {
        return [
            ['id' => 'openai', 'name' => 'OpenAI GPT-4'],
            ['id' => 'claude', 'name' => 'Anthropic Claude 3.5'],
            ['id' => 'local', 'name' => 'SOLPI Local (Llama 3)']
        ];
    }
}
