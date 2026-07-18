<?php
/**
 * Test Fixtures - Payloads de Webhook
 */

return [
    'zabbix_webhook' => [
        'trigger_id' => '12345',
        'status' => 'PROBLEM',
        'severity' => 'high',
        'title' => 'CPU usage critical',
        'description' => 'CPU usage is above 90%',
        'hostname' => 'server-01',
        'timestamp' => time(),
        'signature' => 'mock_signature',
    ],

    'evolution_webhook' => [
        'data' => [
            'instanceName' => 'production',
            'data' => [
                'message' => [
                    'fromMe' => false,
                    'from' => '5511999999999@s.whatsapp.net',
                    'to' => '5511987654321@s.whatsapp.net',
                    'body' => 'Alert: Critical issue detected',
                    'timestamp' => time(),
                ],
                'contacts' => [
                    ['id' => '5511999999999@s.whatsapp.net', 'name' => 'Support Team'],
                ],
            ],
        ],
    ],

    'invalid_webhook' => [
        // Missing required fields
        'signature' => 'invalid_sig',
    ],

    'malformed_json' => '{invalid json',
];
