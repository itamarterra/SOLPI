<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Matchers;

final class UserMatcher implements MatcherInterface
{
    private KeyNormalizer $normalizer;

    public function __construct()
    {
        $this->normalizer = new KeyNormalizer();
    }

    public function match(array $record): array
    {
        $keys = [];

        $uuid = $this->normalizer->text((string)($record['uuid'] ?? ''));
        if ($uuid !== '') {
            $keys[] = ['type' => 'uuid', 'value' => $uuid, 'weight' => 1.00];
        }

        $email = $this->normalizer->email((string)($record['email'] ?? ''));
        if ($email !== '') {
            $keys[] = ['type' => 'email', 'value' => $email, 'weight' => 0.98];
        }

        $cpf = $this->normalizer->digits((string)($record['cpf'] ?? $record['document'] ?? ''));
        if ($cpf !== '') {
            $keys[] = ['type' => 'cpf', 'value' => $cpf, 'weight' => 0.96];
        }

        $phone = $this->normalizer->digits((string)($record['phone'] ?? ''));
        if ($phone !== '') {
            $keys[] = ['type' => 'phone', 'value' => $phone, 'weight' => 0.90];
        }

        $company = $this->normalizer->text((string)($record['company'] ?? $record['company_name'] ?? ''));
        $name = $this->normalizer->text((string)($record['name'] ?? ''));
        if ($name !== '') {
            $keys[] = ['type' => 'name', 'value' => $name, 'weight' => 0.60];
        }

        if ($name !== '' && $company !== '') {
            $keys[] = ['type' => 'name_company', 'value' => $name . '|' . $company, 'weight' => 0.82];
        }

        return [
            'entity_type' => 'user',
            'keys' => $keys,
        ];
    }
}
