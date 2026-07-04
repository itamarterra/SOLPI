<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

use SOLPI\Knowledge\HtmlImporter;
use SOLPI\Knowledge\EntityExtractor;

final class ImportPipeline
{
    private HtmlImporter $htmlImporter;

    private EntityExtractor $extractor;

    private KnowledgeService $knowledge;

    public function __construct()
    {
        $this->htmlImporter = new HtmlImporter();
        $this->extractor = new EntityExtractor();
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
