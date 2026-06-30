<?php

declare(strict_types=1);

namespace SOLPI\AI\Services;

use SOLPI\AI\Memory\VectorMemory;

final class RAGService
{
    private EmbeddingService $embedding;

    private RetrieverService $retriever;

    private PromptBuilder $promptBuilder;

    private VectorMemory $memory;

    public function __construct()
    {
        $this->embedding = new EmbeddingService();

        $this->retriever = new RetrieverService();

        $this->promptBuilder = new PromptBuilder();

        $this->memory = new VectorMemory();
    }

    public function answer(
        string $question,
        string $intent,
        array $entities
    ): array {

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
