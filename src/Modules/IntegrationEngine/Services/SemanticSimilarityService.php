<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Services;

final class SemanticSimilarityService
{
    public function compare(string $a, string $b): float
    {
        $a = $this->normalize($a);
        $b = $this->normalize($b);

        if ($a === '' || $b === '') {
            return 0.0;
        }

        similar_text($a, $b, $percent);

        return round((float)$percent, 2);
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));

        $suffixes = [
            ' ltda',
            ' s/a',
            ' sa',
            ' inc',
            ' corporation',
            ' corp',
            ' brasil',
            ' do brasil',
        ];

        foreach ($suffixes as $suffix) {
            if (str_ends_with($value, $suffix)) {
                $value = trim(substr($value, 0, -strlen($suffix)));
            }
        }

        $value = preg_replace('/[^a-z0-9 ]+/', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }
}
