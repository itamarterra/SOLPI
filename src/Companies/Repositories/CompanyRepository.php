<?php

declare(strict_types=1);

namespace SOLPI\Companies\Repositories;

use SOLPI\Core\BaseRepository;
use SOLPI\Companies\Entities\Company;

final class CompanyRepository extends BaseRepository
{
    protected string $table = 'glpi_plugin_solpi_companies';

    public function create(
        Company $company
    ): int {

        return $this->insert($this->mapCompanyData($company, true));

    }

    public function save(
        Company $company
    ): bool {

        $data = $this->mapCompanyData($company, false);
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->update(
            $company->id(),
            $data
        );

    }

    public function findByUUID(
        string $uuid
    ): ?array {

        return $this->findBy([

            'uuid'=>$uuid

        ]);

    }

    /**
     * @param Company $company
     * @param bool $withTimestamps
     * @return array<string,mixed>
     */
    private function mapCompanyData(Company $company, bool $withTimestamps = false): array
    {
        $data = [
            'uuid' => $company->uuid(),
            'name' => $company->name(),
            'trade_name' => $company->tradeName(),
            'document' => $company->document(),
            'email' => $company->email(),
            'phone' => $company->phone(),
            'website' => $company->website(),
            'address' => $company->address(),
            'city' => $company->city(),
            'state' => $company->state(),
            'zip_code' => $company->zipCode(),
            'active' => $company->active() ? 1 : 0,
            'settings' => json_encode($company->settings(), JSON_UNESCAPED_UNICODE),
            'metadata' => json_encode($company->metadata(), JSON_UNESCAPED_UNICODE),
        ];

        if ($withTimestamps) {
            $data['created_at'] = $company->createdAt()->format('Y-m-d H:i:s');
            $data['updated_at'] = $company->updatedAt()->format('Y-m-d H:i:s');
        }

        return $data;
    }
}
