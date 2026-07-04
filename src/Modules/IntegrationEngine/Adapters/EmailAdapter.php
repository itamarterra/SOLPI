<?php

declare(strict_types=1);

namespace SOLPI\Modules\IntegrationEngine\Adapters;

use RuntimeException;

final class EmailAdapter implements SourceAdapterInterface
{
    public function ingest(array $payload, array $context = []): array
    {
        if (!function_exists('imap_open')) {
            throw new RuntimeException('IMAP extension not available in runtime.');
        }

        $mailbox = (string)($payload['mailbox'] ?? '');
        $user = (string)($payload['user'] ?? '');
        $password = (string)($payload['password'] ?? '');
        $criteria = (string)($payload['criteria'] ?? 'ALL');

        if ($mailbox === '' || $user === '') {
            throw new RuntimeException('Email adapter requires mailbox and user.');
        }

        $inbox = @imap_open($mailbox, $user, $password);
        if ($inbox === false) {
            throw new RuntimeException('IMAP connection failed.');
        }

        $ids = imap_search($inbox, $criteria) ?: [];
        $records = [];

        foreach ($ids as $id) {
            $overview = imap_fetch_overview($inbox, (string)$id, 0);
            $item = is_array($overview) && isset($overview[0]) ? (array)$overview[0] : [];
            $records[] = [
                'id' => (int)$id,
                'subject' => $item['subject'] ?? null,
                'from' => $item['from'] ?? null,
                'date' => $item['date'] ?? null,
            ];
        }

        imap_close($inbox);

        return [
            'records' => $records,
            'meta' => [
                'adapter' => 'email',
                'count' => count($records),
            ],
        ];
    }
}
