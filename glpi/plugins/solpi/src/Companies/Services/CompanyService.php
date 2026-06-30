<?php

declare(strict_types=1);

namespace SOLPI\Companies\Services;

use Ramsey\Uuid\Uuid;
use SOLPI\Core\BaseService;
use SOLPI\Companies\Entities\Company;
use SOLPI\Companies\Repositories\CompanyRepository;

final class CompanyService extends BaseService
{
    public function __construct()
    {
        $this->repository=new CompanyRepository();
    }

    public function new(
        string $name
    ):Company{

        return new Company(

            Uuid::uuid4()->toString(),

            $name

        );

    }
}
