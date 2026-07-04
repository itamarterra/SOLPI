# SOLPI Agent — Installation Registry & Auto-Registration (Design)

Objetivo
- Permitir que toda instalação do SOLPI (plugin) atue como um agente responsável por "tudo" localmente, descobrindo, reportando e interligando componentes instalados; fornecer uma visão (dashboard) que lista todos os sites onde o SOLPI está em uso e mostra inventário/estado.

Princípios
- Não quebrar compatibilidade com GLPI, Plugin SOLPI, Zabbix, Evolution API, DB ou API existente.
- Não copiar código dos projetos de referência; usar apenas como inspiração arquitetural.
- Implementar como novos módulos/namespace (`Agent/Registry`, `Agent/Runtime`) e migrações DB, mantendo arquivos existentes.
- Segurança: comunicação autenticada (HMAC/JWT) e TLS obrigatório.

Componentes principais
- Agent Runtime (local): componente PHP leve executado dentro do ambiente do plugin (ou como worker separado) que:
  - executa descoberta local (processos, containers, integrações configuradas, endpoints),
  - coleta inventário (services, versões, endpoints expostos),
  - envia registro inicial e heartbeats periódicos para API de registro,
  - aceita comandos de diagnóstico (opcional, via MQ ou MCP).

- Agent Registry API (server-side, parte do plugin): endpoints REST dentro do plugin GLPI para:
  - `POST /agent/register` — registro inicial (site, url, version, capabilities, public_key/hmac_id),
  - `POST /agent/heartbeat` — heartbeat curto com last_seen, status, metrics,
  - `GET /agent/installations` — listar instalações (para dashboard), paginado/filtrado,
  - `GET /agent/installations/{id}` — detalhes e inventário,
  - `POST /agent/report` — envio de inventário completo (opt-in, após autorização),
  - `DELETE /agent/{id}` — desregistrar (manual/admin).

Banco de Dados (migração)
- Nova tabela `solpi_installations` (exemplo):

| coluna | tipo | descrição |
|---|---:|---|
| id | BIGINT AUTO_INCREMENT | PK |
| site_name | VARCHAR(255) | nome amigável |
| site_url | VARCHAR(1024) | URL pública/proxy |
| glpi_version | VARCHAR(64) | versão GLPI |
| solpi_version | VARCHAR(64) | versão do plugin |
| ip_address | VARCHAR(64) | opcional |
| capabilities | JSON | features presentes (zabbix,evolution,whatsapp...) |
| inventory | JSON | último inventário completo (opt-in) |
| status | VARCHAR(32) | online/offline/error |
| last_seen | DATETIME | timestamp do último heartbeat |
| auth_token | VARCHAR(256) | chave HMAC/JWT compartilhada (hash armazenado) |
| created_at | DATETIME | |
| updated_at | DATETIME | |

- Migrar usando um arquivo SQL em `sql/migrations/` e registrar no instalador do plugin.

Segurança
- Durante registro inicial, usar um shared secret gerado pelo servidor ou o servidor aceitar um CSR (public key) do agente.
- Recomendado: servidor gera um `auth_token` único, devolve para o agente; agente armazena local, usa HMAC para assinar requests.
- Todas as comunicações via HTTPS e validar timestamps/nonce para mitigar replay.

Fluxo de registro (exemplo)
1. Administrador instala/atualiza plugin SOLPI.
2. Agent Runtime detecta que ainda não está registrado e chama `POST /agent/register` com payload básico (site, url, solpi_version, capabilities).
3. Server cria entrada em `solpi_installations`, gera `auth_token` e retorna (ou pede aprovação manual via UI, dependendo da política).
4. Agent armazena token e passa a enviar `POST /agent/heartbeat` periodicamente (ex.: cada 60s/5m).
5. Dashboard (`/admin/solpi/agents`) consome `GET /agent/installations` e apresenta lista com status, last_seen, capacidades e link para detalhes.

Política de aprovação
- Opções configuráveis:
  - Auto-approve: qualquer instalação que se autentique é lista automaticamente.
  - Manual-approve: administradores aprovam novos registros antes que inventário detalhado seja aceito.

Inventário e privacidade
- Inventário completo só é enviado quando o admin permitir (opt-in) — por padrão enviar apenas metadados e capacidades.
- Inventário pode incluir: containers Docker, serviços ativos, integrações configuradas (Zabbix, Evolution), endpoints expostos.
- Suportar redaction: campos sensíveis são mascarados antes de armazenar.

UI / Dashboard (plugin)
- Nova seção em `Dashboard` ou `Admin` com:
  - Lista de instalações (filtro por status, versão, capacidade),
  - Página de detalhes com inventário, logs recentes, ações (request sync, run diagnostic),
  - Export CSV / JSON e links para acesso remoto seguro (se configurado).

Compatibilidade e impacto
- NÃO alterar tabelas GLPI core; adicionar novas tabelas no schema do plugin.
- Evitar tarefas blocking no lifecycle do plugin — agent runtime executa em worker/crontab assíncrono.
- Fazer testes de carga: muitos sites reportando heartbeats podem gerar tráfego; usar pooling e rate-limits.

Roadmap curto (sprints)
1. Finalizar design do registro (este documento) — revisar com you/admin.
2. Criar migração DB e modelos PHP (`Agent/Registry/Installation.php`, `Agent/Repository/InstallationRepository.php`).
3. Implementar endpoints REST e testes (unitários + integração em container).
4. Implementar `Agent/Runtime` como worker PHP com discovery básico e registro/heartbeat.
5. UI: listar instalações + detalhes.
6. Harden security: HMAC, TLS, approval flows.

Próximos passos imediatos
- Confirmar política de aprovação (auto/manual) e quais campos de inventário são permitidos.
- Deseja que eu crie o arquivo de migração SQL inicial e o esqueleto de `Agent/Registry` e um mock de API (sem implementar lógica)? Se sim, eu preparo o patch inicial com migração e stubs compatíveis (não sobrescrever arquivos existentes).
