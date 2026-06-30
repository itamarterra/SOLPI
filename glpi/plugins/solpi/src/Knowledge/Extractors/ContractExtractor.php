<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Extractors;

final class ContractExtractor
{
    private array $keywords = [

        'CONTRATO',

        'SUPORTE',

        'MANUTENCAO',

        'MANUTENÇÃO',

        'LICITACAO',

        'LICITAÇÃO'

    ];

    public function extract(
        string $text
    ): ?string {

        $upper = mb_strtoupper($text);

        foreach ($this->keywords as $keyword) {

            if (str_contains($upper, $keyword)) {
                return $keyword;
            }

        }

        return null;
    }
}