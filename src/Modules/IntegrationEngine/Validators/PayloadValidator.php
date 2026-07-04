<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Validators;

use InvalidArgumentException;

final class PayloadValidator
{
    /**
     * @param array<string,mixed> $envelope
     */
    public function validate(array $envelope): void
    {
        $source = (string)($envelope['source'] ?? '');
        if ($source === '') {
            throw new InvalidArgumentException('source is required.');
        }

        $event = (string)($envelope['event'] ?? '');
        if ($event === '') {
            throw new InvalidArgumentException('event is required.');
        }

        $payload = $envelope['payload'] ?? null;
        if (!is_array($payload)) {
            throw new InvalidArgumentException('payload must be an object/array.');
        }

        $bytes = strlen((string)json_encode($payload));
        if ($bytes > 5 * 1024 * 1024) {
            throw new InvalidArgumentException('payload too large (max 5MB).');
        }
    }
}
