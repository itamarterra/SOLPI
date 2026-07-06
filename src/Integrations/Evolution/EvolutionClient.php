<?php

declare(strict_types=1);

namespace SOLPI\Integrations\Evolution;

use RuntimeException;

final class EvolutionClient
{
    private string $baseUrl;
    private string $apiKey;
    private string $instance;
    private bool   $enabled;

    public function __construct(array $config)
    {
        $this->enabled  = (bool)($config['enabled'] ?? false);
        $this->baseUrl  = rtrim($config['base_url'] ?? '', '/');
        $this->apiKey   = $config['auth_key'] ?? '';
        $this->instance = $config['instance'] ?? 'solpi';

        if ($this->enabled && $this->baseUrl === '') {
            throw new RuntimeException('Evolution base_url nao esta configurado.');
        }
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function fetchInstance(): array
    {
        $all = $this->get('/instance/fetchInstances');

        if (!isset($all[0])) {
            return ['connectionStatus' => 'disconnected'];
        }

        foreach ($all as $inst) {
            if (($inst['name'] ?? '') === $this->instance) {
                return $inst;
            }
        }

        return ['connectionStatus' => 'not_found'];
    }

    public function connect(): array
    {
        return $this->get('/instance/connect/' . $this->instance);
    }

    private function cleanNumber(string $number): string
    {
        return preg_replace('/\D/', '', $number);
    }

    public function sendText(string $number, string $text): array
    {
        // Debug: Log text content
        if (empty($text)) {
            $text = "Relatório SOLPI: Varredura concluída com sucesso.";
        }

        return $this->post(
            '/message/sendText/' . $this->instance,
            [
                'number' => $this->cleanNumber($number),
                'text' => $text,
                'delay' => 1000
            ]
        );
    }

    private function headers(): array
    {
        return [
            'apikey: ' . $this->apiKey,
            'Content-Type: application/json'
        ];
    }

    private function get(string $path): array
    {
        if (!$this->enabled) {
            return ['enabled' => false];
        }

        $ch = curl_init($this->baseUrl . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers());
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        return $this->parseResponse($ch);
    }

    private function post(string $path, array $body): array
    {
        if (!$this->enabled) {
            return ['enabled' => false];
        }

        $url = $this->baseUrl . $path;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers());
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        return $this->parseResponse($ch);
    }

    private function parseResponse($ch): array
    {
        $response = curl_exec($ch);
        $code     = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['success' => false, 'error' => $error ?: 'Falha cURL', 'status_code' => $code];
        }

        $decoded = json_decode($response, true);

        if ($decoded === null) {
            // Se não for JSON, pode ser um erro de servidor bruto
            error_log("SOLPI Evolution Error Raw: " . $response);
            return ['success' => false, 'error' => 'Resposta invalida do servidor', 'status_code' => $code];
        }

        if (is_array($decoded) && !isset($decoded['status_code'])) {
            $decoded['status_code'] = $code;
            $decoded['success']     = $code >= 200 && $code < 300;
        }

        // Caso o servidor retorne um erro amigável na estrutura
        if (isset($decoded['status']) && $decoded['status'] === 'error') {
            $decoded['success'] = false;
        }

        return $decoded;
    }
}
