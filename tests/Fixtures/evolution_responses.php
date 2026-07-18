<?php
/**
 * Test Fixtures - Respostas da Evolution API
 */

return [
    'message_received' => [
        'id' => 'msg_12345',
        'from' => '5511999999999',
        'to' => '5511987654321',
        'body' => 'Alert: Critical issue on production server',
        'timestamp' => time(),
        'type' => 'text',
    ],

    'message_with_media' => [
        'id' => 'msg_12346',
        'from' => '5511999999999',
        'to' => '5511987654321',
        'body' => 'Check the attached image',
        'timestamp' => time(),
        'type' => 'image',
        'media' => [
            'url' => 'https://api.evolution.ai/media/img_12345.jpg',
            'mimetype' => 'image/jpeg',
        ],
    ],

    'status_update' => [
        'id' => 'msg_12345',
        'status' => 'DELIVERED',
        'timestamp' => time(),
    ],

    'session_info' => [
        'sessionName' => 'production',
        'status' => 'open',
        'webhookUrl' => 'https://example.com/webhook/evolution',
        'webhookByEvents' => true,
        'instances' => 2,
    ],
];
