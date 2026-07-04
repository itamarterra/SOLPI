<?php

echo "<h1>SOLPI v2.0</h1>";

echo "<p><b>Sistema de Otimização e Gestão de Processos de TI</b></p>";

echo "<hr>";

echo "<h2>Menu Principal</h2>";

echo "<table border='1' cellpadding='15' cellspacing='0' width='500'>";

echo "<tr>";
echo "<td><a href='dashboard.php'>📊 Dashboard</a></td>";
echo "</tr>";

echo "<tr>";
echo "<td><a href='tickets.php'>🎫 Tickets SOLPI</a></td>";
echo "</tr>";

echo "<tr>";
echo "<td><a href='/solpi-import.php' target='_blank' rel='noopener noreferrer'>📥 Janela de Importação SOLPI (nova aba)</a></td>";
echo "</tr>";

echo "<tr>";
echo "<td><a href='glpi_tickets.php'>🖥️ Chamados GLPI</a></td>";
echo "</tr>";

echo "<tr>";
echo "<td><a href='config.php'>⚙️ Configurações</a></td>";
echo "</tr>";

echo "<tr>";
echo "<td><a href='evolution.php'>📱 Evolution API</a></td>";
echo "</tr>";

echo "<tr>";
echo "<td><a href='index.php'>🏠 Página Inicial</a></td>";
echo "</tr>";

echo "</table>";

echo "<br><hr>";

echo "<h2>Status do Projeto</h2>";

echo "<ul>";
echo "<li>✅ Plugin SOLPI instalado</li>";
echo "<li>✅ Banco de dados criado</li>";
echo "<li>✅ Integração com GLPI</li>";
echo "<li>✅ Captura de chamados reais</li>";
echo "<li>✅ Captura de problema e solução</li>";
echo "<li>✅ Confirmação via WhatsApp simulada</li>";
echo "<li>✅ Fechamento automático de chamados</li>";
echo "<li>✅ Pesquisa de satisfação</li>";
echo "<li>🔄 Integração Evolution API</li>";
echo "</ul>";

echo "<hr>";

echo "<h2>Objetivo do SOLPI</h2>";

echo "<p>";
echo "Automatizar o encerramento de chamados do GLPI através da confirmação do usuário via WhatsApp, registrando problema, solução e satisfação.";
echo "</p>";

echo "<hr>";

echo "<h2>Fluxo do SOLPI</h2>";

echo "<ol>";
echo "<li>Técnico resolve o chamado no GLPI</li>";
echo "<li>SOLPI identifica o ticket solucionado</li>";
echo "<li>SOLPI captura problema e solução</li>";
echo "<li>SOLPI envia mensagem via WhatsApp</li>";
echo "<li>Usuário confirma o atendimento</li>";
echo "<li>SOLPI fecha o chamado automaticamente</li>";
echo "<li>Usuário avalia o atendimento</li>";
echo "<li>Dashboard atualiza os indicadores</li>";
echo "</ol>";