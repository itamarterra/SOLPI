<?php

declare(strict_types=1);

namespace SOLPI\Knowledge;

final class CompanyExtractor
{
    /**
     * Empresas conhecidas.
     * No futuro será carregado do banco.
     */
    private array $companies = [

        'TRELICAMP',
        'OCTIO',
        'ECIN',
        'UNIVESP'

    ];

    public function extract(string $text): ?string
    {
        $text = mb_strtoupper($text);

        foreach ($this->companies as $company) {

            if (str_contains($text, $company)) {
                return $company;
            }

        }

        return null;
    }

    public function add(string $company): void
    {
        $company = mb_strtoupper($company);

        if (!in_array($company, $this->companies, true)) {
            $this->companies[] = $company;
        }
    }

    public function all(): array
    {
        return $this->companies;
    }
}