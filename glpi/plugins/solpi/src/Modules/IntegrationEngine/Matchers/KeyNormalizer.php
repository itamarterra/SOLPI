<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Matchers;

final class KeyNormalizer
{
    public function text(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;
        $value = preg_replace('/[^a-z0-9@._\-\/ ]+/u', '', $value) ?? $value;

        return trim($value);
    }

    public function digits(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return preg_replace('/\D+/', '', $value) ?? '';
    }

    public function email(?string $value): string
    {
        return $this->text($value);
    }

    public function domain(?string $value): string
    {
        $value = $this->text($value);
        if ($value === '') {
            return '';
        }

        $value = preg_replace('#^https?://#', '', $value) ?? $value;
        $value = explode('/', $value)[0] ?? $value;

        return trim($value, '.');
    }
}
