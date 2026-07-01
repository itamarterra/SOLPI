(The file was previously empty — added environment & secrets guidance.)

# Environment & Secrets

This project stores several integration tokens (Zabbix, Evolution, AI provider) and the webhook verification secret.

Recommended steps before deploying:

- Do NOT store secrets in the database as plaintext. Instead, provide secrets via environment variables or a secrets manager.
- Required environment variables:
	- `SOLPI_ZABBIX_TOKEN` — Zabbix API token
	- `SOLPI_EVOLUTION_TOKEN` — Evolution API token
	- `SOLPI_AI_API_KEY` — Primary AI provider API key
	- `SOLPI_WEBHOOK_SECRET` — Shared secret used to verify webhook signatures

- If you must store secrets in DB, store them encrypted and rotate keys regularly. Prefer a secure vault for production.

Adjust your deployment to set these env vars (systemd, container, or CI secret manager).

