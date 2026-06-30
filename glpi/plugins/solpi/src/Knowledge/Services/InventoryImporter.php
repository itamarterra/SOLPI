<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

use SOLPI\Knowledge\Builders\EntityBuilder;

final class InventoryImporter
{
    private EntityBuilder $builder;

    private EntityClassifier $classifier;

    public function __construct()
    {
        $this->builder = new EntityBuilder();

        $this->classifier = new EntityClassifier();
    }

    public function import(array $rows): array
    {
        $inventory = [];

        foreach ($rows as $row) {

            $inventory[] = $this->parseRow($row);

        }

        return $inventory;
    }

    private function parseRow(array $row): array
    {
        $description = $row['description'] ?? '';

        $entity = [

            'type' => $this->classifier->classify($row),

            'description' => $description,

            'serial' => $row['serial'] ?? null,

            'warranty' => $row['warranty'] ?? null,

            'value' => $row['value'] ?? null,

            'invoice' => $row['invoice'] ?? null

        ];

        return $entity;
    }
}
