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

    public function sendText(string $number, string $text): array
    {
        return $this->post(
            '/message/sendText/' . $this->instance,
            ['number' => $number, 'text' => $text]
        );
    }

    /**
     * Envia mensagem de avaliacao com botoes clicaveis (1-5 estrelas).
     * Envia dois grupos: notas 1-3 e notas 4-5.
     */
    public function sendRatingButtons(string $number): void
    {
        $base = [
            'number'      => $number,
            'title'       => 'Avaliacao do Atendimento',
            'footer'      => 'SOLPI Service Desk',
        ];

        // Grupo 1: notas 1, 2, 3
        $this->post('/message/sendButtons/' . $this->instance, array_merge($base, [
            'description' => 'Como voce avalia o nosso atendimento? (notas 1 a 3)',
            'buttons'     => [
                ['buttonId' => '1', 'type' => 'reply', 'buttonText' => ['displayText' => '1 ⭐ Pessimo']],
                ['buttonId' => '2', 'type' => 'reply', 'buttonText' => ['displayText' => '2 ⭐⭐ Ruim']],
                ['buttonId' => '3', 'type' => 'reply', 'buttonText' => ['displayText' => '3 ⭐⭐⭐ Regular']],
            ],
        ]));

        // Grupo 2: notas 4, 5
        $this->post('/message/sendButtons/' . $this->instance, array_merge($base, [
            'description' => 'Ou selecione aqui (notas 4 a 5)',
            'buttons'     => [
                ['buttonId' => '4', 'type' => 'reply', 'buttonText' => ['displayText' => '4 ⭐⭐⭐⭐ Bom']],
                ['buttonId' => '5', 'type' => 'reply', 'buttonText' => ['displayText' => '5 ⭐⭐⭐⭐⭐ Excelente']],
            ],
        ]));
    }

    private function headers(): array
    {
        return [
            'apikey: ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
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

        $ch = curl_init($this->baseUrl . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers());
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        return $this->parseResponse($ch);
    }

    private function parseResponse(\CurlHandle $ch): array
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
            return ['success' => false, 'error' => 'JSON invalido', 'raw' => $response, 'status_code' => $code];
        }

        if (is_array($decoded) && !isset($decoded['status_code'])) {
            $decoded['status_code'] = $code;
            $decoded['success']     = $code >= 200 && $code < 300;
        }

        return $decoded;
    }
}