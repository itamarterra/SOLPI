<?php

declare(strict_types=1);

namespace SOLPI\AI\Services;

use SOLPI\AI\Memory\VectorMemory;

final class RetrieverService
{
    public function search(
        array $queryEmbedding,
        VectorMemory $memory,
        int $limit = 5
    ): array {

        $similarity = new SimilarityService();

        $results = [];

        foreach ($memory->all() as $id => $document) {

            $score = $similarity->cosine(

                $queryEmbedding,

                $document['embedding']

            );

            $results[] = [

                'id' => $id,

                'score' => $score,

                'payload' => $document['payload']

            ];

        }

        usort(

            $results,

            fn($a, $b) => $b['score'] <=> $a['score']

        );

        return array_slice(

            $results,

            0,

            $limit

        );

    }
}
