<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\EntityResolver;

use SOLPI\Modules\IntegrationEngine\Matchers\MatcherFactory;
use SOLPI\Modules\IntegrationEngine\Repositories\IdentityMapRepository;
use SOLPI\Modules\IntegrationEngine\Services\SemanticSimilarityService;

final class EntityResolverService implements EntityResolverInterface
{
    private MatcherFactory $factory;
    private IdentityMapRepository $identityMap;
    private SemanticSimilarityService $semantic;

    public function __construct()
    {
        $this->factory = new MatcherFactory();
        $this->identityMap = new IdentityMapRepository();
        $this->semantic = new SemanticSimilarityService();
    }

    /**
     * @return array<string,mixed>
     */
    public function resolve(array $record): array
    {
        $entityType = strtolower((string)($record['entity_type'] ?? ''));
        if ($entityType === '') {
            $entityType = $this->guessEntityType($record);
        }

        $matcher = $this->factory->make($entityType);
        $matched = $matcher->match($record);
        $keys = $matched['keys'] ?? [];

        $candidates = $this->identityMap->findByKeys($entityType, $keys);
        $scores = [];
        $ranked = [];
        $incomingLabel = (string)($record['name'] ?? $record['hostname'] ?? $record['email'] ?? '');

        foreach ($candidates as $candidate) {
            $cid = (string)($candidate['canonical_id'] ?? '');
            if ($cid === '') {
                continue;
            }

            if (!isset($scores[$cid])) {
                $scores[$cid] = 0.0;
            }

            $base = (float)($candidate['confidence'] ?? 0.0);
            $candidateValue = (string)($candidate['key_value'] ?? '');
            $semantic = $this->semantic->compare($incomingLabel, $candidateValue);
            $final = ($base * 0.70) + ($semantic * 0.30);

            $scores[$cid] += $final;

            $ranked[] = [
                'canonical_id' => $cid,
                'key_type' => (string)($candidate['key_type'] ?? ''),
                'key_value' => $candidateValue,
                'base_score' => round($base, 2),
                'semantic_score' => round($semantic, 2),
                'final_score' => round($final, 2),
            ];
        }

        arsort($scores);
        $topCanonical = (string)(array_key_first($scores) ?? '');
        $topScore = (float)($topCanonical !== '' ? ($scores[$topCanonical] ?? 0.0) : 0.0);

        $canonicalId = $topCanonical !== '' ? $topCanonical : $this->canonicalId($entityType, $record);
        $confidence = $topCanonical !== '' ? min(100.0, $topScore / max(1, count($keys))) : 35.0;

        $source = (string)($record['source'] ?? 'integration-engine');
        $rawHash = hash('sha256', json_encode($record, JSON_UNESCAPED_UNICODE));

        foreach ($keys as $key) {
            $this->identityMap->upsertKey(
                $entityType,
                $canonicalId,
                (string)$key['type'],
                (string)$key['value'],
                (float)round(((float)$key['weight']) * 100, 2),
                $source,
                $rawHash,
                ['weight' => $key['weight'] ?? null]
            );
        }

        return [
            'entity_type' => $entityType,
            'canonical_id' => $canonicalId,
            'matched' => $topCanonical !== '',
            'confidence' => round($confidence, 2),
            'candidate_count' => count($scores),
            'keys' => $keys,
            'ranked_candidates' => $ranked,
        ];
    }

    private function guessEntityType(array $record): string
    {
        if (isset($record['cnpj']) || isset($record['trade_name']) || isset($record['company'])) {
            return 'company';
        }

        if (isset($record['cpf']) || isset($record['department']) || isset($record['position'])) {
            return 'user';
        }

        return 'asset';
    }

    private function canonicalId(string $entityType, array $record): string
    {
        $seed = $entityType . '|' . json_encode($record, JSON_UNESCAPED_UNICODE) . '|' . microtime(true);
        return substr(hash('sha256', $seed), 0, 40);
    }
}
