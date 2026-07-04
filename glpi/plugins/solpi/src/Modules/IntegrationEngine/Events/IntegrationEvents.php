<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Events;

final class IntegrationEvents
{
    public const RECEIVED = 'integration.received';
    public const QUEUED = 'integration.queued';
    public const DUPLICATE = 'integration.duplicate';
    public const JOB_FAILED = 'integration.job_failed';
}
