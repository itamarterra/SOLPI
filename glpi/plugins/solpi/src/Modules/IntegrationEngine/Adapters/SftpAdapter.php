<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Adapters;

use RuntimeException;

final class SftpAdapter implements SourceAdapterInterface
{
    public function ingest(array $payload, array $context = []): array
    {
        if (!function_exists('ssh2_connect')) {
            throw new RuntimeException('SFTP (ssh2) extension not available in runtime.');
        }

        $host = (string)($payload['host'] ?? '');
        $user = (string)($payload['user'] ?? '');
        $password = (string)($payload['password'] ?? '');
        $path = (string)($payload['path'] ?? '.');
        $port = (int)($payload['port'] ?? 22);

        if ($host === '' || $user === '') {
            throw new RuntimeException('SFTP adapter requires host and user.');
        }

        $conn = @ssh2_connect($host, $port);
        if ($conn === false) {
            throw new RuntimeException('SFTP connection failed.');
        }

        if (!@ssh2_auth_password($conn, $user, $password)) {
            throw new RuntimeException('SFTP authentication failed.');
        }

        $sftp = @ssh2_sftp($conn);
        if ($sftp === false) {
            throw new RuntimeException('SFTP subsystem init failed.');
        }

        $uri = 'ssh2.sftp://' . (int)$sftp . $path;
        $list = @scandir($uri);
        $records = [];
        $mode = strtolower((string)($payload['mode'] ?? 'list'));
        $maxFiles = max(1, (int)($payload['max_files'] ?? 50));
        $encoding = strtolower((string)($payload['encoding'] ?? 'base64'));

        if (is_array($list)) {
            foreach ($list as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $remoteFile = rtrim($path, '/') . '/' . $item;
                if ($mode === 'download') {
                    if (count($records) >= $maxFiles) {
                        break;
                    }

                    $fileUri = 'ssh2.sftp://' . (int)$sftp . $remoteFile;
                    $raw = @file_get_contents($fileUri);
                    if ($raw === false) {
                        continue;
                    }

                    $records[] = [
                        'file' => $item,
                        'content' => $encoding === 'text' ? $raw : base64_encode($raw),
                        'encoding' => $encoding === 'text' ? 'text' : 'base64',
                        'size' => strlen($raw),
                    ];
                    continue;
                }

                $records[] = ['file' => $item];
            }
        }

        return [
            'records' => $records,
            'meta' => [
                'adapter' => 'sftp',
                'mode' => $mode,
                'count' => count($records),
            ],
        ];
    }
}
