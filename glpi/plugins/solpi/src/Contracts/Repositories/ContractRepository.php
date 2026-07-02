<?php
declare(strict_types=1);

namespace SOLPI\Contracts\Repositories;

use DBmysql;
use SOLPI\Contracts\Entities\Contract;
use SOLPI\Core\BaseRepository;

final class ContractRepository extends BaseRepository
{
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
        $query = "SELECT * FROM `glpi_solpi_contracts` WHERE 1=1";

        foreach ($filters as $key => $value) {
            $query .= " AND `{$key}` = '{$value}'";
        }

        $result = $this->db->query($query);
        $contracts = [];

        while ($row = $result->fetch_assoc()) {
            $contracts[] = $this->hydrate($row);
        }

        return $contracts;
    }

    /**
     * @return array<string,mixed>
     */
    public function getStatistics(): array
    {
        $query = "SELECT COUNT(*) as total, SUM(value) as sum_value FROM `glpi_solpi_contracts`";
        $result = $this->db->query($query);
        
        return $result->fetch_assoc() ?: ['total' => 0, 'sum_value' => 0];
    }

    /**
     * @param array<string,mixed> $row
     * @return Contract
     */
    private function hydrate(array $row): Contract
    {
        return new Contract(
            $row['id'],
            $row['number'],
            $row['company_id'],
            $row['start_date'],
            $row['end_date']
        );
    }

    /**
     * @param Contract $contract
     * @return Contract
     */
    public function save(Contract $contract): Contract
    {
        $query = "INSERT INTO `glpi_solpi_contracts` (id, number, company_id, start_date, end_date) VALUES (?, ?, ?, ?, ?)";
        // Prepared statement would go here
        return $contract;
    }
}

