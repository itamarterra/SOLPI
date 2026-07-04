<?php
declare(strict_types=1);

namespace SOLPI\Modules\Tickets;

final class TicketController
{
    private TicketRepository $repository;

    public function __construct()
    {
        $this->repository = new TicketRepository();
    }

    /**
     * @return array<string,mixed>
     */
    public function summary(): array
    {
        return $this->repository->statusSummary();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function recent(int $limit = 20): array
    {
        return $this->repository->recent($limit);
    }

    /**
     * @return array<string,mixed>
     */
    public function close(int $glpiTicketId): array
    {
        $this->repository->closeGLPITicket($glpiTicketId);

        $solpi = $this->repository->findByGLPITicketId($glpiTicketId);
        if (is_array($solpi) && isset($solpi['id'])) {
            $this->repository->updateStatus((int)$solpi['id'], 'AWAITING_RATING');
        }

        return [
            'status' => 'closed',
            'glpi_ticket_id' => $glpiTicketId,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function reopen(int $glpiTicketId): array
    {
        $this->repository->reopenGLPITicket($glpiTicketId);

        $solpi = $this->repository->findByGLPITicketId($glpiTicketId);
        if (is_array($solpi) && isset($solpi['id'])) {
            $this->repository->updateStatus((int)$solpi['id'], 'OPEN');
        }

        return [
            'status' => 'reopened',
            'glpi_ticket_id' => $glpiTicketId,
        ];
    }
}

