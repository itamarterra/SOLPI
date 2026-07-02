# SOLPI - Fase 1 - Semana 1 Checklist

Objetivo: transformar o contexto de lote em algo auditavel, observavel e util para operacao e suporte.

## Checklist tecnico

### 1. Estruturar o contexto de lote no payload
- [ ] Garantir que `_queue_meta` esteja presente em todo job gerado por ingestao por adapter.
- [ ] Confirmar que `_queue_meta` carregue `adapter`, `source`, `event`, `batch_size`, `batch_count`, `batch_index` e `job_index`.
- [ ] Confirmar que `_queue_meta` carregue `records_total`, `records_queued`, `records_duplicate` e `truncated`.
- [ ] Preservar `checkpoint_enabled`, `checkpoint_name`, `checkpoint_in` e `checkpoint_out` quando checkpoint estiver ativo.

### 2. Registrar contexto no worker
- [ ] Ler `_queue_meta` no worker antes do processamento do payload canonico.
- [ ] Incluir `batch_index`, `batch_count`, `batch_size` e `batch_jobs_in_chunk` na auditoria de sucesso.
- [ ] Incluir `batch_index` e `batch_count` na auditoria de erro.
- [ ] Garantir que o worker continue processando mesmo quando `_queue_meta` estiver ausente.

### 3. Consolidar persistencia da fila
- [ ] Validar que `enqueueBatch()` persista cada job com payload JSON valido.
- [ ] Garantir que lote vazio retorne array vazio sem escrita no banco.
- [ ] Confirmar que IDs retornados correspondam aos jobs inseridos.

### 4. Documentar comportamento operacional
- [ ] Explicar o papel de `_queue_meta` no README do IntegrationEngine.
- [ ] Atualizar exemplos de API para mostrar o contexto de lote.
- [ ] Registrar como interpretar lotes, duplicados e truncamento.

### 5. Validar com smoke
- [ ] Adicionar um caso de smoke para ingestao em lote.
- [ ] Simular um lote truncado e confirmar metricas.
- [ ] Simular um job com checkpoint e validar o contexto final no log.

## Arquivos principais

- [IntegrationOrchestratorService.php](../src/Modules/IntegrationEngine/Services/IntegrationOrchestratorService.php)
- [IntegrationEngineWorker.php](../src/Modules/IntegrationEngine/Workers/IntegrationEngineWorker.php)
- [JobRepository.php](../src/Modules/IntegrationEngine/Repositories/JobRepository.php)
- [IntegrationEngine README](../src/Modules/IntegrationEngine/README.md)
- [IntegrationEngineSmokeRunner.php](../src/Modules/IntegrationEngine/Tests/IntegrationEngineSmokeRunner.php)

## Definicao de pronto

- O lote aparece desde a chamada de ingestao ate o log final do worker.
- O suporte consegue rastrear cada job por contexto e origem.
- O comportamento de lote fica documentado e testavel.
