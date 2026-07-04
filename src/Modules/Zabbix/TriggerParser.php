<?php

declare(strict_types=1);

namespace SOLPI\Modules\Zabbix;

/**
 * Analisa e limpa os dados recebidos do Zabbix
 */
final class TriggerParser
{
    /**
     * @param array<string,mixed> $data
     * @return array{host:string, severity:string, message:string, event_id:int}
     */
    public function parse(array $data): array
    {
        return [
            'host'     => (string)($data['host'] ?? 'Desconhecido'),
            'severity' => $this->mapSeverity((int)($data['severity'] ?? 0)),
            'message'  => trim((string)($data['trigger_description'] ?? 'Alerta sem descrição')),
            'event_id' => (int)($data['event_id'] ?? 0),
        ];
    }

    private function mapSeverity(int $level): string
    {
        return match($level) {
            1 => 'INFORMATIVO',
            2 => 'ATENÇÃO',
            3 => 'MÉDIO',
            4 => 'ALTO',
            5 => 'CRÍTICO',
            default => 'DESCONHECIDO'
        };
    }
}
