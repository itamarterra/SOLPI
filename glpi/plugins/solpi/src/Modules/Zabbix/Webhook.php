<?php
declare(strict_types=1);

namespace SOLPI\Modules\Zabbix;

final class Webhook
{
    private TriggerParser $parser;

    public function __construct()
    {
        $this->parser = new TriggerParser();
    }

    public function handle(array $payload): bool
    {
        $alert = $this->parser->parse($payload);
        
        // Lógica para processar o alerta (ex: abrir ticket se for PROBLEM)
        if ($alert['status'] === 'PROBLEM') {
            // Aqui chamaria o TicketRepository
            return true;
        }

        return false;
    }
}

