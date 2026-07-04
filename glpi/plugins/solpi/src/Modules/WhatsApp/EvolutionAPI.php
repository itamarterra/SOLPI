<?php
declare(strict_types=1);

namespace SOLPI\Modules\WhatsApp;

use SOLPI\Core\Http\Transport\CurlTransport;
use RuntimeException;

final class EvolutionAPI
{
    private CurlTransport $transport;
    private string $baseUrl;
    private string $apiKey;

    public function __construct(string $baseUrl, string $apiKey)
    {
        $this->transport = new CurlTransport();
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
    }

    public function sendMessage(string $instance, string $number, string $text): array
    {
        $url = "{$this->baseUrl}/message/sendText/{$instance}";
        $body = json_encode([
            'number' => $number,
            'text'   => $text
        ]);

        $headers = [
            'Content-Type: application/json',
            "apikey: {$this->apiKey}"
        ];

        return $this->transport->send('POST', $url, $headers, $body);
    }
}

