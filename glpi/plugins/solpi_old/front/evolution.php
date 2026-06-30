<?php

$config_file = __DIR__ . '/../config.json';

$config = [];

if (file_exists($config_file)) {
    $config = json_decode(file_get_contents($config_file), true);
}

echo "<h1>Evolution API - SOLPI</h1>";

echo "<hr>";

echo "<h2>Status da Integração</h2>";

$api_url = $config['api_url'] ?? '';
$api_token = $config['api_token'] ?? '';

if (!empty($api_url) && !empty($api_token)) {

    echo "<p style='color:green'><b>Configuração encontrada</b></p>";

    echo "<p><b>URL:</b> " . htmlspecialchars($api_url) . "</p>";

    echo "<p><b>Token:</b> " . htmlspecialchars($api_token) . "</p>";

} else {

    echo "<p style='color:red'><b>Evolution API não configurada</b></p>";
}

echo "<hr>";

echo "<h2>Teste de Envio WhatsApp</h2>";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $telefone = $_POST['telefone'] ?? '';
    $mensagem = $_POST['mensagem'] ?? '';

    echo "<p style='color:green'><b>Simulação realizada com sucesso</b></p>";

    echo "<p><b>Telefone:</b> "
        . htmlspecialchars($telefone)
        . "</p>";

    echo "<p><b>Mensagem:</b><br>"
        . nl2br(htmlspecialchars($mensagem))
        . "</p>";

    echo "<hr>";
}

echo "<form method='post'>";

echo "<p>";
echo "<label>Telefone:</label><br>";
echo "<input type='text' name='telefone' value='5511999999999' size='30'>";
echo "</p>";

echo "<p>";
echo "<label>Mensagem:</label><br>";
echo "<textarea name='mensagem' rows='5' cols='60'>";
echo "Olá! Seu chamado foi resolvido? Responda SIM para confirmar.";
echo "</textarea>";
echo "</p>";

echo "<input type='submit' value='Simular Envio'>";

echo "</form>";

echo "<hr>";

echo "<h2>Próximas Funcionalidades</h2>";

echo "<ul>";
echo "<li>Enviar mensagem real pela Evolution API</li>";
echo "<li>Receber resposta do usuário</li>";
echo "<li>Confirmar ticket automaticamente</li>";
echo "<li>Solicitar avaliação</li>";
echo "<li>Atualizar GLPI automaticamente</li>";
echo "</ul>";