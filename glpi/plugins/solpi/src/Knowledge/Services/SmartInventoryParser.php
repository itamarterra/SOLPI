<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

final class SmartInventoryParser
{
    private ManufacturerDetector $manufacturer;

    private ModelDetector $model;

    private EntityClassifier $classifier;

    public function __construct()
    {
        $this->manufacturer = new ManufacturerDetector();

        $this->model = new ModelDetector();

        $this->classifier = new EntityClassifier();
    }

    public function parse(
        array $row
    ): array {

        $description = trim(
            $row['description'] ?? ''
        );

        return [

            'description' => $description,

            'type' => $this->classifier->classify($row),

            'manufacturer' => $this->manufacturer->detect($description),

            'model' => $this->model->detect($description),

            'serial' => $row['serial'] ?? null,

            'warranty' => $row['warranty'] ?? null,

            'value' => $row['value'] ?? null,

            'invoice' => $row['invoice'] ?? null

        ];

    }
}
