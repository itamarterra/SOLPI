<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\DTO;

final class IngestionEnvelope
{
    public string $correlationId;
    public string $source;
    public string $event;
    public string $idempotencyKey;
    public string $receivedAt;

    /**
     * @var array<string,mixed>
     */
    public array $payload;

    /**
     * @param array<string,mixed> $payload
     */
    public function __construct(string $source, string $event, array $payload, ?string $correlationId = null)
    {
        $this->source = strtolower(trim($source));
        $this->event = trim($event) !== '' ? trim($event) : 'upsert';
        $this->payload = $payload;
        $this->receivedAt = date(DATE_ATOM);
        $this->correlationId = $correlationId ?: self::uuidLike();
        $this->idempotencyKey = hash('sha256', $this->source . '|' . $this->event . '|' . json_encode($payload));
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'correlation_id' => $this->correlationId,
            'source' => $this->source,
            'event' => $this->event,
            'idempotency_key' => $this->idempotencyKey,
            'received_at' => $this->receivedAt,
            'payload' => $this->payload,
        ];
    }

    private static function uuidLike(): string
    {
        return sprintf(
            '%s-%s-%s-%s-%s',
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(2)),
            bin2hex(random_bytes(6))
        );
    }
}
