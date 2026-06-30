<?php

declare(strict_types=1);

namespace SOLPI\Integrations\Evolution;

use RuntimeException;

final class EvolutionClient
{
    private string $baseUrl;
    private string $apiKey;
    private string $apiKeyHeader;
    private bool $enabled;

    public function __construct(array $config)
    {
        $this->enabled = $config['enabled'] ?? false;
        $this->baseUrl = rtrim($config['base_url'] ?? '', '/');
        $this->apiKey = $config['auth_key'] ?? '';
        $this->apiKeyHeader = $config['api_key_header'] ?? 'x-api-key';

        if ($this->enabled === true && $this->baseUrl === '') {
            throw new RuntimeException('Evolution base_url não está configurado.');
        }
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function status(): array
    {
        return $this->get('/api/status');
    }

    public function session(): array
    {
        return $this->get('/api/session');
    }

    public function qrCode(): array
    {
        return $this->get('/api/qrcode');
    }

    private function get(string $path): array
    {
        if (!$this->enabled) {
            return ['enabled' => false];
        }

        $url = $this->baseUrl . $path;
        $headers = [
            $this->apiKeyHeader . ': ' . $this->apiKey,
            'Accept: application/json',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return [
                'success' => false,
                'error' => $error ?: 'Falha na requisição CURL',
                'status_code' => $code,
            ];
        }

        $decoded = json_decode($response, true);

        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error' => 'Resposta inválida JSON',
                'raw' => $response,
                'status_code' => $code,
            ];
        }

        return array_merge(
            ['success' => $code >= 200 && $code < 300, 'status_code' => $code],
            $decoded
        );
    }
}
