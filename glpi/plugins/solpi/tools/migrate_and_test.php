<?php
declare(strict_types=1);

/**
 * Script de migração e teste para SOLPI Agent Registry.
 *
 * Uso: php migrate_and_test.php
 *
 * O script tenta localizar o GLPI root procurando por 'vendor/autoload.php' ou 'inc/includes.php'
 * em diretórios ascendentes. Ajuste as constantes se necessário.
 */

function find_glpi_root(): ?string
{
    $cwd = __DIR__;

    $dir = $cwd;
    for ($i = 0; $i < 8; $i++) {
        if (file_exists($dir . '/vendor/autoload.php') || file_exists($dir . '/inc/includes.php')) {
            return $dir;
        }
        $parent = dirname($dir);
        if ($parent === $dir) {
            break;
        }
        $dir = $parent;
    }

    // Common fallback
    if (file_exists('/var/www/glpi/vendor/autoload.php')) {
        return '/var/www/glpi';
    }

    return null;
}

$glpiRoot = find_glpi_root();

if ($glpiRoot === null) {
    echo "GLPI root not found. Please run this script from within a GLPI installation or adjust the script.\n";
    exit(1);
}

require_once $glpiRoot . '/vendor/autoload.php';

// Try to include DB config if present
if (file_exists($glpiRoot . '/config/config_db.php')) {
    require_once $glpiRoot . '/config/config_db.php';
}

// Setup plugin bootstrap
require_once dirname(__DIR__) . '/inc/bootstrap.php';

use SOLPI\Core\Database;
use SOLPI\Agent\Registry\InstallationRepository;

echo "GLPI_ROOT: {$glpiRoot}\n";

$migrationsDir = dirname(__DIR__) . '/sql/migrations';

if (!is_dir($migrationsDir)) {
    echo "Migrations directory not found: {$migrationsDir}\n";
    exit(1);
}

$db = new Database();

$files = glob($migrationsDir . '/*.sql');
sort($files, SORT_NATURAL);

if (empty($files)) {
    echo "No migration files found in: {$migrationsDir}\n";
    exit(1);
}

foreach ($files as $file) {
    echo "Applying migration file: {$file}\n";
    $sql = file_get_contents($file);
    try {
        $db->query($sql);
        echo "Applied: {$file}\n";
    } catch (Throwable $e) {
        echo "Warning: migration failed for {$file}: " . $e->getMessage() . "\n";
        // continue to attempt remaining migrations
    }
}

echo "All migrations processed.\n";

// Insert test installation (generate token and show it)
$repo = new InstallationRepository();

try {
    try {
        $plainToken = bin2hex(random_bytes(24));
    } catch (\Exception $e) {
        $plainToken = bin2hex(openssl_random_pseudo_bytes(24));
    }
    $tokenHash = password_hash($plainToken, PASSWORD_DEFAULT);

    $data = [
        'site_name' => 'Test Instance ' . uniqid(),
        'site_url' => 'http://localhost',
        'glpi_version' => 'TEST',
        'solpi_version' => 'TEST',
        'ip_address' => '127.0.0.1',
        'capabilities' => ['zabbix' => true, 'evolution' => true],
        'inventory' => ['services' => ['glpi','zabbix']],
        'auth_token' => $tokenHash,
    ];

    $id = $repo->createFromArray($data);
    echo "Inserted test installation with id: {$id}\n";
    echo "Test auth token (store this securely on agent): {$plainToken}\n";
} catch (Throwable $e) {
    echo "Insert failed: " . $e->getMessage() . "\n";
    exit(1);
}

// List installations
$list = $repo->listAll(10, 0);

echo "Current installations (up to 10):\n";
foreach ($list as $row) {
    echo "- [{$row['id']}] {$row['site_name']} ({$row['status']}) last_seen={$row['last_seen']}\n";
}

echo "Done.\n";
