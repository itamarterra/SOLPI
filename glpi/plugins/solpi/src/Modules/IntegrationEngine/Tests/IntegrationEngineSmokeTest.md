# IntegrationEngine Smoke Test (Fase 6)

## Pre requisitos
- API ativa em /glpi/plugins/solpi/api/index.php
- Segredo configurado em SOLPI_WEBHOOK_SECRET (ou evolution.auth_key)

Base URL recomendada no ambiente docker deste repositorio:
- http://localhost:8081/solpi/index.php

## Runner automatizado (recomendado)

Execute o script unico com validacao de respostas esperadas:

- `php src/Modules/IntegrationEngine/Tests/IntegrationEngineSmokeRunner.php --base-url="http://localhost:8081/solpi/index.php" --api-key="SEU_SEGREDO"`

Fluxo coberto pelo runner:
- GET /integration-engine/adapters
- POST /integration-engine/ingest
- POST /integration-engine/ingest/adapter (json)
- POST /integration-engine/ingest/adapter (json truncado + checkpoint)
- POST /integration-engine/worker/run-once
- POST /integration-engine/classify
- GET /integration-engine/jobs?limit=10
- GET /integration-engine/summary

Observacao operacional:
- O runner gera um run_id unico por execucao para evitar falsos duplicados e garantir que o summary reflita metadados reais de lote/checkpoint/truncamento.

## 1) Descobrir adapters suportados
GET /integration-engine/adapters

Resposta esperada:
- items com rest, soap, csv, json, xml, sql, ldap, active_directory, ftp, sftp, email, webhook

## 2) Ingestao direta (baseline)
POST /integration-engine/ingest

Body minimo:
{
	"apikey": "SEU_SEGREDO",
	"source": "smoke_baseline",
	"event": "upsert",
	"payload": {
		"entity_type": "company",
		"name": "ACME Smoke",
		"email": "smoke@acme.test"
	}
}

Resposta esperada:
- status=queued e job_id

## 3) Ingestao por adapter (json)
POST /integration-engine/ingest/adapter

Body minimo:
{
	"apikey": "SEU_SEGREDO",
	"source": "smoke_json_adapter",
	"event": "upsert",
	"adapter": "json",
	"payload": {
		"data": [
			{
				"entity_type": "user",
				"email": "user1@acme.test",
				"name": "User One"
			},
			{
				"entity_type": "user",
				"email": "user2@acme.test",
				"name": "User Two"
			}
		]
	}
}

Resposta esperada:
- status=queued
- records_total=2
- records_queued=2 (ou duplicates > 0 em reexecucao)

## 4) Worker run once
POST /integration-engine/worker/run-once

Body:
{
	"apikey": "SEU_SEGREDO",
	"limit": 20
}

Resposta esperada:
- status=ok
- processed >= 1

## 5) Verificar filas e DLQ
- GET /integration-engine/jobs
- GET /integration-engine/review
- GET /integration-engine/dead-letter

## 6) Validar idempotencia
Repetir exatamente a requisicao do passo 2.

Resposta esperada:
- status=duplicate

## 7) Cenarios dedicados por fonte
Use os endpoints:
- POST /integration-engine/ingest/rest
- POST /integration-engine/ingest/soap
- POST /integration-engine/ingest/csv
- POST /integration-engine/ingest/json
- POST /integration-engine/ingest/xml
- POST /integration-engine/ingest/sql
- POST /integration-engine/ingest/ldap
- POST /integration-engine/ingest/ftp
- POST /integration-engine/ingest/sftp
- POST /integration-engine/ingest/email
- POST /integration-engine/ingest/webhook

## 8) Classificacao (Fase 6 inicial)
POST /integration-engine/classify

Body minimo:
{
	"apikey": "SEU_SEGREDO",
	"record": {
		"correlation_id": "classify-smoke-001",
		"text": "Servidor indisponivel com erro critico apos atualizacao de release"
	}
}

Resposta esperada:
- status=classified ou status=review_required
- category e confidence preenchidos

## 9) Checkpoints por fonte (Fase 6)
Exemplo de ingestao incremental SQL com checkpoint automatico:

POST /integration-engine/ingest/adapter

{
	"apikey": "SEU_SEGREDO",
	"source": "erp_sql",
	"event": "upsert",
	"adapter": "sql",
	"context": {
		"checkpoint": {
			"enabled": true,
			"name": "companies_sync"
		}
	},
	"payload": {
		"dsn": "mysql:host=db;dbname=erp",
		"user": "usuario",
		"password": "senha",
		"query": "SELECT id, updated_at, name, email FROM companies",
		"incremental": {
			"enabled": true,
			"column": "updated_at",
			"operator": ">"
		}
	}
}

Campos esperados na resposta:
- checkpoint_in
- checkpoint_out
- checkpoint_saved

## 9.1) Truncamento + checkpoint (json adapter)

Use um payload com mais de um registro e force max_records=1.

Campos esperados na resposta:
- truncated=true
- records_total=1
- records_duplicate >= 0
- checkpoint_enabled=true
- checkpoint_name preenchido
- checkpoint_out preenchido quando checkpoint_field for informado

## 10) Batch context local

Execute o self-check local:

- `php src/Modules/IntegrationEngine/Tests/BatchContextSmoke.php`

Resultado esperado:

- `BatchContextSmoke OK`

## 11) Summary calculator local

Execute o self-check local:

- `php src/Modules/IntegrationEngine/Tests/IntegrationSummaryCalculatorSmoke.php`

Resultado esperado:

- `IntegrationSummaryCalculatorSmoke OK`

## 12) SQL adapter local

Execute o self-check local:

- `php src/Modules/IntegrationEngine/Tests/SqlAdapterSmoke.php`

Resultado esperado:

- `SqlAdapterSmoke OK`

## 13) CSV and XML adapters local

Execute o self-check local:

- `php src/Modules/IntegrationEngine/Tests/CsvXmlAdapterSmoke.php`

Resultado esperado:

- `CsvXmlAdapterSmoke OK`

## 14) Worker failure and replay local

Execute o self-check local:

- `php src/Modules/IntegrationEngine/Tests/WorkerFailureSmoke.php`

Resultado esperado:

- `WorkerFailureSmoke OK`

## 15) REST e SOAP validations local

Execute o self-check local:

- `php src/Modules/IntegrationEngine/Tests/RestSoapAdapterSmoke.php`

Resultado esperado:

- `RestSoapAdapterSmoke OK`

Consulta checkpoint salvo:
- GET /integration-engine/checkpoints?source=erp_sql&adapter=sql&name=companies_sync

## 16) Summary operacional da Fase 1

Depois do runner, valide no resumo:

- `batches.jobs_with_meta > 0`
- `batches.records_total >= batches.records_queued`
- `batches.truncated_jobs >= 1` (quando o passo 9.1 for executado)
- `batches.checkpoint_jobs >= 1`

## 17) Cenario de carga (1000 registros)

Execute o runner dedicado de carga:

- `php src/Modules/IntegrationEngine/Tests/IntegrationEngineLoadRunner.php --base-url="http://localhost:8081/solpi/index.php" --api-key="SEU_SEGREDO" --records=1000 --batch-size=250 --worker-limit=300`

Saidas esperadas:
- `status=ok`
- `records_total=1000`
- `records_queued` maior que zero
- `throughput_records_per_sec` maior que zero
- `jobs_with_meta_for_source` maior que zero

Campos uteis para analise:
- `enqueue_seconds`
- `worker_seconds`
- `total_seconds`
- `worker_runs`
- `summary_batches.batch_size_max`

Observacao:
- O summary consolida `records_total`, `records_queued`, `records_duplicate`, `checkpoint_jobs` e `truncated_jobs` por `ingestion_run_id` para evitar supercontagem por job.

## 18) Benchmark comparativo (250, 500, 1000, 2000)

Execute o benchmark runner:

- `php src/Modules/IntegrationEngine/Tests/IntegrationEngineBenchmarkRunner.php --base-url="http://localhost:8081/solpi/index.php" --api-key="SEU_SEGREDO" --sizes="250,500,1000,2000" --batch-size=250 --worker-limit=300`

Saida esperada:
- Tabela comparativa com `enqueue_s`, `worker_s`, `total_s` e `throughput_rec_s` por volume.
- Bloco JSON com as mesmas linhas para automacao.

## 19) Baseline diario com historico

Execute o runner de baseline historico:

- `php src/Modules/IntegrationEngine/Tests/IntegrationEngineBenchmarkHistoryRunner.php --base-url="http://localhost:8081/solpi/index.php" --api-key="SEU_SEGREDO" --sizes="250,500,1000,2000" --batch-size=250 --worker-limit=300`

Comportamento:
- Executa o benchmark comparativo completo.
- Preserva a saida de tabela/JSON no terminal.
- Grava uma linha JSON por execucao em:
	- `logs/integration_engine_benchmark_history.jsonl`

Formato da linha historica:
- `recorded_at`, `base_url`, `sizes`, `batch_size`, `worker_limit`, `rows[]`.

## 20) Relatorio de tendencia

Gerar relatorio comparando a ultima execucao com a anterior:

- `php src/Modules/IntegrationEngine/Tests/IntegrationEngineBenchmarkTrendReport.php`

Opcional (janela customizada):

- `php src/Modules/IntegrationEngine/Tests/IntegrationEngineBenchmarkTrendReport.php --last=7`
- `php src/Modules/IntegrationEngine/Tests/IntegrationEngineBenchmarkTrendReport.php --last=7 --threshold-pct=10`

Saida esperada:
- Tabela com `latest_throughput`, `prev_throughput`, `delta_abs`, `delta_pct` por volume.
- Indicador agregado `mean_delta_throughput_abs`.
- Com `--threshold-pct`, retorna erro (exit code 3) quando algum volume cai alem do limite percentual.

## 21) Fluxo diario em comando unico

Executa baseline historico e relatorio de tendencia em sequencia:

- `php src/Modules/IntegrationEngine/Tests/IntegrationEngineBenchmarkDailyRunner.php --base-url="http://localhost:8081/solpi/index.php" --api-key="SEU_SEGREDO" --sizes="250,500,1000,2000" --batch-size=250 --worker-limit=300 --last=7`
- `php src/Modules/IntegrationEngine/Tests/IntegrationEngineBenchmarkDailyRunner.php --base-url="http://localhost:8081/solpi/index.php" --api-key="SEU_SEGREDO" --sizes="250,500,1000,2000" --batch-size=250 --worker-limit=300 --last=7 --threshold-pct=10`

Saida esperada:
- Bloco de benchmark comparativo.
- Gravacao no historico JSONL.
- Relatorio latest vs previous ao final.
- Opcionalmente falha com alerta de regressao quando o threshold percentual for ultrapassado.

Todos aceitam envelope:
{
	"apikey": "SEU_SEGREDO",
	"source": "nome_fonte",
	"event": "upsert",
	"payload": { ...config_adapter... }
}
