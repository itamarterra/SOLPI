<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Services;

final class ClassificationService
{
    private ReviewService $review;

    public function __construct()
    {
        $this->review = new ReviewService();
    }

    /**
     * @param array<string,mixed> $record
     * @return array<string,mixed>
     */
    public function classify(array $record): array
    {
        $text = $this->normalizeText(
            (string)($record['text'] ?? $record['description'] ?? $record['message'] ?? '')
        );

        $scores = [
            'incident' => $this->score($text, ['down', 'indisponivel', 'erro', 'falha', 'critical', 'panic']),
            'request' => $this->score($text, ['solicitacao', 'acesso', 'permissao', 'pedido', 'requisicao']),
            'change' => $this->score($text, ['mudanca', 'change', 'deploy', 'release', 'atualizacao']),
            'security' => $this->score($text, ['ataque', 'vulnerabilidade', 'malware', 'ransomware', 'cve', 'seguranca']),
            'asset' => $this->score($text, ['notebook', 'servidor', 'switch', 'asset', 'inventario', 'equipamento']),
        ];

        arsort($scores);
        $category = (string)array_key_first($scores);
        $bestScore = (int)($scores[$category] ?? 0);
        $confidence = $this->toConfidence($bestScore, mb_strlen($text));

        $result = [
            'category' => $category,
            'confidence' => $confidence,
            'scores' => $scores,
        ];

        if ($confidence < 65.0) {
            $reviewId = $this->review->enqueue(
                'classification',
                $confidence,
                $record,
                $result,
                [],
                isset($record['correlation_id']) ? (string)$record['correlation_id'] : null
            );

            $result['status'] = 'review_required';
            $result['review_id'] = $reviewId;

            return $result;
        }

        $result['status'] = 'classified';
        return $result;
    }

    private function normalizeText(string $text): string
    {
        $text = mb_strtolower(trim($text));
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return $text;
    }

    /**
     * @param array<int,string> $keywords
     */
    private function score(string $text, array $keywords): int
    {
        $score = 0;
        foreach ($keywords as $keyword) {
            if ($keyword === '') {
                continue;
            }

            if (str_contains($text, $keyword)) {
                $score += 20;
            }
        }

        return min(100, $score);
    }

    private function toConfidence(int $bestScore, int $textLength): float
    {
        if ($textLength === 0) {
            return 0.0;
        }

        $lengthBoost = min(10.0, $textLength / 60.0);
        return round(min(99.0, $bestScore + $lengthBoost), 2);
    }
}
