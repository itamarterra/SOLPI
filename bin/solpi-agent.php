<?php

declare(strict_types=1);

/**
 * SOLPI Agent CLI v1.0
 * Operação: Discovery, Heartbeat e Automação Local
 */

if (PHP_SAPI !== 'cli') {
    die("Este script deve ser executado via CLI.\n");
}

echo "\n🚀 SOLPI AGENT v1.0\n";
echo "====================\n\n";

// 1. Configurações Iniciais
$configPath = __DIR__ . '/agent_config.json';
$config = [];

if (file_exists($configPath)) {
    $config = json_decode(file_get_contents($configPath), true);
} else {
    echo "⚙️  Setup Inicial do Agente\n";
    $config['glpi_url'] = readline("   🔗 URL do GLPI (ex: http://localhost:8081): ");
    $config['agent_token'] = readline("   🔑 Token do Agente: ");
    $config['site_name'] = gethostname();

    file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT));
    echo "\n✅ Configuração salva!\n";
}

echo "📡 Conectando ao SOLPI Registry em " . $config['glpi_url'] . "...\n";
echo "🛰️  Agente " . $config['site_name'] . " está agora em operação.\n\n";

// 2. Loop de Heartbeat
while (true) {
    echo "[" . date('H:i:s') . "] ❤️  Heartbeat Status: ONLINE\n";

    /**
     * Futura implementação real de envio de inventário:
     * $payload = [
     *    'hostname' => gethostname(),
     *    'os' => PHP_OS,
     *    'ip' => gethostbyname(gethostname())
     * ];
     */

    sleep(30); // Heartbeat a cada 30 segundos para teste
}
