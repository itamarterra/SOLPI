<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

use SOLPI\Knowledge\Repositories\KnowledgeRepository;

final class SemanticSearch
{
    private KnowledgeRepository $repository;

    public function __construct()
    {
        $this->repository=new KnowledgeRepository();
    }

    public function search(
        string $query
    ): array{

        $result=[];

        foreach(

            $this->repository->entities()

            as $entity

        ){

            if(

                stripos(

                    json_encode($entity),

                    $query

                )!==false

            ){

                $result[]=$entity;

            }

        }

        return $result;

    }
}
