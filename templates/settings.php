<div class="container-fluid">

    <h1>SOLPI - Configurações</h1>

    <table class="table table-bordered">

        <thead>

            <tr>

                <th>Configuração</th>

                <th>Valor</th>

            </tr>

        </thead>

        <tbody>

        <?php foreach($settings as $key => $value): ?>

            <tr>

                <td><?= htmlspecialchars($key) ?></td>

                <td><?= htmlspecialchars((string)$value) ?></td>

            </tr>

        <?php endforeach; ?>

        </tbody>

    </table>

</div>
