<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Matchers;

use InvalidArgumentException;

final class MatcherFactory
{
    public function make(string $entityType): MatcherInterface
    {
        return match (strtolower($entityType)) {
            'company' => new CompanyMatcher(),
            'user' => new UserMatcher(),
            'asset' => new AssetMatcher(),
            default => throw new InvalidArgumentException('Unsupported entity_type for matcher: ' . $entityType),
        };
    }
}
