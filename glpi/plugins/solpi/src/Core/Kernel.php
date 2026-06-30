<?php

declare(strict_types=1);

namespace SOLPI\Core;

final class Kernel
{
    private Container $container;

    private Config $config;

    private Logger $logger;

    private Database $database;

    /**
     * @var array<string, object>
     */
    private array $modules = [];

    private bool $booted = false;

    public function __construct(
        Container $container,
        Config $config,
        Logger $logger,
        Database $database
    ) {
        $this->container = $container;
        $this->config = $config;
        $this->logger = $logger;
        $this->database = $database;
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->registerCore();

        $this->registerModules();

        $this->bootModules();

        $this->booted = true;
    }

    private function registerCore(): void
    {
        $this->container->instance(
            Container::class,
            $this->container
        );

        $this->container->instance(
            Config::class,
            $this->config
        );

        $this->container->instance(
            Logger::class,
            $this->logger
        );

        $this->container->instance(
            Database::class,
            $this->database
        );
    }

    private function registerModules(): void
    {
        $this->autoloadDirectory(
            dirname(__DIR__)
        );
    }

    private function bootModules(): void
    {
        foreach ($this->modules as $module) {

            if (method_exists($module, 'boot')) {
                $module->boot();
            }

        }
    }

    public function register(
        string $name,
        object $module
    ): void {

        $this->modules[$name] = $module;

    }

    public function module(
        string $name
    ): ?object {

        return $this->modules[$name] ?? null;

    }

    public function modules(): array
    {
        return $this->modules;
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }

    private function autoloadDirectory(
        string $directory
    ): void {

        $iterator = new \RecursiveIteratorIterator(

            new \RecursiveDirectoryIterator(
                $directory,
                \FilesystemIterator::SKIP_DOTS
            )

        );

        foreach ($iterator as $file) {

            if (
                !$file->isFile() ||
                $file->getExtension() !== 'php'
            ) {
                continue;
            }

            require_once $file->getRealPath();

        }
    }
}