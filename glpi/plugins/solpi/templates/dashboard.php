<div class="container-fluid">

    <h1 class="mb-4">
        SOLPI Professional
    </h1>

    <div class="row">

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Chamados Abertos</h6>
                    <h2><?= $dashboard->openTickets ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Chamados Fechados</h6>
                    <h2><?= $dashboard->closedTickets ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Alertas</h6>
                    <h2><?= $dashboard->alerts ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6>Usuários</h6>
                    <h2><?= $dashboard->users ?></h2>
                </div>
            </div>
        </div>

    </div>

    <br>

    <div class="row">

        <div class="col-md-4">

            <div class="card">

                <div class="card-header">

                    Integrações

                </div>

                <div class="card-body">

                    Zabbix:
                    <strong><?= $dashboard->zabbixOnline ? 'ONLINE' : 'OFFLINE' ?></strong>

                    <hr>

                    WhatsApp:
                    <strong><?= $dashboard->whatsappOnline ? 'ONLINE' : 'OFFLINE' ?></strong>

                    <hr>

                    IA:
                    <strong><?= $dashboard->aiOnline ? 'ONLINE' : 'OFFLINE' ?></strong>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card">

                <div class="card-header">

                    Mensagens

                </div>

                <div class="card-body">

                    WhatsApp

                    <h2><?= $dashboard->messages ?></h2>

                </div>

            </div>

        </div>

        <div class="col-md-4">

            <div class="card">

                <div class="card-header">

                    IA

                </div>

                <div class="card-body">

                    Requisições

                    <h2><?= $dashboard->aiRequests ?></h2>

                </div>

            </div>

        </div>

    </div>

</div>
