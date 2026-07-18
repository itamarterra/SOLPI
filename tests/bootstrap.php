<?php
/**
 * PHPUnit Bootstrap File
 * 
 * Carrega autoloader, constantes e stubs necessários para testes
 */

// Caminho raiz
define('SOLPI_ROOT', __DIR__ . '/..');
define('SOLPI_SRC', SOLPI_ROOT . '/src');

// Autoloader Composer
require_once SOLPI_ROOT . '/vendor/autoload.php';

// Ambiente de teste
putenv('SOLPI_TEST_ENV=true');
putenv('SOLPI_DEBUG=false');

// Variáveis de ambiente para integrações (mocks)
putenv('SOLPI_ZABBIX_TOKEN=test_token_zabbix_12345');
putenv('SOLPI_EVOLUTION_TOKEN=test_token_evolution_67890');
putenv('SOLPI_AI_API_KEY=test_ai_key_abc123');
putenv('SOLPI_WEBHOOK_SECRET=test_webhook_secret_xyz789');

// Constantes GLPI mockadas (para não quebrar imports)
if (!defined('GLPI_ROOT')) {
    define('GLPI_ROOT', SOLPI_ROOT);
    define('GLPI_CONFIG_DIR', SOLPI_ROOT . '/config');
    define('GLPI_VAR_DIR', SOLPI_ROOT . '/storage');
    define('GLPI_LOG_DIR', SOLPI_ROOT . '/storage/logs');
    define('GLPI_PLUGIN_DIR', SOLPI_ROOT);
}

// Database mocking helper
class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function getMockDB()
    {
        return $this->createMock(\DBConnection::class);
    }

    protected function getTestFixture(string $name): array
    {
        $path = __DIR__ . '/Fixtures/' . $name . '.php';
        if (!file_exists($path)) {
            throw new \RuntimeException("Fixture not found: {$name}");
        }
        return include $path;
    }
}
