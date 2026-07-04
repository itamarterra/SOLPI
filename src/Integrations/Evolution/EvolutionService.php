<?php

declare(strict_types=1);

namespace SOLPI\Integrations\Evolution;

use SOLPI\Core\Config;

final class EvolutionService
{
    private EvolutionClient $client;

    public function __construct()
    {
        $config = new Config();
        $config->load();

        $this->client = new EvolutionClient(
            $config->get('evolution', [])
        );
    }

    public function isConnected(): bool
    {
        $instance = $this->client->fetchInstance();
        return ($instance['connectionStatus'] ?? '') === 'open';
    }

    public function fetchInstance(): array
    {
        return $this->client->fetchInstance();
    }

    public function connect(): array
    {
        return $this->client->connect();
    }

    public function sendText(string $number, string $message): array
    {
        return $this->client->sendText($number, $message);
    }
}
