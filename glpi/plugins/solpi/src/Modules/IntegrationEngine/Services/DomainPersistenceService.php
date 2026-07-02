<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Services;

use RuntimeException;
use SOLPI\Modules\IntegrationEngine\Repositories\AssetRecordRepository;
use SOLPI\Modules\IntegrationEngine\Repositories\CompanyRecordRepository;
use SOLPI\Modules\IntegrationEngine\Repositories\UserRecordRepository;

final class DomainPersistenceService
{
    private object $db;
    private CompanyRecordRepository $companies;
    private UserRecordRepository $users;
    private AssetRecordRepository $assets;

    public function __construct()
    {
        global $DB;
        if (!is_object($DB)) {
            throw new RuntimeException('Conexao com o banco do GLPI nao encontrada.');
        }

        $this->db = $DB;
        $this->companies = new CompanyRecordRepository();
        $this->users = new UserRecordRepository();
        $this->assets = new AssetRecordRepository();
    }

    /**
     * @param array<string,mixed> $record
     * @return array<string,mixed>
     */
    public function persist(string $entityType, array $record): array
    {
        return match ($entityType) {
            'company' => $this->companies->upsert($record),
            'user' => $this->users->upsert($record),
            'asset' => $this->assets->upsert($record),
            default => throw new RuntimeException('Unsupported entity_type for persistence: ' . $entityType),
        };
    }
}
