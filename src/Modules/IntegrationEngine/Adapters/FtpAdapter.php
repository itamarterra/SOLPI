<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Adapters;

use RuntimeException;

final class FtpAdapter implements SourceAdapterInterface
{
    public function ingest(array $payload, array $context = []): array
    {
        if (!function_exists('ftp_connect')) {
            throw new RuntimeException('FTP extension not available in runtime.');
        }

        $host = (string)($payload['host'] ?? '');
        $user = (string)($payload['user'] ?? 'anonymous');
        $password = (string)($payload['password'] ?? '');
        $path = (string)($payload['path'] ?? '.');
        $port = (int)($payload['port'] ?? 21);

        if ($host === '') {
            throw new RuntimeException('FTP adapter requires host.');
        }

        $conn = ftp_connect($host, $port, (int)($payload['timeout'] ?? 30));
        if ($conn === false) {
            throw new RuntimeException('FTP connection failed.');
        }

        if (!@ftp_login($conn, $user, $password)) {
            ftp_close($conn);
            throw new RuntimeException('FTP login failed.');
        }

        $mode = strtolower((string)($payload['mode'] ?? 'list'));
        $files = ftp_nlist($conn, $path);

        ftp_close($conn);

        $records = [];
        if (is_array($files)) {
            $maxFiles = max(1, (int)($payload['max_files'] ?? 50));
            $encoding = strtolower((string)($payload['encoding'] ?? 'base64'));

            foreach ($files as $file) {
                if ($mode === 'download') {
                    if (count($records) >= $maxFiles) {
                        break;
                    }

                    $localTmp = tempnam(sys_get_temp_dir(), 'solpi_ftp_');
                    if ($localTmp === false) {
                        continue;
                    }

                    $connDl = ftp_connect($host, $port, (int)($payload['timeout'] ?? 30));
                    if ($connDl === false || !@ftp_login($connDl, $user, $password)) {
                        if ($connDl !== false) {
                            ftp_close($connDl);
                        }
                        @unlink($localTmp);
                        continue;
                    }

                    $ok = @ftp_get($connDl, $localTmp, (string)$file, FTP_BINARY);
                    ftp_close($connDl);
                    if (!$ok) {
                        @unlink($localTmp);
                        continue;
                    }

                    $raw = file_get_contents($localTmp);
                    @unlink($localTmp);
                    if ($raw === false) {
                        continue;
                    }

                    $records[] = [
                        'file' => $file,
                        'content' => $encoding === 'text' ? $raw : base64_encode($raw),
                        'encoding' => $encoding === 'text' ? 'text' : 'base64',
                        'size' => strlen($raw),
                    ];
                    continue;
                }

                $records[] = ['file' => (string)$file];
            }
        }

        return [
            'records' => $records,
            'meta' => [
                'adapter' => 'ftp',
                'mode' => $mode,
                'count' => count($records),
            ],
        ];
    }
}
