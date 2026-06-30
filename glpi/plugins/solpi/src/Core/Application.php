<?php

declare(strict_types=1);

namespace SOLPI\Core;

/**
 * SOLPI Professional
 *
 * Classe principal da aplicação.
 * Responsável por inicializar todos os módulos,
 * configurações e serviços do sistema.
 */
final class Application
{
    private static ?Application $instance = null;

    private Kernel $kernel;

    private Container $container;

    private Config $config;

    private Logger $logger;

    private Database $database;

    private bool $booted = false;

    private function __construct()
    {
        $this->container = new Container();
        $this->config    = new Config();
        $this->logger    = new Logger();
        $this->database  = new Database();
        $this->kernel    = new Kernel(
            $this->container,
            $this->config,
            $this->logger,
            $this->database
        );
    }

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        $this->config->load();

        $this->logger->initialize();

        $this->database->connect();

        $this->kernel->boot();

        $this->booted = true;
    }

    public function kernel(): Kernel
    {
        return $this->kernel;
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function logger(): Logger
    {
        return $this->logger;
    }

    public function database(): Database
    {
        return $this->database;
    }

    public function isBooted(): bool
    {
        return $this->booted;
    }
}