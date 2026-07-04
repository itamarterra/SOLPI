<?php
declare(strict_types=1);

namespace SOLPI\Modules\Zabbix;

final class TriggerParser
{
    public function parse(array $data): array
    {
        return [
            'host'        => $data['host'] ?? 'Desconhecido',
            'severity'    => $data['severity'] ?? 'Informação',
            'description' => $data['trigger_name'] ?? $data['event_name'] ?? 'Alerta sem nome',
            'status'      => $data['status'] ?? 'PROBLEM',
            'timestamp'   => date('Y-m-d H:i:s'),
            'details'     => json_encode($data)
        ];
    }
}

