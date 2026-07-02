# SOLPI API Schemas

Este documento resume os contratos principais da API do SOLPI para evitar divergencia entre implementacao e documentacao.

## Envelope padrao de resposta

Todas as rotas da API devem responder no seguinte formato base:

```json
{
  "status": "ok",
  "time": "2026-07-02T12:00:00-03:00",
  "data": {}
}
```

Em caso de erro:

```json
{
  "status": "error",
  "time": "2026-07-02T12:00:00-03:00",
  "error": {
    "message": "Unauthorized",
    "code": 401,
    "details": {
      "hint": "apikey missing"
    }
  }
}
```

## Ingestao direta

### Request

```json
{
  "apikey": "SEU_SEGREDO",
  "source": "nome_fonte",
  "event": "upsert",
  "payload": {
    "entity_type": "company",
    "name": "ACME"
  }
}
```

### Data esperada

```json
{
  "status": "queued",
  "job_id": 123,
  "idempotency_key": "...",
  "correlation_id": "..."
}
```

## Ingestao por adapter

### Request

```json
{
  "apikey": "SEU_SEGREDO",
  "source": "erp_sql",
  "event": "upsert",
  "adapter": "sql",
  "context": {
    "batch_size": 250,
    "checkpoint": {
      "enabled": true,
      "name": "companies_sync"
    }
  },
  "payload": {}
}
```

### Data esperada

```json
{
  "status": "queued",
  "adapter": "sql",
  "records_total": 10,
  "records_queued": 10,
  "records_duplicate": 0,
  "batch_size": 250,
  "batch_count": 1,
  "batch_total": 1,
  "checkpoint_enabled": true,
  "checkpoint_name": "companies_sync",
  "checkpoint_in": "...",
  "checkpoint_out": "...",
  "checkpoint_saved": true
}
```

## Summary operacional

### GET /integration-engine/summary

### Data esperada

```json
{
  "status": "ok",
  "generated_at": "2026-07-02T12:00:00-03:00",
  "runtime": {
    "php_version": "8.3.0"
  },
  "jobs": {
    "total": 100,
    "pending": 2,
    "running": 1,
    "done": 95,
    "dead": 2
  },
  "review_queue": {
    "total": 3,
    "pending": 1
  },
  "dead_letter": {
    "total": 2,
    "dead": 1,
    "replayed": 1
  },
  "checkpoints": {
    "total": 4
  },
  "quality": {
    "latest_report": null
  }
}
```

## Checkpoints

### GET /integration-engine/checkpoints?source=...&adapter=...&name=...

### Data esperada

```json
{
  "item": {
    "source": "erp_sql",
    "adapter": "sql",
    "name": "companies_sync",
    "last_value": "2026-07-02T12:00:00-03:00"
  }
}
```

## Erros recorrentes

- `API secret is not configured` - segredo nao definido.
- `Unauthorized` - chave invalida.
- `source is required` - faltou origem na ingestao.
- `adapter is required` - faltou adapter na ingestao por fonte.
- `payload must be object/array` - corpo invalido.
- `record must be object/array` - classificacao ou resolucao com payload invalido.
