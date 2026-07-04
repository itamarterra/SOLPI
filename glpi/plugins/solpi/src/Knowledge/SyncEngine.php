<?php
declare(strict_types=1);

namespace SOLPI\Knowledge;

final class SyncEngine
{
    private array $tasks = [];

    public function addTask(callable $task): void
    {
        $this->tasks[] = $task;
    }

    public function run(): array
    {
        $results = [];
        foreach ($this->tasks as $task) {
            $results[] = $task();
        }
        return [
            'status'    => 'success',
            'processed' => count($results),
            'time'      => date('Y-m-d H:i:s')
        ];
    }
}

