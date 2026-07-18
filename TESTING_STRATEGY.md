# SOLPI Plugin Testing Strategy

## 🧪 Ferramentas de Teste

### 1. **PHPUnit** — Testes Unitários & Integração
```bash
composer require --dev phpunit/phpunit:^11.0
```

### 2. **PHPStan** — Análise Estática
- ✅ Já em `require-dev` (composer.json)
- Detecta type errors, undefined methods, etc.

### 3. **PHPCPD** — Detecção de Código Duplicado
- ✅ Já em `require-dev` (composer.json)
- Previne code bloat

### 4. **Infection** — Mutation Testing (opcional)
```bash
composer require --dev infection/infection
```

---

## 📁 Estrutura de Testes

```
tests/
├── Unit/                          # Testes unitários
│   ├── Modules/
│   │   ├── IntegrationEngineTest.php
│   │   ├── ZabbixIntegrationTest.php
│   │   ├── EvolutionIntegrationTest.php
│   │   └── AIIntegrationTest.php
│   ├── Services/
│   │   ├── WebhookServiceTest.php
│   │   ├── NotificationServiceTest.php
│   │   └── ValidationServiceTest.php
│   └── Utils/
│       └── EncryptionUtilTest.php
│
├── Integration/                   # Testes de integração
│   ├── ZabbixWorkflowTest.php
│   ├── EvolutionWorkflowTest.php
│   ├── AIWorkflowTest.php
│   └── EndToEndWorkflowTest.php
│
├── Fixtures/                      # Dados de teste
│   ├── zabbix_responses.php
│   ├── evolution_responses.php
│   └── webhook_payloads.php
│
└── bootstrap.php                  # Setup de teste

phpunit.xml                        # Configuração PHPUnit
phpstan.neon                       # Já existe (revisar)
phpcpd.xml                         # Já existe (revisar)
```

---

## 🚀 Configuração Inicial

### Step 1: Instalar Dependências
```bash
composer install
composer require --dev phpunit/phpunit:^11.0
```

### Step 2: Criar phpunit.xml
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="https://schema.phpunit.de/11.0/phpunit.xsd"
         bootstrap="tests/bootstrap.php"
         cacheDirectory=".phpunit.cache"
         colors="true"
         verbose="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/Integration</directory>
        </testsuite>
    </testsuites>

    <source>
        <include>
            <directory>src</directory>
        </include>
        <exclude>
            <directory>src/stubs</directory>
        </exclude>
    </source>

    <coverage processUncoveredFiles="true"
              pathCoverage="false"
              ignoreDeprecatedCodeUnits="true"
              disableCodeCoverageIgnore="false">
        <report>
            <html outputDirectory="coverage"/>
            <text outputFile="php://stdout" showUncoveredFiles="true"/>
        </report>
    </coverage>
</phpunit>
```

### Step 3: Criar tests/bootstrap.php
```php
<?php
// Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Stubs para ambiente de teste
define('GLPI_ROOT', __DIR__ . '/..');
define('GLPI_CONFIG_DIR', __DIR__ . '/../config');

// Variaveis de ambiente para testes
putenv('SOLPI_TEST_ENV=true');
putenv('SOLPI_ZABBIX_TOKEN=test_token_zabbix');
putenv('SOLPI_EVOLUTION_TOKEN=test_token_evolution');
putenv('SOLPI_AI_API_KEY=test_ai_key');
putenv('SOLPI_WEBHOOK_SECRET=test_webhook_secret');
```

---

## 🧪 Exemplos de Testes

### Unit Test: Validation Service
```php
<?php
// tests/Unit/Services/ValidationServiceTest.php

namespace SOLPI\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use SOLPI\Services\ValidationService;

class ValidationServiceTest extends TestCase
{
    private ValidationService $validator;

    protected function setUp(): void
    {
        $this->validator = new ValidationService();
    }

    public function testValidateZabbixWebhook(): void
    {
        $payload = [
            'trigger_id' => '12345',
            'status' => 'PROBLEM',
            'severity' => 'high',
            'title' => 'CPU usage critical',
        ];

        $result = $this->validator->validateZabbixWebhook($payload);
        $this->assertTrue($result);
    }

    public function testRejectInvalidZabbixWebhook(): void
    {
        $payload = [
            'trigger_id' => '',  // Missing required field
            'status' => 'PROBLEM',
        ];

        $result = $this->validator->validateZabbixWebhook($payload);
        $this->assertFalse($result);
    }

    public function testValidateEvolutionPayload(): void
    {
        $payload = [
            'phone' => '5511999999999',
            'message' => 'Test message',
            'timestamp' => time(),
        ];

        $result = $this->validator->validateEvolutionPayload($payload);
        $this->assertTrue($result);
    }
}
```

### Integration Test: Webhook Workflow
```php
<?php
// tests/Integration/ZabbixWorkflowTest.php

namespace SOLPI\Tests\Integration;

use PHPUnit\Framework\TestCase;
use SOLPI\Modules\ZabbixIntegration;
use SOLPI\Services\WebhookService;

class ZabbixWorkflowTest extends TestCase
{
    private ZabbixIntegration $zabbix;
    private WebhookService $webhookService;

    protected function setUp(): void
    {
        $this->zabbix = new ZabbixIntegration();
        $this->webhookService = new WebhookService();
    }

    public function testZabbixAlertToChatNotification(): void
    {
        // Simular webhook do Zabbix
        $webhookPayload = $this->loadFixture('zabbix_alert.php');

        // Validar
        $this->assertTrue($this->webhookService->validate($webhookPayload));

        // Processar
        $result = $this->zabbix->handleWebhook($webhookPayload);

        // Asserções
        $this->assertIsArray($result);
        $this->assertArrayHasKey('notification_id', $result);
        $this->assertEquals('pending', $result['status']);
    }

    protected function loadFixture(string $filename): array
    {
        return include __DIR__ . '/../Fixtures/' . $filename;
    }
}
```

---

## 📊 Comandos de Teste

### Executar todos os testes
```bash
./vendor/bin/phpunit
```

### Executar apenas testes unitários
```bash
./vendor/bin/phpunit tests/Unit
```

### Executar apenas testes de integração
```bash
./vendor/bin/phpunit tests/Integration
```

### Com coverage report
```bash
./vendor/bin/phpunit --coverage-html coverage/
```

### Análise estática (PHPStan)
```bash
./vendor/bin/phpstan analyse src --level=max
```

### Detecção de código duplicado
```bash
./vendor/bin/phpcpd src/
```

### Executar tudo (completo)
```bash
composer run-script test
```

---

## 🔄 CI/CD GitHub Actions

Arquivo: `.github/workflows/test.yml`

```yaml
name: Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php-version: [ '8.3', '8.4' ]
    
    steps:
      - uses: actions/checkout@v4
      
      - name: Setup PHP ${{ matrix.php-version }}
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php-version }}
          extensions: json, curl, xml, pdo_mysql
          tools: composer:v2
      
      - name: Validate composer.json
        run: composer validate --strict
      
      - name: Install dependencies
        run: composer install --prefer-dist --no-progress
      
      - name: Run PHPUnit tests
        run: composer run-script test:unit
      
      - name: Run static analysis (PHPStan)
        run: composer run-script analyse
      
      - name: Check for code duplication
        run: composer run-script duplicate-check
      
      - name: Upload coverage to Codecov
        uses: codecov/codecov-action@v3
        with:
          files: ./coverage/coverage.xml
          flags: unittests
          name: codecov-umbrella
```

---

## 📝 composer.json - Scripts Adicionais

Adicione na raiz do `composer.json`:

```json
"scripts": {
    "test": "phpunit",
    "test:unit": "phpunit tests/Unit",
    "test:integration": "phpunit tests/Integration",
    "test:coverage": "phpunit --coverage-html coverage/",
    "analyse": "phpstan analyse src --level=max",
    "duplicate-check": "phpcpd src/",
    "check": [
        "@test",
        "@analyse",
        "@duplicate-check"
    ]
}
```

---

## ✅ Checklist de Implementação

- [ ] Instalar PHPUnit via Composer
- [ ] Criar estrutura de diretórios `tests/`
- [ ] Criar `tests/bootstrap.php`
- [ ] Criar `phpunit.xml`
- [ ] Criar testes unitários básicos
- [ ] Criar testes de integração
- [ ] Criar GitHub Actions workflow
- [ ] Adicionar scripts em composer.json
- [ ] Rodar testes localmente
- [ ] Verificar coverage > 80%
- [ ] Configurar codecov.io
- [ ] Documentar casos de teste críticos

---

## 📚 Referências

- [PHPUnit Documentation](https://phpunit.de/)
- [PHPStan Documentation](https://phpstan.org/)
- [PHPCPD Documentation](https://github.com/sebastianbergmann/phpcpd)
- [Infection Documentation](https://infection.github.io/)

