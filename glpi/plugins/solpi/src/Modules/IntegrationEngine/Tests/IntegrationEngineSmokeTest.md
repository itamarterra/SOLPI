# IntegrationEngine Smoke Test (Fase 5)

## Pre requisitos
- API ativa em /glpi/plugins/solpi/api/index.php
- Segredo configurado em SOLPI_WEBHOOK_SECRET (ou evolution.auth_key)

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

Consulta checkpoint salvo:
- GET /integration-engine/checkpoints?source=erp_sql&adapter=sql&name=companies_sync

Todos aceitam envelope:
{
	"apikey": "SEU_SEGREDO",
	"source": "nome_fonte",
	"event": "upsert",
	"payload": { ...config_adapter... }
}
