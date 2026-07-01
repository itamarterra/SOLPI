<?php

declare(strict_types=1);

/**
 * Compatibility stub for DBmysql used by the legacy GLPI integration.
 *
 * This file provides a minimal, non-invasive implementation so static
 * analyzers (phpstan) can resolve the symbol. It is intentionally
 * lightweight and must NOT be used at runtime in production — replace
 * with the real DB layer when available.
 */

if (!class_exists('DBmysql')) {
    class DBmysql
    {
        /**
         * Insert a row into table.
         * @param string $table
         * @param array<string,mixed> $data
         * @return bool
         */
        public function insert(string $table, array $data): bool
        {
            return true;
        }

        /**
         * Return last insert id as int.
         */
        public function insertId(): int
        {
            return 0;
        }

        /**
         * Update a row in table.
         * @param string $table
         * @param array<string,mixed> $data
         * @param array<string,mixed> $where
         * @return bool
         */
        public function update(string $table, array $data, array $where = []): bool
        {
            return true;
        }

        /**
         * Delete rows matching where.
         * @param string $table
         * @param array<string,mixed> $where
         * @return bool
         */
        public function delete(string $table, array $where = []): bool
        {
            return true;
        }

        /**
         * Execute a request and return rows.
         * @param string|array<string,mixed> $sql
         * @return iterable<int,array<string,mixed>>
         */
        public function request(mixed $sql): iterable
        {
            return [];
        }

        /**
         * Execute a raw query (non-select) and return success.
         * @param string $sql
         * @return bool
         */
        public function query(string $sql): bool
        {
            return true;
        }

        /**
         * Escape a string for use in SQL.
         * @param string $value
         * @return string
         */
        public function escape(string $value): string
        {
            return addslashes($value);
        }
    }
}
