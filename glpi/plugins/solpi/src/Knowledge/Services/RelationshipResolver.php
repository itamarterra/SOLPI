<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

final class RelationshipResolver
{
    public function resolve(
        array $entity
    ): array {

        $relations=[];

        if(isset($entity['company_uuid'])){

            $relations[]=[

                'relation'=>'BELONGS_TO_COMPANY',

                'target'=>$entity['company_uuid']

            ];

        }

        if(isset($entity['user_uuid'])){

            $relations[]=[

                'relation'=>'ASSIGNED_TO',

                'target'=>$entity['user_uuid']

            ];

        }

        if(isset($entity['asset_uuid'])){

            $relations[]=[

                'relation'=>'RELATED_ASSET',

                'target'=>$entity['asset_uuid']

            ];

        }

        return $relations;

    }
}
