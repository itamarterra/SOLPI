<?php

declare(strict_types=1);

namespace SOLPI\Modules\Tickets;

use DBmysql;
use RuntimeException;

final class TicketRepository
{
    private DBmysql $db;

    public function __construct()
    {
        global $DB;
        if (!$DB instanceof DBmysql) {
            throw new RuntimeException('Conexao com o banco do GLPI nao encontrada.');
        }
        $this->db = $DB;
    }

    public function createGLPITicket(string $title, string $content, int $priority = 3): int
    {
        $now = date('Y-m-d H:i:s');
        $this->db->insert('glpi_tickets', [
            'entities_id'           => 0,
            'name'                  => $this->db->escape($title),
            'content'               => $this->db->escape('<p>' . nl2br(htmlspecialchars($content)) . '</p>'),
            'date'                  => $now,
            'date_creation'         => $now,
            'date_mod'              => $now,
            'status'                => 1,
            'type'                  => 1,
            'priority'              => $priority,
            'urgency'               => 3,
            'impact'                => 3,
            'requesttypes_id'       => 1,
            'users_id_lastupdater'  => 0,
            'is_deleted'            => 0,
        ]);
        $id = (int)$this->db->insertId();
        if ($id === 0) {
            throw new RuntimeException('Falha ao criar ticket no GLPI.');
        }

        // Associar solicitante anonimo — necessario para o GLPI exibir o chamado
        $this->db->insert('glpi_tickets_users', [
            'tickets_id'        => $id,
            'users_id'          => 0,
            'type'              => 1,
            'use_notification'  => 0,
            'alternative_email' => '',
        ]);

        return $id;
    }

    public function createSyncRecord(int $glpiTicketId, ?int $alertId = null): int
    {
        $this->db->insert('glpi_plugin_solpi_tickets', [
            'glpi_ticket_id' => $glpiTicketId,
            'alert_id'       => $alertId,
            'status'         => 'OPEN',
            'opened_at'      => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->insertId();
    }

    public function findByGLPITicketId(int $glpiTicketId): ?array
    {
        foreach ($this->db->request([
            'FROM'  => 'glpi_plugin_solpi_tickets',
            'WHERE' => ['glpi_ticket_id' => $glpiTicketId],
            'LIMIT' => 1,
        ]) as $row) {
            return $row;
        }
        return null;
    }

    public function findByPhone(string $phone): ?array
    {
        // Passo 1: buscar o ticket_id mais recente para este numero
        $solpiTicketId = null;

        foreach ($this->db->request([
            'SELECT' => ['ticket_id'],
            'FROM'   => 'glpi_plugin_solpi_whatsapp',
            'WHERE'  => ['phone' => $phone, 'direction' => 'INBOUND'],
            'ORDER'  => 'id DESC',
            'LIMIT'  => 1,
        ]) as $row) {
            $solpiTicketId = (int)$row['ticket_id'];
        }

        if ($solpiTicketId === null) {
            return null;
        }

        // Passo 2: buscar o ticket SOLPI pelo ID
        foreach ($this->db->request([
            'FROM'  => 'glpi_plugin_solpi_tickets',
            'WHERE' => ['id' => $solpiTicketId],
            'LIMIT' => 1,
        ]) as $row) {
            return $row;
        }

        return null;
    }

    public function getPhoneForTicket(int $solpiTicketId): ?string
    {
        foreach ($this->db->request([
            'SELECT' => ['phone'],
            'FROM'   => 'glpi_plugin_solpi_whatsapp',
            'WHERE'  => ['ticket_id' => $solpiTicketId, 'direction' => 'INBOUND'],
            'ORDER'  => 'id DESC',
            'LIMIT'  => 1,
        ]) as $row) {
            return $row['phone'];
        }
        return null;
    }

    public function updateStatus(int $solpiTicketId, string $status): void
    {
        $this->db->update('glpi_plugin_solpi_tickets', ['status' => $status], ['id' => $solpiTicketId]);
    }

    public function saveRating(int $solpiTicketId, int $rating): void
    {
        $this->db->update(
            'glpi_plugin_solpi_tickets',
            ['rating' => $rating, 'status' => 'RATED'],
            ['id' => $solpiTicketId]
        );
    }

    public function closeGLPITicket(int $glpiTicketId): void
    {
        $this->db->update('glpi_tickets', [
            'status'     => 6,
            'closedate'  => date('Y-m-d H:i:s'),
            'date_mod'   => date('Y-m-d H:i:s'),
        ], ['id' => $glpiTicketId]);
    }

    public function reopenGLPITicket(int $glpiTicketId): void
    {
        $this->db->update('glpi_tickets', [
            'status'   => 1,
            'date_mod' => date('Y-m-d H:i:s'),
        ], ['id' => $glpiTicketId]);
    }
}