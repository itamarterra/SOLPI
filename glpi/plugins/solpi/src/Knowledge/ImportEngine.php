<?php

declare(strict_types=1);

namespace SOLPI\Knowledge;

final class ImportEngine
{
    /**
     * @var array<string,callable>
     */
    private array $importers = [];

    public function register(
        string $type,
        callable $importer
    ): void {

        $this->importers[$type] = $importer;

    }

    public function import(
        string $type,
        string $content
    ): array {

        if (!isset($this->importers[$type])) {

            throw new \RuntimeException(
                "Importer {$type} não encontrado."
            );

        }

        return call_user_func(

            $this->importers[$type],

            $content

        );
    }

    public function supported(): array
    {
        return array_keys(
            $this->importers
        );
    }
}