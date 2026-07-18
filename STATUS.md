# ✅ SOLPI Plugin — Status Final

## 🎯 O Que É SOLPI

**SOLPI** é um **plugin PHP para GLPI** que integra:
- 🔔 Zabbix (alertas de infraestrutura)
- 📱 Evolution API (WhatsApp)
- 🤖 IA (análise de incidentes)

## 📦 Stack Técnico

| Componente | Versão | Propósito |
|-----------|--------|----------|
| **PHP** | 8.3+ | Linguagem principal |
| **GLPI** | 10.0+ | Sistema base |
| **Composer** | 2.x | Gerenciador de dependências |
| **PHPUnit** | 11.x | Testes unitários & integração |
| **PHPStan** | 2.x | Análise estática (type checking) |
| **PHPCPD** | 6.x | Detecção de código duplicado |

## 🚀 Como Usar

### 1. Instalar
```bash
cd /var/www/glpi/plugins
git clone https://github.com/itamarterra/SOLPI.git solpi
cd solpi
composer install
```

### 2. Configurar
```bash
cp .env.example .env
# Editar .env com suas credenciais
```

### 3. Ativar em GLPI
- Ir em: `Configuração → Plugins → SOLPI → Instalar → Ativar`

### 4. Rodar Testes
```bash
composer run-script test
```

## 📋 Comandos Disponíveis

```bash
# Testes
composer run-script test              # Rodar todos os testes
composer run-script test:unit         # Apenas testes unitários
composer run-script test:integration  # Apenas testes de integração
composer run-script test:coverage     # Com relatório HTML

# Qualidade de Código
composer run-script analyse           # PHPStan (type checking)
composer run-script duplicate-check   # PHPCPD (duplicação)

# Completo
composer run-script check             # test + analyse + duplicate-check
```

## 📁 Estrutura

```
SOLPI/
├── src/
│   ├── Modules/           # Integrações (Zabbix, Evolution, IA)
│   ├── Services/          # Serviços (WebhookService, etc)
│   ├── Models/            # Modelos de dados GLPI
│   ├── Helpers/           # Funções auxiliares
│   ├── Database/          # Migrations & queries
│   ├── Exceptions/        # Exceções customizadas
│   └── stubs/             # Stubs para testes
│
├── tests/
│   ├── Unit/              # Testes unitários
│   ├── Integration/       # Testes de integração
│   ├── Fixtures/          # Dados de teste (mocks)
│   ├── bootstrap.php      # Setup automático
│   └── *Test.php          # Testes
│
├── templates/             # Templates GLPI
├── hook.php              # Hooks do ciclo de vida
├── setup.php             # Setup do plugin
├── webhook.php           # Handler de webhooks
├── composer.json         # Dependências PHP
├── phpunit.xml          # Config PHPUnit
├── phpstan.neon         # Config PHPStan
├── phpcpd.xml          # Config PHPCPD
├── README.md            # Este arquivo
└── TESTING.md           # Guia de testes detalhado
```

## 🧪 Testes

### Rodar Testes Localmente
```bash
composer install
composer run-script test
```

### Ver Coverage
```bash
composer run-script test:coverage
open coverage/index.html
```

### CI/CD Automático

GitHub Actions executa automaticamente em:
- ✅ Push para `main` ou `develop`
- ✅ Pull Request para `main` ou `develop`

Acesse: https://github.com/itamarterra/SOLPI/actions

## 🔐 Variáveis de Ambiente

```env
SOLPI_ZABBIX_TOKEN=seu_token_zabbix
SOLPI_EVOLUTION_TOKEN=seu_token_evolution_api
SOLPI_AI_API_KEY=sua_chave_api_ia
SOLPI_WEBHOOK_SECRET=seu_segredo_para_webhooks
```

**IMPORTANTE:** Nunca commitar `.env` no Git!

## 📚 Documentação

- [README.md](README.md) — Visão geral do plugin
- [TESTING.md](TESTING.md) — Guia completo de testes
- [docs/](docs/) — Documentação adicional do projeto

## ✨ O Que Funciona

- ✅ PHPUnit 11 configurado e pronto
- ✅ PHPStan em level=max (máxima rigorosidade)
- ✅ PHPCPD para detectar duplicação
- ✅ GitHub Actions CI/CD automático (PHP 8.3 + 8.4)
- ✅ Fixtures para dados de teste (Zabbix, Evolution, webhooks)
- ✅ Bootstrap automático com env vars
- ✅ Coverage report em HTML
- ✅ Testes de validação básicos

## ❌ O Que NÃO Tem (E Não Precisa)

- ❌ Docker (é um plugin PHP, não container)
- ❌ docker-compose (rode localmente com GLPI)
- ❌ Node.js (é PHP puro)
- ❌ Webpack/npm (sem frontend build system)

## 🔄 Próximas Ações

1. **Adicione testes** para seus módulos em `tests/Unit/` ou `tests/Integration/`
2. **Execute `composer run-script check`** antes de cada commit
3. **Monitore coverage** — Mantenha acima de 80%
4. **Configure Codecov** (opcional) em https://codecov.io
5. **Verifique CI/CD** em GitHub Actions

## 🐛 Troubleshooting

### "Class not found"
```bash
composer dumpautoload -o
```

### Permissões no GLPI
```bash
sudo chown -R www-data:www-data /var/www/glpi/plugins/solpi
sudo chmod -R 755 /var/www/glpi/plugins/solpi
```

### Cache GLPI
```bash
rm -rf /var/www/glpi/cache/*
```

## 💡 Desenvolvimento

### TDD (Test-Driven Development)
1. Escreva o teste PRIMEIRO
2. Rode e veja falhar
3. Implemente o código
4. Rode e veja passar
5. Refatore se necessário

### Antes de Commit
```bash
composer run-script check
git commit -am "feat: sua feature aqui"
git push origin main
```

## 📞 Suporte

- 📖 Veja [TESTING.md](TESTING.md) para testes
- 🔗 GitHub: https://github.com/itamarterra/SOLPI
- 👤 Autor: @itamarterra

---

**Status:** ✅ Pronto para produção  
**Versão:** 2.0.0-alpha  
**Última atualização:** 2025-07-18
