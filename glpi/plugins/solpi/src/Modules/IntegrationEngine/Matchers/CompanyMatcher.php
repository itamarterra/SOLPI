<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Matchers;

final class CompanyMatcher implements MatcherInterface
{
    private KeyNormalizer $normalizer;

    public function __construct()
    {
        $this->normalizer = new KeyNormalizer();
    }

    public function match(array $record): array
    {
        $keys = [];

        $uuid = $this->normalizer->text((string)($record['uuid'] ?? $record['solpi_uuid'] ?? ''));
        if ($uuid !== '') {
            $keys[] = ['type' => 'uuid', 'value' => $uuid, 'weight' => 1.00];
        }

        $cnpj = $this->normalizer->digits((string)($record['cnpj'] ?? $record['document'] ?? ''));
        if ($cnpj !== '') {
            $keys[] = ['type' => 'cnpj', 'value' => $cnpj, 'weight' => 1.00];
        }

        $domain = $this->normalizer->domain((string)($record['domain'] ?? $record['website'] ?? ''));
        if ($domain !== '') {
            $keys[] = ['type' => 'domain', 'value' => $domain, 'weight' => 0.92];
        }

        $email = $this->normalizer->email((string)($record['email'] ?? ''));
        if ($email !== '') {
            $keys[] = ['type' => 'email', 'value' => $email, 'weight' => 0.88];
        }

        $phone = $this->normalizer->digits((string)($record['phone'] ?? ''));
        if ($phone !== '') {
            $keys[] = ['type' => 'phone', 'value' => $phone, 'weight' => 0.85];
        }

        $name = $this->normalizer->text((string)($record['name'] ?? ''));
        if ($name !== '') {
            $keys[] = ['type' => 'name', 'value' => $name, 'weight' => 0.70];
        }

        $trade = $this->normalizer->text((string)($record['trade_name'] ?? ''));
        if ($trade !== '') {
            $keys[] = ['type' => 'trade_name', 'value' => $trade, 'weight' => 0.68];
        }

        return [
            'entity_type' => 'company',
            'keys' => $keys,
        ];
    }
}
