<?php
declare(strict_types=1);

namespace SOLPI\Modules\Notifications;

use Ramsey\Uuid\Uuid;
use SOLPI\Modules\Notifications\Repositories\NotificationRepository;

final class NotificationService
{
    private NotificationRepository $repository;

    public function __construct()
    {
        $this->repository = new NotificationRepository();
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    public function send(string $type, string $recipient, array $data): array
    {
        return $this->repository->create([
            'id' => Uuid::uuid4()->toString(),
            'type' => $type,
            'recipient' => $recipient,
            'data' => json_encode($data),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function pending(int $limit = 50): array
    {
        return $this->repository->findByStatus('pending', $limit);
    }

    /**
     * @return array<string,int>
     */
    public function stats(): array
    {
        return $this->repository->getStatistics();
    }
}

