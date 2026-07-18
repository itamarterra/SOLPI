<?php
/**
 * Test Fixtures - Respostas do Zabbix
 */

return [
    'alert_problem' => [
        'triggerid' => '12345',
        'status' => 'PROBLEM',
        'severity' => 'high',
        'title' => 'CPU usage critical',
        'description' => 'CPU usage is above 90%',
        'hostname' => 'server-01',
        'timestamp' => time(),
    ],
    
    'alert_resolved' => [
        'triggerid' => '12345',
        'status' => 'OK',
        'severity' => 'info',
        'title' => 'CPU usage critical - RESOLVED',
        'description' => 'CPU usage is back to normal',
        'hostname' => 'server-01',
        'timestamp' => time(),
    ],

    'alert_with_metrics' => [
        'triggerid' => '12346',
        'status' => 'PROBLEM',
        'severity' => 'average',
        'title' => 'Memory usage high',
        'description' => 'Memory usage is above 85%',
        'hostname' => 'server-02',
        'timestamp' => time(),
        'metrics' => [
            'value' => 87.5,
            'threshold' => 85,
        ],
    ],
];
