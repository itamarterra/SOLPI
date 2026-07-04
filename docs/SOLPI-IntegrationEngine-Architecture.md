# SOLPI Integration Engine - Enterprise Architecture Blueprint

Status: Draft for approval
Scope: GLPI 11 Enterprise Plugin (SOLPI)
Constraint: No code implementation until architecture approval

## 1) Executive Summary

The Integration Engine is the strategic core of SOLPI for large enterprises and MSPs.
It ingests heterogeneous data, normalizes entities, resolves identity and relationships, and persists data into GLPI without duplication, without data loss, and with full auditability.

Primary non-functional commitments:
- Never create duplicate records.
- Never lose source data.
- Never overwrite critical fields without audit and merge policy.
- Full modular architecture for extensibility.
- Asynchronous, resilient, and horizontally scalable operation.

## 2) Target Architecture

Proposed module tree:

Modules/
IntegrationEngine/
Adapters/
Importers/
Connectors/
Normalizers/
Matchers/
EntityResolver/
MergeEngine/
Queue/
Workers/
Transformers/
Validators/
Repositories/
Services/
Controllers/
DTO/
Policies/
Events/
Listeners/
Logs/
Audit/
Security/
Tests/

Logical layers:
- Ingestion Layer: Adapters, Connectors, Importers.
- Processing Layer: Normalizers, Validators, Matchers, EntityResolver, MergeEngine.
- Persistence Layer: Repositories, Transaction services, Outbox.
- Orchestration Layer: Queue, Workers, Retry, Dead Letter Queue.
- Governance Layer: Audit, Logs, Security policies, Data quality controls.
- Intelligence Layer: AI matching, classification, consistency suggestions, Knowledge Graph updates.

## 3) End-to-End Flow

1. Source event arrives via API, file drop, webhook, mail, or connector pull.
2. Raw payload is persisted in immutable landing storage with checksum and metadata.
3. A queue job is created with correlation_id and idempotency_key.
4. Worker loads job and executes source adapter.
5. Payload is transformed to canonical DTOs.
6. Validators execute schema, business, and security checks.
7. Entity Matcher runs deterministic and fuzzy matching.
8. EntityResolver identifies or creates canonical entity references.
9. MergeEngine applies field-level merge policy with conflict logging.
10. Repository layer writes inside transactional boundary.
11. Outbox emits domain events.
12. Audit trail and integration log records all steps.
13. Knowledge Graph updater projects entity and relationship changes.
14. AI services run optional reconciliation and quality suggestions.
15. Job final state is marked success, partial_success, retry, or dead_letter.

## 4) High-Level Diagram (Text)

External Sources -> Adapters/Connectors -> Landing Raw Store -> Queue -> Workers
Workers -> Normalizers -> Validators -> Matcher -> EntityResolver -> MergeEngine
MergeEngine -> Repositories -> GLPI Tables
Repositories -> Outbox Events -> Listeners
Listeners -> Audit Log / Metrics / Knowledge Graph / AI Suggestions
Failures -> Retry Policy -> DLQ

## 5) Data Sources and Adapter Strategy

Supported inputs:
- REST API
- SOAP
- CSV
- Excel
- JSON
- XML
- LDAP
- Microsoft Active Directory
- MySQL
- MariaDB
- SQL Server
- Oracle
- PostgreSQL
- SQLite
- MongoDB
- ERP
- CRM
- Proprietary systems
- Webhooks
- FTP
- SFTP
- Watched folders
- E-mail

Adapter contract principle:
- Every source implements source metadata discovery.
- Every source emits canonical ingestion envelope.
- Every source includes checkpoint support where possible.
- Every source includes idempotent source cursor processing.

## 6) Canonical Data Model

Core canonical entities:
- Company
- User
- Asset
- License
- Contract
- Document
- Ticket
- Project
- IntegrationJob
- AuditRecord
- HistoryRecord

Canonical envelope fields:
- correlation_id
- source_system
- source_endpoint
- source_record_id
- source_hash
- received_at
- tenant_context
- payload_version
- classification

## 7) Database Architecture (MariaDB)

Primary operational tables:
- solpi_integrations
- solpi_integration_runs
- solpi_raw_payloads
- solpi_queue_jobs
- solpi_queue_attempts
- solpi_dead_letter
- solpi_identities
- solpi_match_candidates
- solpi_merge_decisions
- solpi_audit_log
- solpi_history_log
- solpi_outbox_events
- solpi_kg_nodes
- solpi_kg_edges
- solpi_ai_suggestions

Critical constraints:
- Unique composite keys for identity anchors.
- Immutable raw payload table.
- Versioned records for critical entities.
- Soft-delete with lineage references.

Index strategy:
- High-cardinality indexes on source_record_id, correlation_id, source_hash.
- Composite indexes per entity matching keys.
- Time-based partitioning for logs and payloads at high scale.

## 8) Entity Matching Strategy (No Duplicates)

Matching engine tiers:
1. Deterministic exact match.
2. Deterministic normalized exact match.
3. Weighted similarity match.
4. AI-assisted semantic match.
5. Human-review fallback when confidence below threshold.

Normalization examples:
- Case folding.
- Accent stripping.
- Legal suffix stripping.
- Token sorting.
- Phone and document canonicalization.
- Domain canonicalization.

Entity-specific keys:
- Company: legal_name, trade_name, tax_id, domain, website, phone, email, address, city, state, postal_code, solpi_uuid.
- User: uuid, email, national_id, phone, name, department, title, company.
- Asset: serial, asset_tag, hostname, mac, uuid, patrimony, model, manufacturer.
- License: serial, product_key, vendor, category, company, asset.
- Document: sha256, name, company, category.
- Contract: number, company, vendor, date.

Confidence policy:
- High confidence: auto-merge.
- Medium confidence: merge with protected field policy.
- Low confidence: route to review queue.

## 9) Merge Strategy (No Silent Overwrite)

Field classification:
- Immutable fields.
- Protected fields.
- Mutable fields.
- Computed fields.

Merge policy levels:
- Source-of-truth priority per integration.
- Freshness-based updates with trust score.
- Non-destructive append for multi-value attributes.
- Conflict snapshot before any protected-field change.

Every merge action writes:
- previous_value
- new_value
- decision_reason
- decision_mode
- confidence
- actor_type (system, ai, user)

## 10) Rollback and Recovery Strategy

Rollback principles:
- Raw payload remains immutable and replayable.
- Every write operation stores before_state and after_state references.
- Transactional unit per entity group.
- Outbox pattern for guaranteed event consistency.

Recovery modes:
- Job retry with exponential backoff.
- Partial replay by correlation_id.
- Full replay by run_id.
- DLQ replay after remediation.

## 11) Queue, Workers, Retry, DLQ

Queue model:
- ingestion_queue
- validation_queue
- merge_queue
- postprocess_queue
- review_queue

Worker model:
- stateless workers
- horizontal scale
- adaptive concurrency
- priority scheduling per SLA

Retry strategy:
- exponential backoff with jitter
- max attempts per error category
- poison message detection
- DLQ with remediation metadata

## 12) APIs (Integration Engine)

Main API domains:
- Integrations management
- Ingestion endpoints
- Job and run status
- Review and resolution workflows
- Audit and lineage retrieval
- Metrics and health

Representative endpoints:
- POST /integration-engine/v1/ingest/{source}
- POST /integration-engine/v1/webhooks/{source}
- GET /integration-engine/v1/jobs/{job_id}
- GET /integration-engine/v1/runs/{run_id}
- POST /integration-engine/v1/review/{candidate_id}/approve
- POST /integration-engine/v1/review/{candidate_id}/reject
- GET /integration-engine/v1/audit/{correlation_id}

API principles:
- Strict idempotency keys.
- Pagination and cursor for large datasets.
- Signed requests for webhooks.
- Rate limiting and abuse protection.

## 13) Domain Events and Listeners

Core events:
- IntegrationReceived
- IntegrationValidated
- EntityMatched
- EntityCreated
- EntityMerged
- MergeConflictDetected
- JobRetried
- JobDeadLettered
- AuditRecorded
- KnowledgeGraphUpdated
- AISuggestionGenerated

Listeners:
- AuditListener
- MetricsListener
- NotificationListener
- KnowledgeProjectionListener
- AIQualityListener

## 14) Classes and Interfaces Blueprint

Core interfaces:
- SourceAdapterInterface
- ConnectorInterface
- ImporterInterface
- NormalizerInterface
- ValidatorInterface
- MatcherInterface
- EntityResolverInterface
- MergeStrategyInterface
- QueueProducerInterface
- QueueConsumerInterface
- AuditRepositoryInterface
- KnowledgeGraphRepositoryInterface

Core classes:
- IntegrationOrchestrator
- IngestionController
- PayloadNormalizer
- EntityMatcherEngine
- MergeEngine
- IdempotencyService
- AuditService
- RetryPolicyService
- DeadLetterService
- KnowledgeGraphProjector
- AISuggestionService

## 15) Knowledge Graph Strategy

Graph model:
- Nodes: Company, User, Asset, License, Contract, Document, Ticket, Project.
- Edges: owns, assigned_to, licensed_to, governed_by, references, related_to, opened_by.

Update modes:
- Real-time incremental projection from outbox events.
- Batch reconciliation for deep consistency checks.

Usage:
- Better entity disambiguation.
- Relationship-aware deduplication.
- Explainable AI recommendations.

## 16) AI Strategy

AI use-cases:
- Similarity enhancement and semantic matching.
- Data classification and taxonomy suggestions.
- Inconsistency detection and correction proposals.
- Duplicate candidate ranking.

AI safety controls:
- Prompt and context isolation.
- Output schema enforcement.
- Confidence thresholds and deterministic fallback.
- Human-in-the-loop for low confidence.

Anti-poisoning measures:
- Source trust scoring.
- Data provenance tagging.
- Suspicious pattern detection.
- Controlled RAG ingestion with verification gates.

## 17) Security Architecture

Threat coverage:
- SQL injection
- XSS
- CSRF
- SSRF
- Command injection
- Prompt injection
- Mass assignment
- Broken authentication
- Broken authorization
- Unsafe deserialization
- Malicious uploads
- Path traversal
- Directory traversal
- Race condition
- Prompt leakage
- Model poisoning
- RAG poisoning

Security controls:
- Prepared statements and strict repository layer.
- Input allowlist validation.
- Output encoding and CSP.
- CSRF tokens and same-site cookie policy.
- RBAC and ABAC for admin and integration roles.
- Secret management by environment and vault.
- Signed webhook verification.
- Upload scanning and content-type enforcement.
- Security audit trail immutable records.

## 18) DevSecOps and Operations

Pipeline controls:
- SAST
- dependency scanning
- IaC scanning
- secret scanning
- DAST for exposed endpoints
- policy gates before deploy

Runtime controls:
- WAF rules for integration endpoints.
- Structured logging and SIEM integration.
- OpenTelemetry tracing and metrics.
- Error budget and SLO policy.

## 19) Performance and Scalability

Target support:
- 1M companies
- 10M assets
- 100M records

Scalability strategy:
- Horizontal workers.
- Queue partitioning by tenant/source/entity.
- Read/write path separation where needed.
- Caching for hot reference data.
- Batch ingest with micro-batching and streaming.
- Partitioned log and payload storage.

Performance guardrails:
- P95 and P99 latency budgets per stage.
- Backpressure on ingest.
- Adaptive retry and circuit breaker.

## 20) Data Governance

Governance principles:
- Data lineage end-to-end.
- Data retention and purge policies.
- Privacy and legal compliance controls.
- Master data ownership per domain.
- Data quality scorecards.

Audit minimum fields:
- date_time
- source_system
- endpoint
- received_payload_hash
- processed_payload_hash
- processing_time_ms
- warnings_count
- errors_count
- duplicate_candidates_count
- actor
- ip_address

## 21) Use Cases

Primary use cases:
- Import company master data from ERP.
- Sync users from AD and LDAP.
- Ingest assets from network inventory systems.
- Import licenses and contracts from procurement systems.
- Attach documents from file stores and e-mail.
- Create or update tickets and projects from external workflows.

Special use cases:
- Replay failed runs from DLQ.
- Resolve duplicate candidates in review queue.
- Audit full history by correlation_id.

## 22) Rollout Strategy

Phase 1:
- Core ingestion, queue, audit, and deterministic matcher for Company/User/Asset.

Phase 2:
- Merge policies, confidence scoring, review queue, and Knowledge Graph projection.

Phase 3:
- Full adapter expansion and AI-assisted matching/classification.

Phase 4:
- Performance hardening for large-scale workloads and multi-tenant isolation.

## 23) Acceptance Criteria for Architecture Approval

- All required data sources supported by adapter contract.
- End-to-end idempotent ingestion defined.
- No-duplicate strategy formally documented and testable.
- Merge and audit policies formally defined.
- Queue, retry, and DLQ strategy operationally complete.
- Security controls mapped to listed threats.
- Scalability approach aligns with target volumes.

## 24) Open Decisions for Stakeholder Approval

1. Source-of-truth ranking per data domain and per tenant.
2. Confidence thresholds for auto-merge and review queue.
3. Retention periods for raw payload, audit, and history.
4. Multi-tenant isolation mode: shared schema vs schema per tenant.
5. Priority adapter roadmap by business value.

## 25) Next Step After Approval

After architecture approval, implementation starts in this exact order:
1. Foundation module skeleton with interfaces and DTOs.
2. Queue, job orchestration, idempotency, and audit baseline.
3. Deterministic matcher and merge engine for Company/User/Asset.
4. API endpoints for ingestion and run observability.
5. AI and Knowledge Graph incremental enablement with safety gates.
