<?php
declare(strict_types=1);

namespace SOLPI\Modules\Notifications;

use SOLPI\Modules\Notifications\Services\NotificationService as NotificationSvc;

final class NotificationController
{
    private NotificationSvc $service;

    public function __construct()
    {
        $this->service = new NotificationSvc();
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    public function send(string $type, string $recipient, array $data): array
    {
        return $this->service->send($type, $recipient, $data);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function pending(int $limit = 50): array
    {
        return $this->service->pending($limit);
    }

    /**
     * @return array<string,int>
     */
    public function stats(): array
    {
        return $this->service->stats();
    }
}

