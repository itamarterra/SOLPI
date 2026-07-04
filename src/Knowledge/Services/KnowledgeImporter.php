<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

use SOLPI\Knowledge\HtmlImporter;
use SOLPI\Knowledge\EntityExtractor;

final class KnowledgeImporter
{
    private HtmlImporter $htmlImporter;

    private EntityExtractor $entityExtractor;

    private KnowledgeService $knowledgeService;

    public function __construct()
    {
        $this->htmlImporter = new HtmlImporter();

        $this->entityExtractor = new EntityExtractor();

        $this->knowledgeService = new KnowledgeService();
    }

    public function importHtml(
        string $html
    ): array {

        $text = $this->htmlImporter->import($html);

        return $this->importText($text);

    }

    public function importText(
        string $text
    ): array {

        $entities = $this->entityExtractor->extract($text);

        $result = [];

        foreach ($entities as $entity) {

            $result[] = $entity->toArray();

            $this->knowledgeService->register(

                $entity->type(),

                $entity->uuid(),

                $entity->toArray()

            );

        }

        return $result;

    }
}
