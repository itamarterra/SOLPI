<?php
declare(strict_types=1);

namespace SOLPI\Contracts\Services;

use Ramsey\Uuid\Uuid;
use SOLPI\Contracts\Entities\Contract;
use SOLPI\Contracts\Repositories\ContractRepository;

final class ContractService
{
    private ContractRepository $repository;

    public function __construct()
    {
        $this->repository = new ContractRepository();
    }

    /**
     * @param array<string,mixed> $data
     * @return Contract
     */
    public function create(array $data): Contract
    {
        $contract = new Contract(
            Uuid::uuid4()->toString(),
            $data['number'] ?? '',
            $data['company_id'] ?? null,
            $data['start_date'] ?? null,
            $data['end_date'] ?? null
        );

        return $this->repository->save($contract);
    }

    /**
     * @param array<string,mixed> $filters
     * @return array<int,Contract>
     */
    public function find(array $filters = []): array
    {
        return $this->repository->findBy($filters);
    }

    /**
     * @return array<string,mixed>
     */
    public function getStats(): array
    {
        return $this->repository->getStatistics();
    }
}

