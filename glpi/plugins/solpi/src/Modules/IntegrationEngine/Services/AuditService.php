<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Services;

use SOLPI\Modules\IntegrationEngine\Repositories\AuditLogRepository;

final class AuditService
{
    private AuditLogRepository $logs;

    public function __construct()
    {
        $this->logs = new AuditLogRepository();
    }

    /**
     * @param array<string,mixed> $context
     */
    public function info(string $message, array $context = []): void
    {
        $this->logs->write('IntegrationEngine', 'INFO', $message, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    public function warning(string $message, array $context = []): void
    {
        $this->logs->write('IntegrationEngine', 'WARNING', $message, $context);
    }

    /**
     * @param array<string,mixed> $context
     */
    public function error(string $message, array $context = []): void
    {
        $this->logs->write('IntegrationEngine', 'ERROR', $message, $context);
    }
}
