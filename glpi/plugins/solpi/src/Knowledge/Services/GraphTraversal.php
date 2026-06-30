<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

use SOLPI\Knowledge\Repositories\KnowledgeRepository;

final class GraphTraversal
{
    private KnowledgeRepository $repository;

    public function __construct()
    {
        $this->repository=new KnowledgeRepository();
    }

    public function neighbours(
        string $uuid
    ): array{

        $result=[];

        foreach(

            $this->repository->relationships()

            as $relation

        ){

            if(

                $relation['source_uuid']===$uuid

            ){

                $result[]=$relation;

            }

        }

        return $result;

    }
}
