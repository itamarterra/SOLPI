<?php

declare(strict_types=1);

namespace SOLPI\AI\Services;

final class EmbeddingService
{
    /**
     * @return array<string,int>
     */
    public function generate(
        string $text
    ): array {

        $text = mb_strtolower($text);

        $words = preg_split('/\s+/', $text) ?: [];

        $vector = [];

        foreach ($words as $word) {

            $word = trim($word);

            if ($word === '') {
                continue;
            }

            if (!isset($vector[$word])) {
                $vector[$word] = 0;
            }

            $vector[$word]++;

        }

        ksort($vector);

        return $vector;
    }
}
