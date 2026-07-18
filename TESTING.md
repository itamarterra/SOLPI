# 🧪 SOLPI Plugin — Guia de Testes

## 📋 Índice

1. [Setup Inicial](#setup-inicial)
2. [Executar Testes](#executar-testes)
3. [Estrutura de Testes](#estrutura-de-testes)
4. [Escrevendo Novos Testes](#escrevendo-novos-testes)
5. [CI/CD GitHub Actions](#cicd-github-actions)

---

## Setup Inicial

### 1. Instalar Dependências
```bash
cd /path/to/SOLPI
composer install
```

Isso instala:
- ✅ **PHPUnit 11** — Framework de testes unitários
- ✅ **PHPStan 2** — Análise estática de código
- ✅ **PHPCPD 6** — Detecção de código duplicado

### 2. Verificar PHP Version
```bash
php --version
# Requer PHP 8.3+
```

### 3. Validar Instalação
```bash
composer validate --strict
./vendor/bin/phpunit --version
./vendor/bin/phpstan --version
./vendor/bin/phpcpd --version
```

---

## Executar Testes

### ⚡ Todos os Testes
```bash
composer run-script test
# Equivalente a: ./vendor/bin/phpunit
```

### 📊 Apenas Testes Unitários
```bash
composer run-script test:unit
# tests/Unit
```

### 🔗 Apenas Testes de Integração
```bash
composer run-script test:integration
# tests/Integration
```

### 📈 Testes com Coverage Report
```bash
composer run-script test:coverage
# Gera relatório em: coverage/index.html
```

Abrir no navegador:
```bash
# macOS
open coverage/index.html

# Windows
start coverage\index.html

# Linux
xdg-open coverage/index.html
```

### 🔍 Análise Estática (PHPStan)
```bash
composer run-script analyse
# Detecta: type errors, undefined methods, code smells
```

### 🚨 Verificar Código Duplicado
```bash
composer run-script duplicate-check
# Encontra: segmentos de código repetidos
```

### ✅ Verificação Completa (Antes de Commit)
```bash
composer run-script check
# Executa: test + analyse + duplicate-check
```

---

## Estrutura de Testes

```
tests/
├── Unit/                      # Testes Unitários
│   ├── ValidationTest.php     # Validação de payloads
│   └── ConfigurationTest.php  # Configuração do plugin
│
├── Integration/               # Testes de Integração
│   └── (será adicionado conforme necessário)
│
├── Fixtures/                  # Dados de Teste
│   ├── zabbix_responses.php
│   ├── evolution_responses.php
│   └── webhook_payloads.php
│
└── bootstrap.php              # Setup (executado automaticamente)

phpunit.xml                   # Configuração PHPUnit
```

### Usar Fixtures em Testes

```php
public function testWithFixture(): void
{
    $zabbixData = $this->getTestFixture('zabbix_responses');
    $alert = $zabbixData['alert_problem'];
    
    $this->assertEquals('PROBLEM', $alert['status']);
}
```

---

## Escrevendo Novos Testes

### Estrutura Básica

```php
<?php
namespace SOLPI\Tests\Unit;

use PHPUnit\Framework\TestCase;
use SOLPI\Modules\ZabbixIntegration;

class ZabbixIntegrationTest extends TestCase
{
    private ZabbixIntegration $zabbix;

    protected function setUp(): void
    {
        $this->zabbix = new ZabbixIntegration();
    }

    public function testProcessAlert(): void
    {
        // Arrange (preparar dados)
        $alert = [
            'trigger_id' => '12345',
            'status' => 'PROBLEM',
            'hostname' => 'server-01',
        ];

        // Act (executar ação)
        $result = $this->zabbix->processAlert($alert);

        // Assert (validar resultado)
        $this->assertIsArray($result);
        $this->assertArrayHasKey('notification_id', $result);
    }

    public function testRejectInvalidAlert(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $invalid = ['status' => 'PROBLEM']; // missing required fields
        $this->zabbix->processAlert($invalid);
    }
}
```

### Executar Teste Específico

```bash
# Teste específico
./vendor/bin/phpunit tests/Unit/ZabbixIntegrationTest.php

# Método específico
./vendor/bin/phpunit tests/Unit/ZabbixIntegrationTest.php --filter testProcessAlert

# Com verbose output
./vendor/bin/phpunit --verbose
```

### Mock de Dependências

```php
public function testWithMock(): void
{
    $mock = $this->createMock(\SOLPI\Services\WebhookService::class);
    $mock->method('validate')
         ->willReturn(true);
    
    $this->assertTrue($mock->validate([]));
}
```

---

## CI/CD GitHub Actions

### Arquivo: `.github/workflows/tests.yml`

Automaticamente executa quando:
- ✅ Push para `main` ou `develop`
- ✅ Pull Request para `main` ou `develop`

### Workflows Inclusos:

1. **PHPUnit** — Testa PHP 8.3 + 8.4
2. **PHPStan** — Análise estática (level=max)
3. **PHPCPD** — Detecção de duplicação
4. **PHP Lint** — Validação de sintaxe

### Ver Status

Acesse: https://github.com/itamarterra/SOLPI/actions

---

## Métricas de Qualidade

### Coverage Mínimo

Alvo: **80% de cobertura de código**

```bash
composer run-script test:coverage
open coverage/index.html
```

### PHPStan Level

Configurado: **level=max** (máximo rigor)

```bash
composer run-script analyse
```

Resolva todos os erros antes de fazer commit!

---

## Troubleshooting

### ❌ PHPUnit não encontrado
```bash
composer install
export PATH="vendor/bin:$PATH"
```

### ❌ Erro: "Class not found"
```bash
composer dumpautoload -o
composer run-script test
```

### ❌ Memory limit exceeded
```bash
php -d memory_limit=512M ./vendor/bin/phpunit
```

### ❌ Tests muito lentos
Rode apenas um teste específico:
```bash
./vendor/bin/phpunit tests/Unit/ValidationTest.php
```

### ❌ Fixture não encontrada
Verifique:
1. Arquivo existe em `tests/Fixtures/`
2. Nome está correto
3. Extensão é `.php`

---

## Boas Práticas

### ✅ Faça
- ✅ Escreva testes ANTES de implementar (TDD)
- ✅ Use nomes descritivos: `testValidateZabbixPayloadWithMissingFields`
- ✅ Teste casos positivos E negativos
- ✅ Use fixtures para dados complexos
- ✅ Run `composer run-script check` antes de commit
- ✅ Mantenha coverage acima de 80%

### ❌ Evite
- ❌ Testes que dependem de APIs externas (mock them!)
- ❌ Testes que deixam dados no banco (use tearDown)
- ❌ Testes que dependem de ordem de execução
- ❌ Hard-coded timestamps (use time() ou fixtures)

---

## Próximos Passos

1. **Instale as dependências:**
   ```bash
   composer install
   ```

2. **Execute os testes existentes:**
   ```bash
   composer run-script test
   ```

3. **Verifique coverage:**
   ```bash
   composer run-script test:coverage
   ```

4. **Escreva novos testes** para seus features

5. **Rode análise completa antes de commit:**
   ```bash
   composer run-script check
   ```

---

## 📚 Referências

- [PHPUnit 11 Docs](https://phpunit.de/)
- [PHPStan Docs](https://phpstan.org/)
- [PHPCPD Docs](https://github.com/sebastianbergmann/phpcpd)
- [GLPI Plugin Development](https://glpi-project.org/)

---

**Versão:** 1.0  
**Última atualização:** 2025-07-18  
**Projeto:** SOLPI Plugin para GLPI
