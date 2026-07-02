<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\MergeEngine;

use SOLPI\Modules\IntegrationEngine\Policies\MergePolicy;
use SOLPI\Modules\IntegrationEngine\Repositories\MergeConflictRepository;

final class FieldMergeEngine implements MergeStrategyInterface
{
    private MergePolicy $policy;
    private MergeConflictRepository $conflicts;

    public function __construct()
    {
        $this->policy = new MergePolicy();
        $this->conflicts = new MergeConflictRepository();
    }

    /**
     * @return array<string,mixed>
     */
    public function merge(array $current, array $incoming, array $context = []): array
    {
        $entityType = (string)($context['entity_type'] ?? 'unknown');
        $correlationId = $context['correlation_id'] ?? null;
        $canonicalId = $context['canonical_id'] ?? null;

        $merged = $current;
        $changes = [];
        $conflicts = [];

        foreach ($incoming as $field => $incomingValue) {
            if ($incomingValue === null || $incomingValue === '') {
                continue;
            }

            $currentValue = $current[$field] ?? null;
            if ($currentValue === $incomingValue) {
                continue;
            }

            $fieldPath = $entityType . '.' . (string)$field;

            if ($this->policy->isProtected($fieldPath) && $currentValue !== null && $currentValue !== '') {
                $conflict = [
                    'correlation_id' => $correlationId,
                    'entity_type' => $entityType,
                    'canonical_id' => $canonicalId,
                    'field_path' => $fieldPath,
                    'current_value' => $currentValue,
                    'incoming_value' => $incomingValue,
                    'decision' => 'KEPT_CURRENT',
                    'reason' => 'protected_field',
                ];

                $this->conflicts->create($conflict);
                $conflicts[] = $conflict;
                continue;
            }

            $merged[$field] = $incomingValue;
            $changes[] = [
                'field' => (string)$field,
                'from' => $currentValue,
                'to' => $incomingValue,
            ];
        }

        return [
            'merged' => $merged,
            'changes' => $changes,
            'conflicts' => $conflicts,
        ];
    }
}
