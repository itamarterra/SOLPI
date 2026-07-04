# SOLPI — Setup & Runbook (melhor opção para execução completa)

Objetivo: fornecer um caminho reproduzível e confiável para rodar o ecossistema SOLPI (GLPI + plugin SOLPI) e ambientes de referência (OpenClaude, Hermes, OpenClaw) usados apenas como referência arquitetural.

Requisitos mínimos
- Host: Linux (preferível) ou Windows com WSL2.
- Docker & Docker Compose (ou Docker Desktop) instalados.
- PHP 8.3 & Composer (para trabalhar com o plugin localmente).
- Node.js 22+ e npm/pnpm (para OpenClaude / OpenClaw referência).
- Python 3.11+ (para Hermes referência).

Variáveis de ambiente obrigatórias (definir antes):
- `SOLPI_ZABBIX_TOKEN`
- `SOLPI_EVOLUTION_TOKEN`
- `SOLPI_AI_API_KEY`
- `SOLPI_WEBHOOK_SECRET`

1) Executar GLPI + Banco de Dados (Docker)

Exemplo usando o compose do workspace (ajuste conforme necessário):

```bash
cd c:/SOLPI
docker compose -f glpi/docker-compose.yml up -d
```

Verificar containers:

```bash
docker ps
```

2) Instalar o plugin SOLPI no container GLPI

Assumindo que o código do plugin está em `glpi/plugins/solpi` no workspace:

```bash
# copiar (se necessário) e ajustar permissões
docker cp glpi/plugins/solpi <glpi_container>:/var/www/html/plugins/solpi
docker exec -it <glpi_container> bash
cd /var/www/html/plugins/solpi
composer install --no-dev --optimize-autoloader
chown -R www-data:www-data /var/www/html/plugins/solpi
exit
```

Abra o painel GLPI (http://localhost:PORT) e ative o plugin via Interface Web (Setup → Plugins). Execute qualquer migration SQL presente em `glpi/plugins/solpi/sql` seguindo as instruções do plugin.

3) Configurar variáveis de ambiente e segredos

Defina as variáveis listadas acima no ambiente do container (ou em um secrets manager). Para debug local, exporte no shell antes de iniciar serviços.

4) Agendadores / Jobs

Se o plugin usa cron/scheduler (ver `src/Scheduler`), configure um cron ou execute o worker dentro do container:

```bash
docker exec -it <glpi_container> bash
php /var/www/html/plugins/solpi/bin/worker.php
```

5) Integrar com Zabbix / Evolution / WhatsApp

Preencha tokens e endpoints no painel do plugin (Config → SOLPI) ou via variáveis de ambiente. Teste cada integração individualmente (ex.: chamada de teste Zabbix, webhook Evolution).

6) Componentes de referência (opcionais — NÃO copiar código)

- Hermes (Python) — leia e rode para estudar patterns: `research/hermes-agent/hermes-agent-main`.
- OpenClaude (Node) — referência para QueryEngine e provider abstraction: `research/openclaude/openclaude-main`.
- OpenClaw (Node) — referência para gateway multi-channel e memory-host: `research/openclaw/openclaw-mine-main`.

Comandos rápidos (referência):

```bash
# Hermes (ex.: criar venv e rodar)
cd research/hermes-agent/hermes-agent-main
./setup-hermes.sh
hermes

# OpenClaude (dev)
cd research/openclaude/openclaude-main
npm install
npm run dev

# OpenClaw (dev)
cd research/openclaw/openclaw-mine-main
npm install
node openclaw.mjs
```

7) Recomendação de arquitetura de execução (melhor opção)

- Rodar GLPI + MySQL/MariaDB via Docker Compose para isolar e garantir compatibilidade.
- Executar o plugin dentro do container GLPI (como código PHP do plugin), com Composer instalado no build/container.
- Manter agentes e componentes experimentais (Hermes/OpenClaude/OpenClaw) fora do ambiente de produção; rodar em VMs separadas ou em workstations de desenvolvimento.
- Habilitar backup automático do banco de dados antes de rodar migrações.
- Monitorar logs: `docker logs -f <glpi_container>` e `glpi/logs/`.

8) Checklist de validação (para considerar "100% rodando")

- GLPI acessível e plugin `SOLPI` ativado.
- Migrations aplicadas sem erros.
- Integrações (Zabbix, Evolution) respondendo a chamadas de teste.
- Jobs/cron funcionando e processando filas.
- Variáveis de ambiente com segredos configuradas via secrets manager ou env de container.
- Backups agendados e restauráveis.

Próximos passos recomendados pelo time técnico:
- Produzir `SOLPI_AGENT_ARCHITECTURE.md` (alta-nível) antes de implementar módulos.
- Definir interface de `Agent/` compatível com GLPI (contratos PHP interfaces) e implementar adaptadores para Memory e Executor.
- Provar integração RAG com uma PoC (vector store local) usando dados não sensíveis.
