<?php

declare(strict_types=1);

namespace SOLPI\Knowledge;

use Ramsey\Uuid\Uuid;

final class EntityExtractor
{
    /**
     * Palavras que representam tipos de entidades.
     */
    private array $types = [

        'NOTEBOOK',
        'DESKTOP',
        'SERVIDOR',
        'SWITCH',
        'ROTEADOR',
        'FIREWALL',
        'IMPRESSORA',
        'MONITOR',
        'LICENÇA',
        'NOBREAK',
        'ACCESS POINT',
        'CAMERA',
        'CÂMERA'

    ];

    public function extract(string $text): array
    {
        $entities = [];

        $lines = preg_split('/\r\n|\r|\n/', $text);

        foreach ($lines as $line) {

            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $entity = $this->parseLine($line);

            if ($entity instanceof KnowledgeEntity) {
                $entities[] = $entity;
            }

        }

        return $entities;
    }

    private function parseLine(string $line): ?KnowledgeEntity
    {
        $upper = mb_strtoupper($line);

        foreach ($this->types as $type) {

            if (str_contains($upper, $type)) {

                $entity = new KnowledgeEntity(

                    Uuid::uuid4()->toString(),

                    $type,

                    trim($line)

                );

                $entity
                    ->attribute('source', 'TEXT')
                    ->attribute('created_at', date('Y-m-d H:i:s'));

                $this->extractCompany($entity, $line);

                $this->extractSerial($entity, $line);

                $this->extractWarranty($entity, $line);

                return $entity;

            }

        }

        return null;
    }

    private function extractCompany(
        KnowledgeEntity $entity,
        string $text
    ): void {

        if (preg_match('/TRELICAMP/i', $text)) {

            $entity->attribute(
                'company',
                'TRELICAMP'
            );

        }

    }

    private function extractSerial(
        KnowledgeEntity $entity,
        string $text
    ): void {

        if (

            preg_match(
                '/([A-Z0-9]{6,20})/',
                $text,
                $match
            )

        ) {

            $entity->attribute(
                'serial',
                $match[1]
            );

        }

    }

    private function extractWarranty(
        KnowledgeEntity $entity,
        string $text
    ): void {

        if (

            preg_match(
                '/(\d{2}\/\d{2}\/\d{4})/',
                $text,
                $match
            )

        ) {

            $entity->attribute(
                'warranty',
                $match[1]
            );

        }

    }
}