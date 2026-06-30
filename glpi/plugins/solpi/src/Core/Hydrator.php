<?php

declare(strict_types=1);

namespace SOLPI\Core;

final class Hydrator
{
    public function hydrate(
        object $object,
        array $data
    ): object {

        foreach ($data as $property => $value) {

            $method = 'set' . ucfirst($property);

            if (method_exists($object, $method)) {

                $object->$method($value);

            }

        }

        return $object;

    }
}
