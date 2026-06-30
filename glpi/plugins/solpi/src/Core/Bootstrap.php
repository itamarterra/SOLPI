<?php

declare(strict_types=1);

namespace SOLPI\Core;

use RuntimeException;

/**
 * Bootstrap do SOLPI.
 */
final class Bootstrap
{
    private static bool $initialized = false;

    /**
     * Inicializa o Core.
     */
    public static function initialize(): void
    {
        if (self::$initialized) {
            return;
        }

        self::$initialized = true;

        self::loadConfiguration();

        self::initializeLogger();
    }

    /**
     * Carrega as configurações.
     */
    private static function loadConfiguration(): void
    {
        Config::load();
    }

    /**
     * Inicializa o Logger.
     */
    private static function initializeLogger(): void
    {
        Logger::initialize();
    }
}