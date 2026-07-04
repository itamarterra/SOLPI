<?php

// Export of modified file: src/Core/BaseRepository.php

declare(strict_types=1);

namespace SOLPI\Core;

use DBmysql;
use RuntimeException;

abstract class BaseRepository
{
    protected DBmysql $db;

    protected string $table;

    public function __construct()
    {
        global $DB;

        if (!$DB instanceof DBmysql) {
            throw new RuntimeException(
                'Conexão com o banco do GLPI não encontrada.'
            );
        }

        $this->db = $DB;
    }

    public function insert(array $data): int
    {
        $this->db->insert(
            $this->table,
            $data
        );

        return (int)$this->db->insertId();
    }

    public function update(
        int $id,
        array $data
    ): bool {

        return (bool)$this->db->update(
            $this->table,
            $data,
            [
                'id' => $id
            ]
        );
    }

    public function delete(
        int $id
    ): bool {

        return (bool)$this->db->delete(
            $this->table,
            [
                'id' => $id
            ]
        );
    }

    /**
     * @param int $id
     * @return array<string,mixed>|null
     */
    public function find(
        int $id
    ): ?array {

        foreach (

            $this->db->request([

                'FROM' => $this->table,

                'WHERE' => [

                    'id' => $id

                ]

            ])

            as $row

        ) {

            return $row;

        }

        return null;

    }

    /**
     * @param array<string,mixed> $where
     * @return array<string,mixed>|null
     */
    public function findBy(
        array $where
    ): ?array {

        foreach (

            $this->db->request([

                'FROM' => $this->table,

                'WHERE' => $where

            ])

            as $row

        ) {

            return $row;

        }

        return null;

    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function all(): array
    {
        return iterator_to_array(

            $this->db->request([

                'FROM' => $this->table

            ])

        );
    }

    public function count(): int
    {
        return count($this->all());
    }
}
