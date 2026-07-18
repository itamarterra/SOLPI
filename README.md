# 🔗 SOLPI — Smart Operations Link for GLPI

**SOLPI** é um plugin profissional para GLPI que integra Zabbix, Evolution API (WhatsApp), e IA em um único platform de operações.

## ✨ Recursos

- 🔔 **Integração Zabbix** — Receba alertas e crie tickets automaticamente
- 📱 **WhatsApp (Evolution)** — Comunique-se diretamente com clientes
- 🤖 **IA Integrada** — Análise de incidentes com IA
- 🔐 **Seguro** — Variáveis de ambiente para secrets
- 📊 **Webhook Handler** — Processe eventos em tempo real

## 📋 Requisitos

- **PHP 8.3+**
- **GLPI 10.0+**
- **Composer** (para gerenciamento de dependências)

## 🚀 Instalação

### 1. Clonar o Plugin
```bash
cd /var/www/glpi/plugins
git clone https://github.com/itamarterra/SOLPI.git solpi
cd solpi
```

### 2. Instalar Dependências
```bash
composer install
```

### 3. Configurar Variáveis de Ambiente

Crie um arquivo `.env`:
```bash
cp .env.example .env
```

Edite `.env` com suas credenciais:
```env
SOLPI_ZABBIX_TOKEN=seu_token_zabbix
SOLPI_EVOLUTION_TOKEN=seu_token_evolution
SOLPI_AI_API_KEY=sua_api_key_ia
SOLPI_WEBHOOK_SECRET=seu_webhook_secret
```

### 4. Ativar o Plugin em GLPI

- Acesse: `Configuração → Plugins`
- Encontre **SOLPI** na lista
- Clique em **Instalar** e depois **Ativar**

## 🧪 Testes

### Rodar Todos os Testes
```bash
composer run-script test
```

### Rodar com Coverage Report
```bash
composer run-script test:coverage
open coverage/index.html
```

### Análise Estática
```bash
composer run-script analyse
```

### Verificação Completa
```bash
composer run-script check
```

Veja [TESTING.md](TESTING.md) para detalhes completos.

## 📁 Estrutura

```
SOLPI/
├── src/
│   ├── Modules/           # Módulos principais (Zabbix, Evolution, IA)
│   ├── Services/          # Serviços (WebhookService, NotificationService)
│   ├── Models/            # Modelos de dados
│   ├── Helpers/           # Funções auxiliares
│   └── stubs/             # Stubs para ambiente de teste
├── tests/
│   ├── Unit/              # Testes unitários
│   ├── Integration/       # Testes de integração
│   └── Fixtures/          # Dados de teste
├── templates/             # Templates GLPI
├── hook.php              # Hooks do plugin
├── setup.php             # Setup do plugin
├── webhook.php           # Handler de webhooks
├── composer.json         # Dependências PHP
├── phpunit.xml          # Configuração PHPUnit
└── TESTING.md           # Guia de testes
```

## 🔧 Configuração

### Webhooks

#### Zabbix
```
URL: https://seu-glpi.com/plugins/solpi/webhook.php?source=zabbix
Formato: JSON
Secret: (use SOLPI_WEBHOOK_SECRET)
```

#### Evolution API
```
URL: https://seu-glpi.com/plugins/solpi/webhook.php?source=evolution
Formato: JSON
Token: (use SOLPI_EVOLUTION_TOKEN)
```

### Variáveis de Ambiente

| Variável | Descrição |
|----------|-----------|
| `SOLPI_ZABBIX_TOKEN` | Token de autenticação Zabbix |
| `SOLPI_EVOLUTION_TOKEN` | Token Evolution API |
| `SOLPI_AI_API_KEY` | API Key para IA |
| `SOLPI_WEBHOOK_SECRET` | Secret para verificar assinatura de webhooks |

## 📖 Documentação

- [TESTING.md](TESTING.md) — Guia completo de testes
- [docs/](docs/) — Documentação adicional

## 🐛 Troubleshooting

### Plugin não aparece em GLPI
1. Verifique se `composer install` foi executado
2. Limpe o cache GLPI: `rm -rf /var/www/glpi/cache/*`
3. Verifique permissões: `chmod -R 755 /var/www/glpi/plugins/solpi`

### Erros de dependências
```bash
composer update
composer dumpautoload -o
```

### Testes falhando
```bash
php -d memory_limit=512M ./vendor/bin/phpunit
```

## 🤝 Contribuindo

1. Fork o repositório
2. Crie uma branch: `git checkout -b feature/sua-feature`
3. Faça suas mudanças
4. Rode testes: `composer run-script check`
5. Commit: `git commit -am 'Add feature'`
6. Push: `git push origin feature/sua-feature`
7. Abra um Pull Request

## 📝 Licença

MIT License — veja [LICENSE](LICENSE) para detalhes.

## 👨‍💻 Autor

**Itamar Terra**  
Email: dev@itamarterra.local  
GitHub: [@itamarterra](https://github.com/itamarterra)

## 🙏 Agradecimentos

- [GLPI Project](https://glpi-project.org/)
- [PHPUnit](https://phpunit.de/)
- [PHPStan](https://phpstan.org/)

---

**SOLPI v2.0.0-alpha** — Making IT Operations Smarter
