<?php

declare(strict_types=1);

namespace SOLPI\AI\Services;

final class SimilarityService
{
    public function cosine(
        array $a,
        array $b
    ): float {

        $dot = 0;

        $normA = 0;

        $normB = 0;

        $keys = array_unique(

            array_merge(

                array_keys($a),

                array_keys($b)

            )

        );

        foreach ($keys as $key) {

            $va = $a[$key] ?? 0;

            $vb = $b[$key] ?? 0;

            $dot += $va * $vb;

            $normA += $va ** 2;

            $normB += $vb ** 2;

        }

        if ($normA == 0 || $normB == 0) {
            return 0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));

    }
}
