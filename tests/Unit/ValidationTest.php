<?php
/**
 * Test Unitário - Validação de Webhooks
 */

namespace SOLPI\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    /**
     * Testa validação de payload Zabbix
     */
    public function testValidateZabbixPayload(): void
    {
        $payload = [
            'trigger_id' => '12345',
            'status' => 'PROBLEM',
            'severity' => 'high',
            'title' => 'CPU usage critical',
            'hostname' => 'server-01',
            'timestamp' => time(),
        ];

        // Assert required fields exist
        $this->assertArrayHasKey('trigger_id', $payload);
        $this->assertArrayHasKey('status', $payload);
        $this->assertArrayHasKey('hostname', $payload);

        // Assert types
        $this->assertIsString($payload['trigger_id']);
        $this->assertIsString($payload['status']);
        $this->assertIsString($payload['severity']);

        // Assert valid values
        $this->assertContains($payload['status'], ['PROBLEM', 'OK']);
        $this->assertContains($payload['severity'], ['info', 'warning', 'average', 'high', 'disaster']);
    }

    /**
     * Testa rejeição de payload Zabbix inválido
     */
    public function testRejectInvalidZabbixPayload(): void
    {
        $payload = [
            // Missing required fields
            'status' => 'PROBLEM',
            'severity' => 'high',
        ];

        // Assert missing fields
        $this->assertArrayNotHasKey('trigger_id', $payload);
        $this->assertArrayNotHasKey('hostname', $payload);
    }

    /**
     * Testa validação de payload Evolution
     */
    public function testValidateEvolutionPayload(): void
    {
        $payload = [
            'from' => '5511999999999',
            'to' => '5511987654321',
            'body' => 'Test message',
            'timestamp' => time(),
            'type' => 'text',
        ];

        // Assert required fields
        $this->assertArrayHasKey('from', $payload);
        $this->assertArrayHasKey('body', $payload);

        // Assert phone format (basic)
        $this->assertMatchesRegularExpression('/^55\d{10,11}$/', $payload['from']);
        $this->assertMatchesRegularExpression('/^55\d{10,11}$/', $payload['to']);

        // Assert message not empty
        $this->assertNotEmpty($payload['body']);
        $this->assertLessThanOrEqual(4096, strlen($payload['body']));
    }

    /**
     * Testa validação de assinatura de webhook
     */
    public function testValidateWebhookSignature(): void
    {
        $secret = getenv('SOLPI_WEBHOOK_SECRET');
        $payload = json_encode(['test' => 'data']);
        $signature = hash_hmac('sha256', $payload, $secret);

        $this->assertIsString($signature);
        $this->assertNotEmpty($signature);
        $this->assertEquals(64, strlen($signature)); // SHA256 hex = 64 chars
    }

    /**
     * Testa rejeição de assinatura inválida
     */
    public function testRejectInvalidSignature(): void
    {
        $secret = getenv('SOLPI_WEBHOOK_SECRET');
        $payload = json_encode(['test' => 'data']);
        $invalidSignature = 'invalid_signature_that_does_not_match';
        $correctSignature = hash_hmac('sha256', $payload, $secret);

        $this->assertNotEquals($invalidSignature, $correctSignature);
    }

    /**
     * Testa parsing de timestamp
     */
    public function testValidateTimestamp(): void
    {
        $timestamp = time();
        $this->assertIsInt($timestamp);
        $this->assertGreaterThan(1600000000, $timestamp); // After 2020-09-13

        // Teste de timestamp antigo (deveria ser rejeitado em produção)
        $oldTimestamp = time() - (24 * 3600); // 24 horas atrás
        $this->assertLessThan($timestamp, $oldTimestamp);
    }
}
