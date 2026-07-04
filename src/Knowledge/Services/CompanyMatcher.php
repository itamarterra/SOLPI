<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

final class CompanyMatcher
{
    public function normalize(
        string $company
    ): string {

        $company = strtoupper($company);

        $company = trim($company);

        $company = preg_replace('/\s+/', ' ', $company);

        return $company;

    }

    public function equals(
        string $a,
        string $b
    ): bool {

        return $this->normalize($a) === $this->normalize($b);

    }
}
