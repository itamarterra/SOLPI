<?php

declare(strict_types=1);

namespace SOLPI\AI\Services;

final class EntityResolver
{
    public function resolve(
        string $question
    ): array {

        $entities=[];

        $question=strtoupper($question);

        $dictionary=[

            'TRELICAMP',

            'OCTIO',

            'NOTEBOOK',

            'DESKTOP',

            'IMPRESSORA',

            'MONITOR',

            'SERVIDOR',

            'LICENÇA',

            'DELL',

            'HP',

            'LENOVO',

            'BROTHER',

            'EPSON'

        ];

        foreach($dictionary as $item){

            if(str_contains($question,$item)){

                $entities[]=$item;

            }

        }

        return $entities;

    }
}
