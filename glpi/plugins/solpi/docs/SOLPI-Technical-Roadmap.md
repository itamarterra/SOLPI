# SOLPI Technical Roadmap

This roadmap translates the SOLPI enterprise vision into implementation phases that can be executed and validated incrementally.

## Guiding Rules

- Preserve compatibility with GLPI.
- Keep ingestion idempotent.
- Avoid duplicate records by design.
- Protect critical fields with merge policy and audit trail.
- Prefer small, testable increments.
- Validate every phase with runtime checks and smoke tests.

## Phase 6 - Scale Hardening and Throughput

Goal: make large-volume ingestion more efficient and predictable.

Deliverables:
- Micro-batching by source and adapter.
- Stronger stop conditions for REST, SQL, CSV, XML, SOAP, and file-based adapters.
- Queue backpressure controls.
- Batch-level checkpoints.
- Better observability for batch duration, retry count, and truncated loads.
- Queue metadata persisted with batch size, batch count, batch index, and chunk size.
- Worker audit logs enriched with batch context for success and failure events.

Acceptance criteria:
- Large ingestions can run without excessive memory growth.
- Pagination stops early when the source signals exhaustion.
- Batch metrics are visible in job metadata and logs.
- Batch context can be traced from API response to queued job to worker audit log.

## Phase 7 - Contextual Knowledge and Semantic Search

Goal: turn operational data into usable knowledge.

Deliverables:
- Knowledge Graph expansion with tickets, assets, users, contracts, documents, and relations.
- Ticket-to-solution linkage.
- Semantic retrieval over resolved tickets and documentation.
- Similarity ranking for knowledge suggestions.
- Knowledge curation workflow for review and approval.

Acceptance criteria:
- The system can suggest related content from real historical data.
- Knowledge records are traceable back to source events.

## Phase 8 - AI Assistant and Classification

Goal: provide operational assistance in natural language.

Deliverables:
- Improved classification with historical context.
- AI-assisted entity resolution suggestions.
- Natural language assistant for search, summaries, and recommendations.
- Controlled prompts and policy gates for sensitive actions.
- Human review fallback for low-confidence results.

Acceptance criteria:
- The assistant can answer common operational questions using company data.
- AI actions remain auditable and reversible where applicable.

## Phase 9 - Automation and Workflow Orchestration

Goal: automate repetitive IT operations safely.

Deliverables:
- Ticket automation rules.
- Asset and user synchronization workflows.
- Approval flows for changes and requests.
- Connector-based event triggers.
- Replayable job execution for automation steps.

Acceptance criteria:
- Common workflows can run without manual intervention.
- Every automated action records actor, timestamp, and decision context.

## Phase 10 - Monitoring and External Operations

Goal: connect SOLPI to infrastructure and communications tools.

Deliverables:
- Zabbix synchronization.
- Grafana-ready operational datasets.
- Microsoft 365 integration points.
- Evolution API and WhatsApp alert routing.
- SMTP notifications and escalation policies.
- Active Directory reconciliation.

Acceptance criteria:
- Operational events can be routed to external systems.
- Monitoring signals can update assets, tickets, and alerts.

## Phase 11 - Security and Multi-Tenant Hardening

Goal: prepare the platform for enterprise isolation and scale.

Deliverables:
- Stronger tenant separation strategy.
- Role-based access control review.
- Field-level encryption for sensitive data.
- Security headers and input validation hardening.
- Retention and archival policies by data class.
- Expanded audit coverage for privileged actions.

Acceptance criteria:
- Sensitive fields are protected according to policy.
- Multi-tenant boundaries are explicit and enforceable.
- High-risk operations are logged end to end.

## Phase 12 - Enterprise Readiness and Operational Excellence

Goal: stabilize the platform for production operations.

Deliverables:
- Expanded smoke test coverage.
- Load-test scenarios and performance baselines.
- Failure recovery playbooks.
- Upgrade and migration playbooks.
- Documentation for operators and integrators.

Acceptance criteria:
- New releases are testable and reproducible.
- Operational runbooks exist for common failure modes.

## Current Priority Order

1. Finish throughput and checkpoint refinement.
2. Expand semantic knowledge and historical context.
3. Improve AI assistant quality and safety controls.
4. Add workflow automation and orchestration.
5. Extend monitoring integrations.
6. Harden security and multi-tenant isolation.
7. Prepare for enterprise release operations.

## References

- [Enterprise Vision and Roadmap](SOLPI-Enterprise-Vision-Roadmap.md)
- [Integration Engine Architecture Blueprint](SOLPI-IntegrationEngine-Architecture.md)
- [Integration Engine README](../src/Modules/IntegrationEngine/README.md)
