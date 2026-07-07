<?php

declare(strict_types=1);

/**
 * SOLPI Agent CLI v1.1
 * Operação: Discovery, Heartbeat e Automação Local
 */

if (PHP_SAPI !== 'cli') {
    die("Este script deve ser executado via CLI.\n");
}

echo "\n🚀 SOLPI AGENT v1.1\n";
echo "====================\n\n";

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

$registerUrl = rtrim($config['glpi_url'], '/') . '/plugins/solpi/ajax/agent_register.php';

echo "📡 Conectando ao SOLPI Registry em " . $config['glpi_url'] . "...\n";
echo "🛰️  Agente " . $config['site_name'] . " está agora em operação.\n\n";

// Loop de Heartbeat Real
while (true) {
    try {
        $payload = json_encode(['site_name' => $config['site_name'], 'token' => $config['agent_token']]);

        $ch = curl_init($registerUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            echo "[" . date('H:i:s') . "] ❤️  Heartbeat Status: ONLINE\n";
        } else {
            echo "[" . date('H:i:s') . "] ⚠️  Erro na conexão (HTTP $httpCode). Tentando novamente...\n";
        }
    } catch (Exception $e) {
        echo "[" . date('H:i:s') . "] ❌ Falha: " . $e->getMessage() . "\n";
    }

    sleep(30);
}
