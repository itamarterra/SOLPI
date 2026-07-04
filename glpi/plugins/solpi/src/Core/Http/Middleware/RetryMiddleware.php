<?php
declare(strict_types=1);

namespace SOLPI\Core\Http\Middleware;

final class RetryMiddleware
{
    public function execute(callable $action, int $maxRetries = 3, int $delay = 1000): mixed
    {
        $attempts = 0;
        while ($attempts < $maxRetries) {
            try {
                return $action();
            } catch (\Exception $e) {
                $attempts++;
                if ($attempts >= $maxRetries) {
                    throw $e;
                }
                usleep($delay * 1000);
            }
        }
        return null;
    }
}

