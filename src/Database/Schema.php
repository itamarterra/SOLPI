<?php

declare(strict_types=1);

namespace SOLPI\Database;

use Migration;

final class Schema
{
    public static function create(Migration $migration): void
    {
        self::createConfig($migration);
        self::createAlerts($migration);
        self::createTickets($migration);
        self::createWhatsapp($migration);
        self::createAI($migration);
        self::createLogs($migration);
        self::createDashboard($migration);
        self::createKnowledge($migration);
        self::createJobs($migration);
        self::createUsers($migration);
        self::createWebhooks($migration);
    }

    private static function createConfig(Migration $migration): void
    {
        // implementação virá na Sprint seguinte
    }

    private static function createAlerts(Migration $migration): void
    {
    }

    private static function createTickets(Migration $migration): void
    {
    }

    private static function createWhatsapp(Migration $migration): void
    {
    }

    private static function createAI(Migration $migration): void
    {
    }

    private static function createLogs(Migration $migration): void
    {
    }

    private static function createDashboard(Migration $migration): void
    {
    }

    private static function createKnowledge(Migration $migration): void
    {
    }

    private static function createJobs(Migration $migration): void
    {
    }

    private static function createUsers(Migration $migration): void
    {
    }

    private static function createWebhooks(Migration $migration): void
    {
    }
}