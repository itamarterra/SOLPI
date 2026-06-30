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
        $evolution = $config->get('evolution', []);

        $this->client = new EvolutionClient($evolution);
    }

    public function status(): array
    {
        return $this->client->status();
    }

    public function session(): array
    {
        return $this->client->session();
    }

    public function qrCode(): array
    {
        return $this->client->qrCode();
    }
}
