# SOLPI - Fase 1 Plano Semanal

Este plano desdobra a Fase 1 do roadmap em entregas semanais objetivas, com foco em escala, estabilidade e observabilidade.

## Objetivo da Fase 1

Fechar a base operacional da ingestao de dados para que o SOLPI suporte grandes volumes com previsibilidade, baixo custo operacional e rastreabilidade completa.

## Semana 1 - Instrumentacao e contexto de lote

### Objetivo
Garantir que cada ingestao carregue contexto suficiente para auditoria, troubleshooting e rastreio de lotes.

### Tarefas
- Consolidar o campo `_queue_meta` no payload enfileirado.
- Registrar `batch_size`, `batch_count`, `batch_index` e `batch_jobs_in_chunk` na auditoria do worker.
- Manter `records_total`, `records_queued`, `records_duplicate` e `truncated` em todo o fluxo.
- Garantir que falhas e sucessos carreguem contexto de lote no log.

### Arquivos principais
- [IntegrationOrchestratorService.php](../src/Modules/IntegrationEngine/Services/IntegrationOrchestratorService.php)
- [IntegrationEngineWorker.php](../src/Modules/IntegrationEngine/Workers/IntegrationEngineWorker.php)
- [JobRepository.php](../src/Modules/IntegrationEngine/Repositories/JobRepository.php)

### Resultado esperado
- Cada job pode ser rastreado da API ate o worker.
- A auditoria mostra claramente como o lote foi processado.

## Semana 2 - Stop conditions por adaptador

### Objetivo
Reduzir chamadas desnecessarias e evitar carregamento excessivo de dados.

### Tarefas
- Manter `stop_when_short_page` para REST e SQL.
- Revisar `records_path` e normalizacao em REST, XML e SOAP.
- Validar `offset` e `limit` no CSV.
- Confirmar parada antecipada quando o lote vem menor que o esperado.
- Documentar o comportamento em smoke tests.

### Arquivos principais
- [RestApiAdapter.php](../src/Modules/IntegrationEngine/Adapters/RestApiAdapter.php)
- [SqlAdapter.php](../src/Modules/IntegrationEngine/Adapters/SqlAdapter.php)
- [CsvAdapter.php](../src/Modules/IntegrationEngine/Adapters/CsvAdapter.php)
- [XmlAdapter.php](../src/Modules/IntegrationEngine/Adapters/XmlAdapter.php)
- [SoapAdapter.php](../src/Modules/IntegrationEngine/Adapters/SoapAdapter.php)

### Resultado esperado
- Menos chamadas por carga.
- Menor uso de memoria em fontes grandes.
- Paginação mais previsivel.

## Semana 3 - Checkpoints e replay operacional

### Objetivo
Fortalecer a retomada de ingestao sem duplicacao e sem perda.

### Tarefas
- Revisar checkpoints para REST, SQL e LDAP.
- Validar persistencia de `checkpoint_in` e `checkpoint_out`.
- Melhorar metadados de checkpoint no retorno da API.
- Garantir replay limpo em caso de falha ou interrupcao.

### Arquivos principais
- [IntegrationOrchestratorService.php](../src/Modules/IntegrationEngine/Services/IntegrationOrchestratorService.php)
- [SourceCheckpointService.php](../src/Modules/IntegrationEngine/Services/SourceCheckpointService.php)
- [SourceCheckpointRepository.php](../src/Modules/IntegrationEngine/Repositories/SourceCheckpointRepository.php)

### Resultado esperado
- A ingestao pode ser retomada com seguranca.
- O operador sabe exatamente qual foi o ultimo ponto processado.

## Semana 4 - Smoke, metrica e estabilizacao

### Objetivo
Fechar a Fase 1 com validacao operacional.

### Tarefas
- Expandir o smoke runner para cobrir lote, checkpoint e parada antecipada.
- Ajustar a documentacao do motor de integracao com exemplos reais.
- Consolidar metricas de lote e resposta de API.
- Revisar status e metadados do worker para facilitar suporte.

### Arquivos principais
- [IntegrationEngineSmokeRunner.php](../src/Modules/IntegrationEngine/Tests/IntegrationEngineSmokeRunner.php)
- [IntegrationEngineSmokeTest.md](../src/Modules/IntegrationEngine/Tests/IntegrationEngineSmokeTest.md)
- [IntegrationEngine README](../src/Modules/IntegrationEngine/README.md)

### Resultado esperado
- Fase 1 fica validada por teste e documentacao.
- O fluxo de ingestao entra no proximo ciclo pronto para conhecimento e IA.

## Critérios de pronto da Fase 1

- Jobs com contexto de lote observavel.
- Paginação e parada antecipada funcionando nos principais adapters.
- Checkpoints consistentes e reutilizaveis.
- Smoke test cobrindo o caminho operacional.
- Documentacao alinhada com o codigo entregue.

## Referencias

- [SOLPI Technical Roadmap](SOLPI-Technical-Roadmap.md)
- [SOLPI 90 Days Plan](SOLPI-90-Dias-Plano.md)
- [Integration Engine Architecture Blueprint](SOLPI-IntegrationEngine-Architecture.md)
