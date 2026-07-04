<?php

declare(strict_types=1);

namespace SOLPI\Helpers;

final class SecurityHelper
{
    public static function hash(string $data): string
    {
        return hash('sha256', $data);
    }

    public static function encrypt(string $data, string $key): string
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    public static function decrypt(string $data, string $key): ?string
    {
        $parts = explode('::', base64_decode($data), 2);
        if (count($parts) !== 2) {
            return null;
        }
        [$encrypted, $iv] = $parts;
        return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv) ?: null;
    }

    public static function verifyWebhookSignature(string $payload, string $signature, string $secret): bool
    {
        $computed = hash_hmac('sha256', $payload, $secret);
        return hash_equals($computed, $signature);
    }
}
