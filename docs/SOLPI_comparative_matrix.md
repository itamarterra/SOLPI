# Matriz Comparativa — SOLPI vs OpenClaude vs Hermes vs OpenClaw

Resumo rápido: análise arquitetural e pontos relevantes para projetar o SOLPI Agent.

| Item | SOLPI (plugin GLPI) | OpenClaude | Hermes Agent | OpenClaw |
|---|---:|---|---|---|
| Linguagem | PHP 8.3 (PSR-4) | TypeScript/Node (CLI) | Python 3.11+ | TypeScript/Node (gateway)
| Tipo | GLPI plugin (monolito modular) | Coding-agent CLI / SDK | Self-improving agent framework | Multi-channel gateway + agents
| Arquitetura | MVC-like + modules, `src/` namespaces | CLI, skills, MCP, QueryEngine, plugins | Agent loop, skills, cron, MCP, plugins | Gateway, channels, plugin-sdk, memory host
| Módulos chave | API, Integrations (Zabbix, Evolution), AI, Automation, Scheduler, Jobs | QueryEngine, skills, tools, context, SDK | Skills, tools, providers, memory, scheduler | Channels, sessions, memory, skills, mcp, plugins
| Skills / Plugins | `src/Modules/` e `tools/` (extensão plugin) | `src/skills`, `plugins/` (CLI skills) | `skills/` (hub de skills) | `skills/`, `extensions/`, `plugin-sdk`
| Memory / Persistence | DB (GLPI) + plugin `storage/` | local session state, memdir, ou DB externo | DB-backed memory, busca, session store | memory, memory-host-sdk, state DB
| MCP support | Necessita integração (Evolution API) | suporte MCP cliente/servidor | `mcp` extra / servidor MCP opcional | `mcp/` e runtime plugin-sdk
| Tool execution | Jobs PHP, cron, fila de jobs | chamadas de tools, shell, file tools | tools & toolsets, sandboxing, cron | sandboxed tools, cron, ações de gateway
| Integrações | GLPI, Zabbix, Evolution API, WhatsApp (plugin) | provedores de modelos, Ollama, Gemini, OpenAI | múltiplos providers e gateways | múltiplos canais (WhatsApp, Telegram, Slack...)
| Extensibilidade | Hooks GLPI, módulos PHP | sistema de plugins, perfis de provider | skills, optional-skills, plugins | plugin-sdk, extensions, agents
| Execução de modelo local? | Delegação a provedores externos | sim (abstração de providers) | sim (abstração + portal) | sim (adapters)

Observações:
- SOLPI é um plugin acoplado ao GLPI e ao banco de dados — compatibilidade reversa é crítica.
- Hermes e OpenClaw possuem padrões maduros para lifecycle de skills, memory e sandboxing.
- OpenClaude traz inspiração para UX CLI, QueryEngine e provider abstraction.

Recomendações iniciais para SOLPI Agent:
- Manter `src/` namespaces atuais e introduzir `Agent/` compatível.
- Implementar Engines (Planner, Memory, Executor, Workflow) como módulos injetáveis.
- Usar DB existente + vector-store para RAG (Orama/LanceDB etc.).
- Habilitar MCP mínimo para comunicação com agents externos sem quebrar GLPI.

--
Documento gerado automaticamente na análise inicial.

## Extração inicial dos componentes do `solpi` (plugin)

- `src/Api`: Controllers, Middleware
- `src/Http`: (vazio)
- `src/Integrations`: AI, Email, Evolution, GLPI, WhatsApp, Zabbix
- `src/Models`: DashboardModel.php
- `src/Modules`: AI, Dashboard, Evolution, GLPI, Knowledge, Monitoring, Notifications, Settings, Tickets, WhatsApp, Zabbix

### Componentes AI (`src/AI`)

- `AIKernel.php`, `AIRepository.php`, `AIService.php`
- `Contracts/` (interfaces para AI)
- `Conversation.php`, `Conversation` handling
- `Embeddings/`, `Kernel/`, `Memory/`, `Models/`, `ModelSelector.php`
- `PromptBuilder.php`, `Prompts/`, `Providers/`, `Services/`

### Core framework (`src/Core`)

- `Application` bootstrap e `Kernel.php`
- Entidades base: `BaseEntity.php`, `EntityInterface.php`
- Repositórios/Services base: `BaseRepository.php`, `BaseService.php`, `Repository.php`
- Container/DI: `Container/Container.php`
- EventBus/Events: `EventDispatcher.php`, `Events/`
- Routing/Http helpers: `Router.php`, `Request.php`, `Response.php`
- Session, Cache, Config, Logger, Database helpers

### Knowledge (`src/Knowledge`)

- Importers: `CsvImporter.php`, `JsonImporter.php`, `XmlImporter.php`, `TxtImporter.php`, `HtmlImporter.php`
- Engines: `ImportEngine.php`, `KnowledgeEngine.php`, `KnowledgeGraphEngine.php`, `MemoryEngine.php`, `SyncEngine.php`
- Parsers, Extractors, Repositories, Services

Observação: os diretórios contêm contratos e implementações que permitem implementar engines de Memory, Knowledge e RAG sem quebrar o modelo de persistência atual (DB + storage).

--
Próximo: extrair listas de controllers (`src/Api/Controllers`), providers (`src/AI/Providers`), services (`src/AI/Services`, `src/Knowledge/Services`) e repositories (`src/Core/Repository` e `src/Knowledge/Repositories`) para detalhar a matriz.
