<?php

declare(strict_types=1);

namespace SOLPI\Core;

use DBmysql;

/**
 * Classe base para acesso ao banco do GLPI.
 */
abstract class Repository
{
    protected DBmysql $db;

    public function __construct()
    {
        global $DB;

        if (!$DB instanceof DBmysql) {
            throw new \RuntimeException(
                'Conexão do GLPI não disponível.'
            );
        }

        $this->db = $DB;
    }

    protected function connection(): DBmysql
    {
        return $this->db;
    }
}