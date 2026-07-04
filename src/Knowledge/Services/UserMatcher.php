<?php

declare(strict_types=1);

namespace SOLPI\Knowledge\Services;

final class UserMatcher
{
    public function normalize(
        string $user
    ): string {

        $user = strtoupper($user);

        $user = trim($user);

        $user = preg_replace('/\s+/', ' ', $user);

        return $user;

    }

    public function equals(
        string $a,
        string $b
    ): bool {

        return $this->normalize($a) === $this->normalize($b);

    }
}
