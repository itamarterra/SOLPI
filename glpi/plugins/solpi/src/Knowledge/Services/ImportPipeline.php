<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

use SOLPI\Knowledge\HtmlImporter;
use SOLPI\Knowledge\EntityExtractor;
use SOLPI\Knowledge\Builders\EntityBuilder;

final class ImportPipeline
{
    private HtmlImporter $htmlImporter;

    private EntityExtractor $extractor;

    private EntityBuilder $builder;

    private KnowledgeService $knowledge;

    public function __construct()
    {
        $this->htmlImporter = new HtmlImporter();
        $this->extractor = new EntityExtractor();
        $this->builder = new EntityBuilder();
        $this->knowledge = new KnowledgeService();
    }

    public function importHtml(
        string $html
    ): array {

        $text = $this->htmlImporter->import($html);

        $entities = $this->extractor->extract($text);

        foreach ($entities as $entity) {

            $this->knowledge->register(

                $entity->type(),

                $entity->uuid(),

                $entity->toArray()

            );

        }

        return $entities;
    }
}
