<?php

declare(strict_types=1);

namespace SOLPI\AI\Services;

final class SimilarityService
{
    /**
     * Compute cosine similarity between two vectors.
     * Accepts either numeric-indexed or associative vectors.
     *
     * @param array<int,float>|array<string,float> $a
     * @param array<int,float>|array<string,float> $b
     */
    public function cosine(array $a, array $b): float
    {
        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        $keys = array_unique(array_merge(array_keys($a), array_keys($b)));

        foreach ($keys as $key) {
            $va = isset($a[$key]) ? (float)$a[$key] : 0.0;
            $vb = isset($b[$key]) ? (float)$b[$key] : 0.0;

            $dot += $va * $vb;
            $normA += $va * $va;
            $normB += $vb * $vb;
        }

        if ($normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }
}
