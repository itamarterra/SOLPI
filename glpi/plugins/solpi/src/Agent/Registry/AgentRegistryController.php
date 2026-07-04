<?php

declare(strict_types=1);

namespace SOLPI\Agent\Registry;

use SOLPI\Core\Controller;
use Session;

final class AgentRegistryController extends Controller
{
    public function register(): void
    {
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw ?: '{}', true);

        if (!is_array($payload) || empty($payload['site_name'])) {
            http_response_code(400);
            echo json_encode(['error' => 'site_name é obrigatório']);
            return;
        }

        $repo = new InstallationRepository();

        // Generate a server-side token and store only its hash
        try {
            $plainToken = bin2hex(random_bytes(24));
        } catch (\Exception $e) {
            $plainToken = bin2hex(openssl_random_pseudo_bytes(24));
        }

        $tokenHash = password_hash($plainToken, PASSWORD_DEFAULT);

        $id = $repo->createFromArray([
            'site_name' => $payload['site_name'],
            'site_url' => $payload['site_url'] ?? null,
            'glpi_version' => $payload['glpi_version'] ?? null,
            'solpi_version' => $payload['solpi_version'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'capabilities' => $payload['capabilities'] ?? [],
            'inventory' => $payload['inventory'] ?? null,
            'auth_token' => $tokenHash,
        ]);

        http_response_code(201);
        echo json_encode(['id' => $id, 'auth_token' => $plainToken]);
    }

    public function heartbeat(): void
    {
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw ?: '{}', true);

        if (!is_array($payload) || empty($payload['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'id é obrigatório']);
            return;
        }

        $repo = new InstallationRepository();

        $id = (int)$payload['id'];

        // Determine token: payload.auth_token or Authorization: Bearer <token>
        $providedToken = $payload['auth_token'] ?? null;
        if (empty($providedToken)) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null);
            if (empty($authHeader) && function_exists('getallheaders')) {
                $h = getallheaders();
                $authHeader = $h['Authorization'] ?? $h['authorization'] ?? null;
            }
            if (!empty($authHeader) && stripos($authHeader, 'Bearer ') === 0) {
                $providedToken = trim(substr($authHeader, 7));
            }
        }

        // Fetch installation record to verify token
        $row = $repo->find($id);
        if ($row === null) {
            http_response_code(404);
            echo json_encode(['error' => 'Instalação não encontrada']);
            return;
        }

        $storedHash = $row['auth_token'] ?? null;
        if (empty($storedHash) || empty($providedToken) || !password_verify($providedToken, $storedHash)) {
            http_response_code(401);
            echo json_encode(['error' => 'unauthorized']);
            return;
        }

        $updated = $repo->updateFromArray($id, [
            'last_seen' => date('Y-m-d H:i:s'),
            'status' => $payload['status'] ?? 'online',
        ]);

        if (!$updated) {
            http_response_code(404);
            echo json_encode(['error' => 'Instalação não encontrada']);
            return;
        }

        http_response_code(200);
        echo json_encode(['ok' => true]);
    }

    public function installations(): void
    {
        $repo = new InstallationRepository();

        $list = $repo->listAll(1000, 0);

        // Remove auth_token from output
        foreach ($list as &$r) {
            if (isset($r['auth_token'])) { unset($r['auth_token']); }
        }

        http_response_code(200);
        echo json_encode($list);
    }

    public function pending(): void
    {
        $repo = new InstallationRepository();

        $list = $repo->listPending(1000, 0);

        foreach ($list as &$r) { if (isset($r['auth_token'])) { unset($r['auth_token']); } }

        http_response_code(200);
        echo json_encode($list);
    }

    public function approve(): void
    {
        // Require authenticated GLPI user with appropriate rights
        if (!Session::getLoginUserID() || !(Session::haveRight('plugin_solpi', UPDATE) || Session::haveRight('config', UPDATE))) {
            http_response_code(403);
            echo json_encode(['error' => 'forbidden']);
            return;
        }
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw ?: '{}', true);

        if (!is_array($payload) || empty($payload['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'id é obrigatório']);
            return;
        }

        $repo = new InstallationRepository();

        $approver = Session::getLoginUserID() ?: ($_SERVER['REMOTE_USER'] ?? 'admin');
        $ok = $repo->setApproved((int)$payload['id'], true, (string)$approver);

        if (!$ok) {
            http_response_code(404);
            echo json_encode(['error' => 'Instalação não encontrada']);
            return;
        }

        http_response_code(200);
        echo json_encode(['ok' => true]);
    }

    public function reject(): void
    {
        // Require authenticated GLPI user with appropriate rights
        if (!Session::getLoginUserID() || !(Session::haveRight('plugin_solpi', UPDATE) || Session::haveRight('config', UPDATE))) {
            http_response_code(403);
            echo json_encode(['error' => 'forbidden']);
            return;
        }
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw ?: '{}', true);

        if (!is_array($payload) || empty($payload['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'id é obrigatório']);
            return;
        }

        $repo = new InstallationRepository();

        $approver = Session::getLoginUserID() ?: ($_SERVER['REMOTE_USER'] ?? 'admin');
        $ok = $repo->setApproved((int)$payload['id'], false, (string)$approver);

        if (!$ok) {
            http_response_code(404);
            echo json_encode(['error' => 'Instalação não encontrada']);
            return;
        }

        http_response_code(200);
        echo json_encode(['ok' => true]);
    }

    public function revoke(): void
    {
        // Require authenticated GLPI user with appropriate rights
        if (!Session::getLoginUserID() || !(Session::haveRight('plugin_solpi', UPDATE) || Session::haveRight('config', UPDATE))) {
            http_response_code(403);
            echo json_encode(['error' => 'forbidden']);
            return;
        }

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw ?: '{}', true);

        if (!is_array($payload) || empty($payload['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'id é obrigatório']);
            return;
        }

        $repo = new InstallationRepository();
        $by = Session::getLoginUserID() ?: ($_SERVER['REMOTE_USER'] ?? 'admin');

        $ok = $repo->revokeToken((int)$payload['id'], (string)$by);

        if (!$ok) {
            http_response_code(404);
            echo json_encode(['error' => 'Instalação não encontrada']);
            return;
        }

        http_response_code(200);
        echo json_encode(['ok' => true]);
    }

    public function rotate(): void
    {
        // Require authenticated GLPI user with appropriate rights
        if (!Session::getLoginUserID() || !(Session::haveRight('plugin_solpi', UPDATE) || Session::haveRight('config', UPDATE))) {
            http_response_code(403);
            echo json_encode(['error' => 'forbidden']);
            return;
        }

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw ?: '{}', true);

        if (!is_array($payload) || empty($payload['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'id é obrigatório']);
            return;
        }

        try {
            $plainToken = bin2hex(random_bytes(24));
        } catch (\Exception $e) {
            $plainToken = bin2hex(openssl_random_pseudo_bytes(24));
        }
        $tokenHash = password_hash($plainToken, PASSWORD_DEFAULT);

        $repo = new InstallationRepository();

        $ok = $repo->rotateToken((int)$payload['id'], $tokenHash);

        if (!$ok) {
            http_response_code(404);
            echo json_encode(['error' => 'Instalação não encontrada']);
            return;
        }

        http_response_code(200);
        echo json_encode(['ok' => true, 'auth_token' => $plainToken]);
    }

    public function detail(): void
    {
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'id inválido']);
            return;
        }

        $repo = new InstallationRepository();

        $row = $repo->find($id);

        if ($row === null) {
            http_response_code(404);
            echo json_encode(['error' => 'não encontrado']);
            return;
        }

        if (isset($row['auth_token'])) { unset($row['auth_token']); }

        http_response_code(200);
        echo json_encode($row);
    }
}
