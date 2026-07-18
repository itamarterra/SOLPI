<?php
/**
 * Test Unitário - Configuração e Variáveis de Ambiente
 */

namespace SOLPI\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    /**
     * Testa carregamento de variáveis de ambiente
     */
    public function testEnvironmentVariablesLoaded(): void
    {
        $this->assertTrue(in_array(getenv('SOLPI_TEST_ENV'), ['true', '1']));
        $this->assertNotEmpty(getenv('SOLPI_ZABBIX_TOKEN'));
        $this->assertNotEmpty(getenv('SOLPI_EVOLUTION_TOKEN'));
        $this->assertNotEmpty(getenv('SOLPI_AI_API_KEY'));
        $this->assertNotEmpty(getenv('SOLPI_WEBHOOK_SECRET'));
    }

    /**
     * Testa que constantes estão definidas
     */
    public function testConstantsAreDefined(): void
    {
        $this->assertTrue(defined('SOLPI_ROOT'));
        $this->assertTrue(defined('SOLPI_SRC'));
        $this->assertTrue(defined('GLPI_ROOT'));
        $this->assertTrue(defined('GLPI_CONFIG_DIR'));
    }

    /**
     * Testa que diretórios existem
     */
    public function testDirectoriesExist(): void
    {
        $this->assertDirectoryExists(SOLPI_ROOT);
        $this->assertDirectoryExists(SOLPI_SRC);
        $this->assertDirectoryExists(SOLPI_ROOT . '/tests');
    }

    /**
     * Testa que arquivos de configuração estão presentes
     */
    public function testConfigFilesPresent(): void
    {
        $this->assertFileExists(SOLPI_ROOT . '/composer.json');
        $this->assertFileExists(SOLPI_ROOT . '/phpunit.xml');
        $this->assertFileExists(SOLPI_ROOT . '/phpstan.neon');
    }

    /**
     * Testa versão do PHP
     */
    public function testPHPVersionIsSupported(): void
    {
        $this->assertGreaterThanOrEqual(8, PHP_MAJOR_VERSION);
        $this->assertGreaterThanOrEqual(3, PHP_MINOR_VERSION);
    }

    /**
     * Testa que extensões necessárias estão carregadas
     */
    public function testRequiredExtensionsLoaded(): void
    {
        $this->assertTrue(extension_loaded('json'));
        $this->assertTrue(extension_loaded('curl'));
        $this->assertTrue(extension_loaded('spl')); // Standard PHP Library
    }
}
