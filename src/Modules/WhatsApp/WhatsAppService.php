<?php
declare(strict_types=1);

namespace SOLPI\Modules\WhatsApp;

use SOLPI\Integrations\Evolution\EvolutionAPI;
use SOLPI\Modules\WhatsApp\Repositories\WhatsAppRepository;

final class WhatsAppService
{
    private EvolutionAPI $api;
    private WhatsAppRepository $repository;

    public function __construct()
    {
        $this->api = new EvolutionAPI();
        $this->repository = new WhatsAppRepository();
    }

    /**
     * @param array<string,mixed> $messageData
     * @return array<string,mixed>
     */
    public function sendMessage(string $recipient, array $messageData): array
    {
        return $this->api->sendMessage($recipient, $messageData);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function getPendingMessages(int $limit = 50): array
    {
        return $this->repository->findPending($limit);
    }

    /**
     * @return array<string,mixed>
     */
    public function getAccountStatus(): array
    {
        return $this->api->getStatus();
    }
}

