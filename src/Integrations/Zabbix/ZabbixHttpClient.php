<?php

declare(strict_types=1);

namespace SOLPI\Integrations\Zabbix;

/**
 * Cliente HTTP para o Zabbix (Compatível com Zabbix 7.0+)
 */
final class ZabbixHttpClient
{
    public function post(string $url, array $headers, array $body, ?string $token = null): array
    {
        $ch = curl_init($url);

        $httpHeaders = [];
        foreach ($headers as $k => $v) {
            $httpHeaders[] = "$k: $v";
        }

        // Se o token for informado, envia via Header (Obrigatório no Zabbix 7.0+)
        if ($token) {
            $httpHeaders[] = "Authorization: Bearer $token";
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => $httpHeaders,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['status' => $code, 'body' => ['error' => $error]];
        }

        return [
            'status' => $code,
            'body' => json_decode($response, true)
        ];
    }
}
