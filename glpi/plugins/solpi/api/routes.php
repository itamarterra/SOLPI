<?php

declare(strict_types=1);

use SOLPI\Core\Config;
use SOLPI\Integrations\Evolution\EvolutionClient;
use SOLPI\Modules\Dashboard\DashboardService;
use SOLPI\Modules\IntegrationEngine\Controllers\IntegrationController;
use SOLPI\Modules\IntegrationEngine\EntityResolver\EntityResolverService;
use SOLPI\Modules\IntegrationEngine\Services\DeadLetterService;
use SOLPI\Modules\IntegrationEngine\Services\GovernanceService;
use SOLPI\Modules\IntegrationEngine\Services\ClassificationService;
use SOLPI\Modules\IntegrationEngine\Services\SourceCheckpointService;
use SOLPI\Modules\IntegrationEngine\Services\ReviewService;
use SOLPI\Modules\IntegrationEngine\Services\SemanticSimilarityService;
use SOLPI\Modules\IntegrationEngine\Workers\IntegrationEngineWorker;
use SOLPI\Modules\Settings\SettingsController;
use SOLPI\Modules\Tickets\TicketController;
use SOLPI\Modules\WhatsApp\WhatsAppController;
use SOLPI\Modules\Zabbix\ZabbixController;

/**
 * @return array<string,array<string,Closure>>
 */
function solpi_api_routes(): array
{
	return [
		'GET' => [
			'/' => static function (array $payload = [], array $query = []): array {
				return [
					'name' => 'SOLPI API',
					'status' => 'ok',
					'time' => date(DATE_ATOM),
				];
			},
			'/health' => static function (array $payload = [], array $query = []): array {
				return [
					'status' => 'healthy',
					'service' => 'solpi',
					'time' => date(DATE_ATOM),
				];
			},
			'/integrations/evolution' => static function (array $payload = [], array $query = []): array {
				$config = new Config();
				$config->load();

				$evolutionConfig = $config->get('evolution', []);
				if (!is_array($evolutionConfig)) {
					$evolutionConfig = [];
				}

				$client = new EvolutionClient($evolutionConfig);

				return [
					'enabled' => $client->isEnabled(),
					'instance' => (string)($evolutionConfig['instance'] ?? 'solpi'),
					'connection' => $client->fetchInstance(),
				];
			},
			'/dashboard' => static function (array $payload = [], array $query = []): array {
				$service = new DashboardService();
				$model = $service->dashboard();

				return [
					'open_tickets' => $model->openTickets,
					'closed_tickets' => $model->closedTickets,
					'alerts' => $model->alerts,
					'users' => $model->users,
					'messages' => $model->messages,
					'ai_requests' => $model->aiRequests,
					'zabbix_online' => $model->zabbixOnline,
					'whatsapp_online' => $model->whatsappOnline,
					'ai_online' => $model->aiOnline,
					'uptime' => $model->uptime,
				];
			},
			'/tickets/summary' => static function (array $payload = [], array $query = []): array {
				$controller = new TicketController();

				return $controller->summary();
			},
			'/tickets/recent' => static function (array $payload = [], array $query = []): array {
				$controller = new TicketController();
				$limit = isset($query['limit']) ? (int)$query['limit'] : 20;

				return [
					'items' => $controller->recent($limit),
				];
			},
			'/alerts/summary' => static function (array $payload = [], array $query = []): array {
				$controller = new ZabbixController();

				return $controller->summary();
			},
			'/alerts/recent' => static function (array $payload = [], array $query = []): array {
				$controller = new ZabbixController();
				$limit = isset($query['limit']) ? (int)$query['limit'] : 20;

				return [
					'items' => $controller->recent($limit),
				];
			},
			'/settings' => static function (array $payload = [], array $query = []): array {
				$module = isset($query['module']) ? (string)$query['module'] : 'core';
				$controller = new SettingsController();

				return $controller->list($module);
			},
			'/integration-engine/jobs' => static function (array $payload = [], array $query = []): array {
				$limit = isset($query['limit']) ? (int)$query['limit'] : 30;
				$controller = new IntegrationController();

				return $controller->jobs($limit);
			},
			'/integration-engine/adapters' => static function (array $payload = [], array $query = []): array {
				$controller = new IntegrationController();

				return [
					'items' => $controller->supportedAdapters(),
				];
			},
			'/integration-engine/review' => static function (array $payload = [], array $query = []): array {
				$limit = isset($query['limit']) ? (int)$query['limit'] : 30;
				$service = new ReviewService();

				return ['items' => $service->pending($limit)];
			},
			'/integration-engine/dead-letter' => static function (array $payload = [], array $query = []): array {
				$limit = isset($query['limit']) ? (int)$query['limit'] : 30;
				$service = new DeadLetterService();

				return ['items' => $service->list($limit)];
			},
			'/integration-engine/quality/reports' => static function (array $payload = [], array $query = []): array {
				$limit = isset($query['limit']) ? (int)$query['limit'] : 20;
				$service = new GovernanceService();

				return ['items' => $service->recentQualityReports($limit)];
			},
			'/integration-engine/checkpoints' => static function (array $payload = [], array $query = []): array {
				$source = isset($query['source']) ? (string)$query['source'] : '';
				$adapter = isset($query['adapter']) ? (string)$query['adapter'] : '';
				$name = isset($query['name']) ? (string)$query['name'] : 'default';
				$limit = isset($query['limit']) ? (int)$query['limit'] : 100;

				if ($source === '') {
					return ['error' => 'source is required'];
				}

				$service = new SourceCheckpointService();

				if ($adapter !== '') {
					$item = $service->get($source, $adapter, $name);

					return [
						'item' => $item,
					];
				}

				return [
					'items' => $service->list($source, null, $limit),
				];
			},
		],
		'POST' => [
			'/webhook/whatsapp' => static function (array $payload = [], array $query = []): array {
				$controller = new WhatsAppController();

				return $controller->handleWebhook($payload);
			},
			'/tickets/close' => static function (array $payload = [], array $query = []): array {
				$glpiTicketId = (int)($payload['glpi_ticket_id'] ?? 0);
				if ($glpiTicketId <= 0) {
					return ['error' => 'glpi_ticket_id is required'];
				}

				$controller = new TicketController();

				return $controller->close($glpiTicketId);
			},
			'/tickets/reopen' => static function (array $payload = [], array $query = []): array {
				$glpiTicketId = (int)($payload['glpi_ticket_id'] ?? 0);
				if ($glpiTicketId <= 0) {
					return ['error' => 'glpi_ticket_id is required'];
				}

				$controller = new TicketController();

				return $controller->reopen($glpiTicketId);
			},
			'/alerts/ingest' => static function (array $payload = [], array $query = []): array {
				$controller = new ZabbixController();

				return $controller->ingest($payload);
			},
			'/alerts/acknowledge' => static function (array $payload = [], array $query = []): array {
				$alertId = (int)($payload['alert_id'] ?? 0);
				if ($alertId <= 0) {
					return ['error' => 'alert_id is required'];
				}

				$controller = new ZabbixController();

				return $controller->acknowledge($alertId);
			},
			'/settings' => static function (array $payload = [], array $query = []): array {
				$module = (string)($payload['module'] ?? 'core');
				$key = (string)($payload['key'] ?? '');
				$type = (string)($payload['type'] ?? 'string');
				$value = $payload['value'] ?? null;

				if ($key === '') {
					return ['error' => 'key is required'];
				}

				$controller = new SettingsController();

				return $controller->set($module, $key, $value, $type);
			},
			'/integration-engine/ingest' => static function (array $payload = [], array $query = []): array {
				$source = (string)($payload['source'] ?? '');
				$event = (string)($payload['event'] ?? 'upsert');
				$body = $payload['payload'] ?? [];

				if ($source === '') {
					return ['error' => 'source is required'];
				}

				if (!is_array($body)) {
					return ['error' => 'payload must be object/array'];
				}

				$controller = new IntegrationController();

				return $controller->ingest($source, $event, $body);
			},
			'/integration-engine/ingest/adapter' => static function (array $payload = [], array $query = []): array {
				$source = (string)($payload['source'] ?? '');
				$event = (string)($payload['event'] ?? 'upsert');
				$adapter = (string)($payload['adapter'] ?? '');
				$body = $payload['payload'] ?? [];
				$context = $payload['context'] ?? [];

				if ($source === '') {
					return ['error' => 'source is required'];
				}

				if ($adapter === '') {
					return ['error' => 'adapter is required'];
				}

				if (!is_array($body)) {
					return ['error' => 'payload must be object/array'];
				}

				if (!is_array($context)) {
					return ['error' => 'context must be object/array'];
				}

				$controller = new IntegrationController();

				return $controller->ingestViaAdapter($source, $event, $adapter, $body, $context);
			},
			'/integration-engine/ingest/rest' => static function (array $payload = [], array $query = []): array {
				$controller = new IntegrationController();
				$source = (string)($payload['source'] ?? 'rest');
				$event = (string)($payload['event'] ?? 'upsert');
				$body = is_array($payload['payload'] ?? null) ? $payload['payload'] : [];

				return $controller->ingestViaAdapter($source, $event, 'rest', $body, []);
			},
			'/integration-engine/ingest/soap' => static function (array $payload = [], array $query = []): array {
				$controller = new IntegrationController();
				$source = (string)($payload['source'] ?? 'soap');
				$event = (string)($payload['event'] ?? 'upsert');
				$body = is_array($payload['payload'] ?? null) ? $payload['payload'] : [];

				return $controller->ingestViaAdapter($source, $event, 'soap', $body, []);
			},
			'/integration-engine/ingest/csv' => static function (array $payload = [], array $query = []): array {
				$controller = new IntegrationController();
				$source = (string)($payload['source'] ?? 'csv');
				$event = (string)($payload['event'] ?? 'upsert');
				$body = is_array($payload['payload'] ?? null) ? $payload['payload'] : [];

				return $controller->ingestViaAdapter($source, $event, 'csv', $body, []);
			},
			'/integration-engine/ingest/json' => static function (array $payload = [], array $query = []): array {
				$controller = new IntegrationController();
				$source = (string)($payload['source'] ?? 'json');
				$event = (string)($payload['event'] ?? 'upsert');
				$body = is_array($payload['payload'] ?? null) ? $payload['payload'] : [];

				return $controller->ingestViaAdapter($source, $event, 'json', $body, []);
			},
			'/integration-engine/ingest/xml' => static function (array $payload = [], array $query = []): array {
				$controller = new IntegrationController();
				$source = (string)($payload['source'] ?? 'xml');
				$event = (string)($payload['event'] ?? 'upsert');
				$body = is_array($payload['payload'] ?? null) ? $payload['payload'] : [];

				return $controller->ingestViaAdapter($source, $event, 'xml', $body, []);
			},
			'/integration-engine/ingest/sql' => static function (array $payload = [], array $query = []): array {
				$controller = new IntegrationController();
				$source = (string)($payload['source'] ?? 'sql');
				$event = (string)($payload['event'] ?? 'upsert');
				$body = is_array($payload['payload'] ?? null) ? $payload['payload'] : [];

				return $controller->ingestViaAdapter($source, $event, 'sql', $body, []);
			},
			'/integration-engine/ingest/ldap' => static function (array $payload = [], array $query = []): array {
				$controller = new IntegrationController();
				$source = (string)($payload['source'] ?? 'ldap');
				$event = (string)($payload['event'] ?? 'upsert');
				$body = is_array($payload['payload'] ?? null) ? $payload['payload'] : [];

				return $controller->ingestViaAdapter($source, $event, 'ldap', $body, []);
			},
			'/integration-engine/ingest/ftp' => static function (array $payload = [], array $query = []): array {
				$controller = new IntegrationController();
				$source = (string)($payload['source'] ?? 'ftp');
				$event = (string)($payload['event'] ?? 'upsert');
				$body = is_array($payload['payload'] ?? null) ? $payload['payload'] : [];

				return $controller->ingestViaAdapter($source, $event, 'ftp', $body, []);
			},
			'/integration-engine/ingest/sftp' => static function (array $payload = [], array $query = []): array {
				$controller = new IntegrationController();
				$source = (string)($payload['source'] ?? 'sftp');
				$event = (string)($payload['event'] ?? 'upsert');
				$body = is_array($payload['payload'] ?? null) ? $payload['payload'] : [];

				return $controller->ingestViaAdapter($source, $event, 'sftp', $body, []);
			},
			'/integration-engine/ingest/email' => static function (array $payload = [], array $query = []): array {
				$controller = new IntegrationController();
				$source = (string)($payload['source'] ?? 'email');
				$event = (string)($payload['event'] ?? 'upsert');
				$body = is_array($payload['payload'] ?? null) ? $payload['payload'] : [];

				return $controller->ingestViaAdapter($source, $event, 'email', $body, []);
			},
			'/integration-engine/ingest/webhook' => static function (array $payload = [], array $query = []): array {
				$controller = new IntegrationController();
				$source = (string)($payload['source'] ?? 'webhook');
				$event = (string)($payload['event'] ?? 'upsert');
				$body = is_array($payload['payload'] ?? null) ? $payload['payload'] : [];

				return $controller->ingestViaAdapter($source, $event, 'webhook', $body, []);
			},
			'/integration-engine/worker/run-once' => static function (array $payload = [], array $query = []): array {
				$limit = (int)($payload['limit'] ?? 20);
				$worker = new IntegrationEngineWorker();
				$processed = $worker->runOnce($limit);

				return [
					'status' => 'ok',
					'processed' => $processed,
				];
			},
			'/integration-engine/resolve' => static function (array $payload = [], array $query = []): array {
				$entityType = (string)($payload['entity_type'] ?? '');
				$record = $payload['record'] ?? [];

				if (!is_array($record)) {
					return ['error' => 'record must be object/array'];
				}

				if ($entityType !== '') {
					$record['entity_type'] = $entityType;
				}

				$resolver = new EntityResolverService();

				return $resolver->resolve($record);
			},
			'/integration-engine/review/approve' => static function (array $payload = [], array $query = []): array {
				$id = (int)($payload['id'] ?? 0);
				if ($id <= 0) {
					return ['error' => 'id is required'];
				}

				$service = new ReviewService();
				$service->approve($id, isset($payload['reason']) ? (string)$payload['reason'] : null);

				return ['status' => 'approved', 'id' => $id];
			},
			'/integration-engine/review/reject' => static function (array $payload = [], array $query = []): array {
				$id = (int)($payload['id'] ?? 0);
				if ($id <= 0) {
					return ['error' => 'id is required'];
				}

				$service = new ReviewService();
				$service->reject($id, isset($payload['reason']) ? (string)$payload['reason'] : null);

				return ['status' => 'rejected', 'id' => $id];
			},
			'/integration-engine/dead-letter/replay' => static function (array $payload = [], array $query = []): array {
				$id = (int)($payload['id'] ?? 0);
				if ($id <= 0) {
					return ['error' => 'id is required'];
				}

				$service = new DeadLetterService();

				return $service->replay($id);
			},
			'/integration-engine/quality/generate' => static function (array $payload = [], array $query = []): array {
				$service = new GovernanceService();

				return $service->generateQualityReport();
			},
			'/integration-engine/retention/run' => static function (array $payload = [], array $query = []): array {
				$days = (int)($payload['days'] ?? 90);
				$service = new GovernanceService();

				return $service->runRetention($days);
			},
			'/integration-engine/semantic/compare' => static function (array $payload = [], array $query = []): array {
				$a = (string)($payload['a'] ?? '');
				$b = (string)($payload['b'] ?? '');

				if ($a === '' || $b === '') {
					return ['error' => 'a and b are required'];
				}

				$service = new SemanticSimilarityService();

				return [
					'a' => $a,
					'b' => $b,
					'score' => $service->compare($a, $b),
				];
			},
			'/integration-engine/classify' => static function (array $payload = [], array $query = []): array {
				$record = $payload['record'] ?? [];

				if (!is_array($record)) {
					return ['error' => 'record must be object/array'];
				}

				$service = new ClassificationService();

				return $service->classify($record);
			},
			'/integration-engine/checkpoints/set' => static function (array $payload = [], array $query = []): array {
				$source = (string)($payload['source'] ?? '');
				$adapter = (string)($payload['adapter'] ?? '');
				$name = (string)($payload['name'] ?? 'default');
				$lastValue = (string)($payload['last_value'] ?? '');
				$metadata = $payload['metadata'] ?? [];

				if ($source === '' || $adapter === '' || $lastValue === '') {
					return ['error' => 'source, adapter and last_value are required'];
				}

				if (!is_array($metadata)) {
					$metadata = [];
				}

				$service = new SourceCheckpointService();

				return $service->set($source, $adapter, $name, $lastValue, $metadata);
			},
			'/integration-engine/checkpoints/reset' => static function (array $payload = [], array $query = []): array {
				$source = (string)($payload['source'] ?? '');
				$adapter = (string)($payload['adapter'] ?? '');
				$name = (string)($payload['name'] ?? 'default');

				if ($source === '' || $adapter === '') {
					return ['error' => 'source and adapter are required'];
				}

				$service = new SourceCheckpointService();

				return $service->reset($source, $adapter, $name);
			},
		],
	];
}

