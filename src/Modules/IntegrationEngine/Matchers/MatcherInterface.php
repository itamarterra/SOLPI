<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Matchers;

interface MatcherInterface
{
    /**
     * @return array<string,mixed>
     */
    public function match(array $record): array;
}
