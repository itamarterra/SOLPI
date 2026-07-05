<?php

declare(strict_types=1);

namespace SOLPI\Modules\Intelligence\Services;

use RuntimeException;

/**
 * Service to calculate mathematical similarity between vectors (embeddings)
 */
final class SimilarityService
{
    /**
     * Calculates cosine similarity between two vectors.
     * Range: -1.0 to 1.0 (1.0 means identical)
     *
     * @param array<int,float> $vecA
     * @param array<int,float> $vecB
     */
    public function cosineSimilarity(array $vecA, array $vecB): float
    {
        if (count($vecA) !== count($vecB)) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        foreach ($vecA as $i => $valA) {
            $valB = $vecB[$i];
            $dotProduct += $valA * $valB;
            $normA += $valA * $valA;
            $normB += $valB * $valB;
        }

        if ($normA == 0 || $normB == 0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }

    /**
     * Finds the most similar items in the database for a given vector
     *
     * @param array<int,float> $targetVector
     * @param string $itemType Optional filter by type
     * @param float $threshold Minimum score to consider
     * @return array<int, array{id:int, score:float}>
     */
    public function findSimilar(array $targetVector, ?string $itemType = null, float $threshold = 0.8): array
    {
        // Nota: Em uma base de dados real com milhões de linhas,
        // usaríamos um Vector DB ou extensões como pgvector.
        // No GLPI, faremos uma busca otimizada via PHP para o MVP.

        global $DB;
        $query = "SELECT source_id, embedding FROM glpi_plugin_solpi_embeddings";
        if ($itemType) {
            $query .= " WHERE source_type = '" . $DB->escape($itemType) . "'";
        }

        $result = $DB->query($query);
        $matches = [];

        while ($row = $DB->fetchAssoc($result)) {
            $vector = json_decode($row['embedding'], true);
            if (!$vector) continue;

            $score = $this->cosineSimilarity($targetVector, $vector);
            if ($score >= $threshold) {
                $matches[] = [
                    'id' => (int)$row['source_id'],
                    'score' => round($score, 4)
                ];
            }
        }

        // Ordena por score descendente
        usort($matches, fn($a, $b) => $b['score'] <=> $a['score']);

        return $matches;
    }
}
