<?php

declare(strict_types=1);

namespace SOLPI\Modules\Dashboard;

use SOLPI\Core\Cache;

final class DashboardCache
{
    private Cache $cache;

    public function __construct()
    {
        $this->cache = new Cache();
    }

    public function remember(string $key, callable $callback): mixed
    {
        if ($this->cache->has($key)) {

            return $this->cache->get($key);

        }

        $value = $callback();

        $this->cache->put($key, $value);

        return $value;
    }
}