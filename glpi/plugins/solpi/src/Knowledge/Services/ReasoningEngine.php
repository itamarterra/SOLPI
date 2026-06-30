<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

final class ReasoningEngine
{
    public function infer(
        array $entities
    ): array {

        $facts=[];

        foreach($entities as $entity){

            if(

                isset($entity['warranty_date'])

            ){

                if(

                    strtotime($entity['warranty_date'])

                    < strtotime('+30 days')

                ){

                    $facts[]=[

                        'type'=>'EXPIRING_WARRANTY',

                        'entity'=>$entity

                    ];

                }

            }

        }

        return $facts;

    }
}
