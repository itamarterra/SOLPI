<?php

// Export of modified file: src/AI/Services/RAGService.php

declare(strict_types=1);

namespace SOLPI\AI\Services;

use SOLPI\AI\Memory\VectorMemory;

final class RAGService
{
    private EmbeddingService $embedding;

    private RetrieverService $retriever;

    private \SOLPI\AI\PromptBuilder $promptBuilder;

    private VectorMemory $memory;

    public function __construct()
    {
        $this->embedding = new EmbeddingService();

        $this->retriever = new RetrieverService();

        $this->promptBuilder = new \SOLPI\AI\PromptBuilder();

        $this->memory = new VectorMemory();
    }

    /**
     * Accept either a list of entity names or an associative map.
     *
     * @param array<int,string>|array<string,mixed> $entities
     * @return array<string,mixed>
     */
    public function answer(
        string $question,
        string $intent,
        array $entities
    ): array {

        // Normalize list form into associative map: ['NAME'=>true]
        if ($entities !== [] && array_values($entities) === $entities) {
            $entities = array_combine($entities, array_fill(0, count($entities), true)) ?: [];
        }

        $vector = $this->embedding->generate(
            $question
        );

        $documents = $this->retriever->search(

            $vector,

            $this->memory

        );

        return [

            'question'=>$question,

            'intent'=>$intent,

            'entities'=>$entities,

            'documents'=>$documents,

            'prompt'=>$this->promptBuilder->build(

                $question,

                $documents

            )

        ];

    }
}
