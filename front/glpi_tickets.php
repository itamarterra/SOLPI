<?php

$pdo = new PDO(
    "mysql:host=glpi-db;dbname=glpi",
    "root",
    "root"
);

if (isset($_GET['enviar'])) {

    $ticket_id = (int)$_GET['enviar'];

    $ticket = $pdo->query("
        SELECT *
        FROM glpi_tickets
        WHERE id = $ticket_id
    ")->fetch(PDO::FETCH_ASSOC);

    if ($ticket) {

        $existe = $pdo->query("
            SELECT COUNT(*)
            FROM glpi_plugin_solpi_tickets
            WHERE ticket_id = $ticket_id
        ")->fetchColumn();

        if (!$existe) {

            $solucao = $pdo->query("
                SELECT content
                FROM glpi_itilsolutions
                WHERE itemtype = 'Ticket'
                AND items_id = {$ticket['id']}
                ORDER BY id DESC
                LIMIT 1
            ")->fetchColumn();

            if (!$solucao) {
                $solucao = 'Sem solução cadastrada';
            }

            $stmt = $pdo->prepare("
                INSERT INTO glpi_plugin_solpi_tickets
                (
                    ticket_id,
                    telefone,
                    problema,
                    solucao,
                    status_whatsapp
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )
            ");

            $stmt->execute([
                $ticket['id'],
                '',
                strip_tags($ticket['content']),
                strip_tags($solucao),
                'AGUARDANDO'
            ]);

            echo "<p style='color:green'><b>Ticket enviado para o fluxo SOLPI com sucesso!</b></p>";

        } else {

            echo "<p style='color:orange'><b>Esse ticket já está no SOLPI.</b></p>";
        }
    }
}

echo "<h1>Chamados Reais do GLPI</h1>";

$sql = "
SELECT id, name, status
FROM glpi_tickets
ORDER BY id DESC
LIMIT 50
";

$resultado = $pdo->query($sql);

echo "<table border='1' cellpadding='10' cellspacing='0'>";

echo "<tr>";
echo "<th>ID</th>";
echo "<th>Título</th>";
echo "<th>Status</th>";
echo "<th>Ação SOLPI</th>";
echo "</tr>";

while ($row = $resultado->fetch(PDO::FETCH_ASSOC)) {

    switch ($row['status']) {

        case 1:
            $status_texto = "Novo";
            break;

        case 2:
            $status_texto = "Em andamento";
            break;

        case 3:
            $status_texto = "Planejado";
            break;

        case 4:
            $status_texto = "Pendente";
            break;

        case 5:
            $status_texto = "Solucionado";
            break;

        case 6:
            $status_texto = "Fechado";
            break;

        default:
            $status_texto = $row['status'];
    }

    echo "<tr>";

    echo "<td>".$row['id']."</td>";
    echo "<td>".$row['name']."</td>";
    echo "<td>".$status_texto."</td>";

    if ($row['status'] == 5) {

        echo "<td>";
        echo "<a href='?enviar=".$row['id']."'>Enviar WhatsApp</a>";
        echo "</td>";

    } else {

        echo "<td>-</td>";
    }

    echo "</tr>";
}

echo "</table>";

echo "<br><hr>";

echo "<h3>Objetivo do SOLPI</h3>";

echo "<ol>";
echo "<li>Detectar chamados solucionados</li>";
echo "<li>Capturar problema e solução do GLPI</li>";
echo "<li>Enviar confirmação via WhatsApp</li>";
echo "<li>Registrar resposta do usuário</li>";
echo "<li>Solicitar avaliação de satisfação</li>";
echo "<li>Fechar chamado automaticamente</li>";
echo "</ol>";