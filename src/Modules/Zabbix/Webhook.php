<?php

declare(strict_types=1);

namespace SOLPI\Modules\Zabbix;

use SOLPI\Helpers\SecurityHelper;
use SOLPI\Core\Config;

/**
 * Endpoint de entrada para alertas do Zabbix
 */
final class Webhook
{
    private ZabbixService $service;
    private string $secret;

    public function __construct()
    {
        $this->service = new ZabbixService();
        $this->secret = (string)getenv('SOLPI_WEBHOOK_SECRET');
    }

    /**
     * Processa a requisição do Zabbix
     */
    public function handle(string $payload, string $signature = ''): array
    {
        // Se houver uma secret configurada, valida a assinatura
        if ($this->secret !== '' && $signature !== '') {
            if (!SecurityHelper::verifyWebhookSignature($payload, $signature, $this->secret)) {
                return ['status' => 'error', 'message' => 'Assinatura inválida'];
            }
        }

        $data = json_decode($payload, true);
        if (!$data) {
            return ['status' => 'error', 'message' => 'JSON inválido'];
        }

        return $this->service->ingest($data);
    }
}
