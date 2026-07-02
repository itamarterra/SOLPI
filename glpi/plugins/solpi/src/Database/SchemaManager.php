<?php

declare(strict_types=1);

namespace SOLPI\Database;

use DBmysql;

final class SchemaManager
{
    private DBmysql $db;

    public function __construct()
    {
        global $DB;

        if (!$DB instanceof DBmysql) {
            throw new \RuntimeException('Conexao com o banco do GLPI nao encontrada.');
        }

        $this->db = $DB;
    }

    public function execute(string $sql): void
    {
        // Strip UTF-8 BOM
        if (str_starts_with($sql, "\xEF\xBB\xBF")) {
            $sql = substr($sql, 3);
        }

        // Remove block comments /* ... */
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql) ?? $sql;

        // Split on statement-ending semicolons
        $statements = preg_split('/;\s*[\r\n]+/', $sql);

        foreach ($statements as $statement) {

            // Remove line comments (--)  
            $lines = array_filter(
                explode("\n", $statement),
                static fn(string $line): bool =>
                    !str_starts_with(trim($line), '--')
            );

            $statement = trim(implode("\n", $lines));

            if ($statement === '') {
                continue;
            }

            // Skip SET statements - GLPI manages connection settings
            if (stripos($statement, 'SET ') === 0) {
                continue;
            }

            $this->db->doQuery($statement);
        }
    }
}
