<?php

declare(strict_types=1);

namespace SOLPI\Core;

final class EventDispatcher
{
    /**
     * @var array<string,array<int,callable>>
     */
    private array $listeners = [];

    public function listen(
        string $event,
        callable $listener
    ): void {

        $this->listeners[$event][] = $listener;

    }

    public function dispatch(
        string $event,
        mixed $payload = null
    ): void {

        if (!isset($this->listeners[$event])) {
            return;
        }

        foreach ($this->listeners[$event] as $listener) {

            call_user_func(
                $listener,
                $payload,
                $event
            );

        }
    }

    public function remove(
        string $event
    ): void {

        unset($this->listeners[$event]);

    }

    public function clear(): void
    {
        $this->listeners = [];
    }

    public function listeners(
        string $event
    ): array {

        return $this->listeners[$event] ?? [];

    }

    public function has(
        string $event
    ): bool {

        return isset($this->listeners[$event]);

    }

    public function count(): int
    {
        return count($this->listeners);
    }
}