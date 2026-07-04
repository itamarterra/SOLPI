<?php
declare(strict_types=1);

namespace SOLPI\Contracts\Repositories;

use SOLPI\Contracts\Entities\Contract;
use SOLPI\Core\BaseRepository;
use SOLPI\Core\Database\QueryBuilder;

final class ContractRepository extends BaseRepository
{
    protected string $table = 'glpi_solpi_contracts';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<int,Contract>
     */
    public function findBy(array $filters = []): array
    {
        $qb = new QueryBuilder($this->db);
        $rows = $qb->from($this->table)
                   ->where($filters)
                   ->execute();

        $contracts = [];
        foreach ($rows as $row) {
            $contracts[] = $this->hydrate($row);
        }

        return $contracts;
    }

    /**
     * @return array<string,mixed>
     */
    public function getStatistics(): array
    {
        $qb = new QueryBuilder($this->db);
        $result = $qb->from($this->table)
                    ->select(['COUNT(*) as total', 'SUM(value) as sum_value'])
                    ->first();
        
        return $result ?: ['total' => 0, 'sum_value' => 0];
    }

    /**
     * @param array<string,mixed> $row
     * @return Contract
     */
    private function hydrate(array $row): Contract
    {
        return new Contract(
            (int)$row['id'],
            (string)$row['number'],
            (int)$row['company_id'],
            (string)$row['start_date'],
            $row['end_date'] ?? null
        );
    }

    /**
     * @param Contract $contract
     * @return Contract
     */
    public function save(Contract $contract): Contract
    {
        $data = [
            'number'     => $contract->number,
            'company_id' => $contract->companyId,
            'start_date' => $contract->startDate,
            'end_date'   => $contract->endDate,
        ];

        if ($contract->id > 0) {
            $this->update($contract->id, $data);
            return $contract;
        }

        $id = $this->insert($data);
        return new Contract(
            $id,
            $contract->number,
            $contract->companyId,
            $contract->startDate,
            $contract->endDate
        );
    }
}

