# 🧪 SOLPI Plugin — Guia de Testes

## 📋 Índice

1. [Setup Inicial](#setup-inicial)
2. [Executar Testes](#executar-testes)
3. [Estrutura de Testes](#estrutura-de-testes)
4. [Escrevendo Novos Testes](#escrevendo-novos-testes)
5. [CI/CD](#cicd)
6. [Troubleshooting](#troubleshooting)

---

## Setup Inicial

### 1. Instalar Dependências de Teste
```bash
composer install
```

Isso instala:
- ✅ **PHPUnit 11** — Framework de testes
- ✅ **PHPStan 2** — Análise estática (já estava em require-dev)
- ✅ **PHPCPD 6** — Detecção de código duplicado (já estava em require-dev)

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

### ⚡ Testes Rápidos (Recomendado para desenvolvimento)
```bash
composer run-script test
# Equivalente a: ./vendor/bin/phpunit
```

Executa todos os testes em paralelo (mais rápido).

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
# Gera: coverage/index.html
```

Abre no navegador:
```bash
open coverage/index.html      # macOS
start coverage\index.html     # Windows
xdg-open coverage/index.html  # Linux
```

### 🔍 Análise Estática (PHPStan)
```bash
composer run-script analyse
# Detecta: type errors, undefined methods, etc.
```

### 🚨 Verificar Código Duplicado
```bash
composer run-script duplicate-check
# Encontra: segmentos de código duplicados
```

### ✅ Verificação Completa (Recomendado antes de commit)
```bash
composer run-script check
# Executa: test + analyse + duplicate-check
```

---

## Estrutura de Testes

```
tests/
├── Unit/                      # Testes Unitários
│   ├── ValidationTest.php     # ✅ Validação de payloads
│   └── ConfigurationTest.php  # ✅ Variáveis e configuração
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

### Fixtures (Dados de Teste)

As fixtures contêm dados mock para testes sem chamar APIs reais:

```php
// Usar em um teste:
$fixtures = $this->getTestFixture('zabbix_responses');
$alert = $fixtures['alert_problem'];
```

---

## Escrevendo Novos Testes

### Estrutura Básica de um Unit Test

```php
<?php
namespace SOLPI\Tests\Unit;

use PHPUnit\Framework\TestCase;

class MyFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        // Executado antes de cada teste
    }

    public function testSomethingWorks(): void
    {
        // Arrange (preparar)
        $input = 'some data';

        // Act (executar)
        $result = doSomething($input);

        // Assert (validar)
        $this->assertEquals('expected', $result);
    }

    public function testSomethingFails(): void
    {
        $this->expectException(\Exception::class);
        doSomething('invalid');
    }
}
```

### Executar um Teste Específico

```bash
# Teste específico
./vendor/bin/phpunit tests/Unit/ValidationTest.php

# Método específico
./vendor/bin/phpunit tests/Unit/ValidationTest.php --filter testValidateZabbixPayload

# Com output verbose
./vendor/bin/phpunit --verbose
```

### Usar Fixtures

```php
public function testWithFixture(): void
{
    $zabbixData = $this->getTestFixture('zabbix_responses');
    $alert = $zabbixData['alert_problem'];
    
    $this->assertEquals('PROBLEM', $alert['status']);
}
```

### Mock de Dependências

```php
public function testWithMock(): void
{
    $mock = $this->createMock(\SomeClass::class);
    $mock->method('getValue')
         ->willReturn(42);
    
    $this->assertEquals(42, $mock->getValue());
}
```

---

## CI/CD

### GitHub Actions

Arquivo: `.github/workflows/tests.yml`

Automaticamente executa quando:
- ✅ Push para `main` ou `develop`
- ✅ Pull Request para `main` ou `develop`

#### Workflows Inclusos:

1. **Test** — PHPUnit (PHP 8.3 + 8.4)
2. **Static Analysis** — PHPStan + PHPCPD
3. **PHP Lint** — Validação de sintaxe

#### Ver Status

Acesse:
```
https://github.com/itamarterra/SOLPI/actions
```

#### Upload de Coverage

Coverage reports são automaticamente enviados para [Codecov.io](https://codecov.io):
```
https://codecov.io/gh/itamarterra/SOLPI
```

---

## Métricas de Qualidade

### Coverage Mínimo

Alvo: **80% de cobertura de código**

```bash
# Ver coverage:
composer run-script test:coverage
# Abrir coverage/index.html
```

### PHPStan Level

Atualmente: **level=max** (máximo rigor)

```bash
composer run-script analyse
```

Resolve todos os erros antes de fazer commit!

### Code Duplication

Máximo tolerado: **5% de duplicação**

```bash
composer run-script duplicate-check
```

---

## Troubleshooting

### ❌ PHPUnit não encontrado
```bash
composer install
export PATH="vendor/bin:$PATH"
```

### ❌ Erro: "Class not found"
Verifique se a classe está no caminho correto e o autoloader está ativado:
```bash
composer dumpautoload -o
```

### ❌ Teste passes localmente mas falha no CI
Verifique:
1. Versão do PHP: `php --version`
2. Extensões: `php -m | grep json`
3. Variáveis de ambiente: `env | grep SOLPI_`

### ❌ Memory limit exceeded
```bash
php -d memory_limit=512M ./vendor/bin/phpunit
```

### ❌ Tests muito lentos
```bash
# Executar em paralelo
composer run-script test
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
- ✅ Use descritições claras: `testValidateZabbixPayloadWithMissingFields`
- ✅ Teste casos positivos E negativos
- ✅ Use fixtures para dados complexos
- ✅ Run `composer run-script check` antes de commit
- ✅ Mantenha coverage acima de 80%

### ❌ Evite
- ❌ Testes que dependem de APIs externas (mock them!)
- ❌ Testes que deixam dados no banco (use tearDown)
- ❌ Testes que dependem de ordem de execução
- ❌ Hard-coded timestamps (use time() ou fixtures)
- ❌ Assertions sem mensagens claras

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

5. **Configure Codecov** (opcional):
   - Acesse https://codecov.io
   - Conecte seu repo GitHub
   - Testes vão automaticamente reportar coverage

---

## 📚 Referências

- [PHPUnit 11 Docs](https://phpunit.de/)
- [PHPStan Docs](https://phpstan.org/)
- [PHPCPD Docs](https://github.com/sebastianbergmann/phpcpd)
- [Codecov Setup](https://docs.codecov.io/)

---

**Última atualização:** 2025-07-18  
**Versão:** 1.0  
**Projeto:** SOLPI Plugin para GLPI
