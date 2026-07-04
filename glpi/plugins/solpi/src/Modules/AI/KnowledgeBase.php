<?php
declare(strict_types=1);

namespace SOLPI\Modules\AI;

final class KnowledgeBase
{
    private \SOLPI\AI\Services\RetrieverService $retriever;

    public function __construct()
    {
        $this->retriever = new \SOLPI\AI\Services\RetrieverService();
    }

    public function query(string $question, int $limit = 5): array
    {
        return $this->retriever->retrieve($question, $limit);
    }
}

