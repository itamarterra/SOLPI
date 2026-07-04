<?php
declare(strict_types=1);

namespace SOLPI\Core\Database;

final class DatabaseManager
{
    private static ?Connection $connection = null;

    public static function getInstance(): Connection
    {
        if (self::$connection === null) {
            self::$connection = new Connection();
        }
        return self::$connection;
    }

    public static function getBuilder(): QueryBuilder
    {
        return new QueryBuilder(self::getInstance()->getRaw());
    }
}

