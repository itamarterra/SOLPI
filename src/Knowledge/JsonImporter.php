<?php

declare(strict_types=1);

namespace SOLPI\Knowledge;

use RuntimeException;

final class JsonImporter implements ImporterInterface
{
    public function import(
        string $content
    ): array {

        $data = json_decode(
            $content,
            true
        );

        if (!is_array($data)) {

            throw new RuntimeException(
                'JSON inválido.'
            );

        }

        return $data;
    }
}