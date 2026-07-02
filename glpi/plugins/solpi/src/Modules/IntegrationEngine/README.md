# IntegrationEngine (Fase 1 + Fase 2 + Fase 3 + Fase 4 + Fase 5)

Escopo implementado:
- Contratos (interfaces) para adapters/connectors/importers/normalizers/matchers/resolver/merge/queue.
- Envelope canônico de ingestão com correlation_id e idempotency_key.
- Baseline de idempotência usando glpi_plugin_solpi_webhooks.
- Baseline de fila assíncrona usando glpi_plugin_solpi_jobs.
- Baseline de auditoria usando glpi_plugin_solpi_logs.
- Worker inicial para consumo e atualização de status da fila.

Fase 2 implementada:
- Matcher determinístico para company, user e asset.
- Entity Resolver com canonical_id, score de confiança e map de identidades.
- MergeEngine por campo com proteção de campos críticos.
- Registro de conflitos de merge em trilha auditável.
- Endpoint de resolução dry-run para validação de matching sem side effects de fila.

Fase 3 implementada:
- Persistência transacional de domínio para company, user e asset com upsert real.
- Review queue para baixa confiança (workflow de aprovação/rejeição).
- Dead Letter Queue para falhas definitivas com endpoint de replay.
- Worker com fluxo completo: resolve -> merge -> review/persist -> auditoria -> DLQ em falha final.

Fase 4 implementada:
- Similaridade semântica para ranqueamento de candidatos no EntityResolver.
- Projeção automática no Knowledge Graph (nós e arestas) após persistência de domínio.
- Governança de dados com geração de relatório de qualidade operacional.
- Retenção operacional configurável para logs, webhooks e relatórios de qualidade.
- Endpoints de operação: qualidade, retenção e comparação semântica.

Fase 5 implementada:
- AdapterFactory com descoberta de adapters suportados.
- Conectores/adapters por fonte: REST, SOAP, CSV, JSON, XML, SQL, LDAP/AD, FTP, SFTP, e-mail (IMAP) e webhook.
- Endpoint genérico de ingestão por adapter: POST /integration-engine/ingest/adapter.
- Endpoints dedicados por fonte: /integration-engine/ingest/{rest|soap|csv|json|xml|sql|ldap|ftp|sftp|email|webhook}.
- Endpoint de catálogo de adapters: GET /integration-engine/adapters.
- Endpoint de resumo operacional: GET /integration-engine/summary.
- O resumo operacional inclui jobs, batches, review queue, dead letter, checkpoints e quality snapshot.
- REST adapter com paginação configurável (page/offset/cursor) e records_path para payloads aninhados.
- SQL adapter com modo incremental e paginação por LIMIT/OFFSET para cargas volumosas.
- FTP/SFTP adapters com modo list e modo download (conteúdo em text/base64).
- Guardrail de volume no ingest por adapter com max_records e truncamento controlado.
- Respostas da API padronizadas em envelope com `status`, `time`, `data` e `error` quando aplicável.
- Smoke test atualizado em src/Modules/IntegrationEngine/Tests/IntegrationEngineSmokeTest.md.
- Runner CLI de smoke em src/Modules/IntegrationEngine/Tests/IntegrationEngineSmokeRunner.php.
- Self-check local de batch context em src/Modules/IntegrationEngine/Tests/BatchContextSmoke.php.

Fase 6 em andamento:
- Micro-batching configuravel por source e adapter.
- Metadados de lote persistidos no payload enfileirado em _queue_meta.
- batch_size, batch_count, batch_total, batch_index e batch_jobs_in_chunk registrados no fluxo de auditoria do worker.
- Stop conditions reforcadas para REST, SQL, CSV, XML e SOAP.
- Melhor observabilidade para volumes truncados, paginas curtas e batches processados.

Próximos passos (Fase 6):
- Pipeline inicial de classificação implementado em Services/ClassificationService.php.
- Endpoint operacional: POST /integration-engine/classify.
- Baixa confiança em classificação já entra na review_queue (entity_type=classification).
- Checkpoints incrementais por fonte implementados (REST/SQL/LDAP) em ingest por adapter.
- Endpoints de checkpoint: GET /integration-engine/checkpoints, POST /integration-engine/checkpoints/set, POST /integration-engine/checkpoints/reset.
- Próximo incremento: otimização de performance para grandes volumes e particionamento avançado.
