<?php

declare(strict_types=1);

namespace SOLPI\Modules\DigitalTwin\Services;

use SOLPI\Modules\DigitalTwin\Entities\Snapshot;

/**
 * Motor de detecção de mudanças entre estados da infraestrutura.
 */
final class ChangeDetectionEngine
{
    /**
     * Compara dois snapshots e retorna o delta de mudanças.
     */
    public function compare(Snapshot $old, Snapshot $new): array
    {
        $delta = [
            'nodes' => $this->diff($old->data()['nodes'], $new->data()['nodes'], 'uuid'),
            'edges' => $this->diff($old->data()['edges'], $new->data()['edges'], 'id') // Arestas usam ID
        ];

        return $delta;
    }

    /**
     * Calcula diferença entre dois arrays de objetos associativos
     */
    private function diff(array $oldSet, array $newSet, string $key): array
    {
        $changes = [
            'added'    => [],
            'removed'  => [],
            'modified' => []
        ];

        $oldMap = [];
        foreach ($oldSet as $item) { $oldMap[$item[$key]] = $item; }

        $newMap = [];
        foreach ($newSet as $item) { $newMap[$item[$key]] = $item; }

        // Detecta Adicionados e Modificados
        foreach ($newMap as $id => $newItem) {
            if (!isset($oldMap[$id])) {
                $changes['added'][] = $newItem;
            } elseif ($oldMap[$id] != $newItem) {
                $changes['modified'][] = [
                    'old' => $oldMap[$id],
                    'new' => $newItem
                ];
            }
        }

        // Detecta Removidos
        foreach ($oldMap as $id => $oldItem) {
            if (!isset($newMap[$id])) {
                $changes['removed'][] = $oldItem;
            }
        }

        return $changes;
    }
}
