<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

use SOLPI\Knowledge\HtmlImporter;
use SOLPI\Knowledge\EntityExtractor;
use SOLPI\Knowledge\Builders\EntityBuilder;

final class KnowledgeImporter
{
    private HtmlImporter $htmlImporter;

    private EntityExtractor $entityExtractor;

    private EntityBuilder $entityBuilder;

    private KnowledgeService $knowledgeService;

    private CompanyMatcher $companyMatcher;

    private UserMatcher $userMatcher;

    private AssetMatcher $assetMatcher;

    private LicenseMatcher $licenseMatcher;

    private EntityClassifier $classifier;

    public function __construct()
    {
        $this->htmlImporter = new HtmlImporter();

        $this->entityExtractor = new EntityExtractor();

        $this->entityBuilder = new EntityBuilder();

        $this->knowledgeService = new KnowledgeService();

        $this->companyMatcher = new CompanyMatcher();

        $this->userMatcher = new UserMatcher();

        $this->assetMatcher = new AssetMatcher();

        $this->licenseMatcher = new LicenseMatcher();

        $this->classifier = new EntityClassifier();
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
