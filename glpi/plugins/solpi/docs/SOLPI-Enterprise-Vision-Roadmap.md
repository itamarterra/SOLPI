# SOLPI Professional

Smart Operational Learning Platform for IT

## Missao

Desenvolver uma plataforma inteligente integrada ao GLPI capaz de centralizar, organizar, automatizar e aprender continuamente com todas as informacoes relacionadas ao ambiente de TI de uma empresa, usando inteligencia artificial para reduzir trabalho manual, aumentar produtividade e transformar dados em conhecimento.

## Visao

O SOLPI nao deve ser apenas um plugin para o GLPI. A meta e evoluir para uma plataforma enterprise de gestao inteligente de TI, capaz de integrar multiplos sistemas, consolidar informacoes, aprender continuamente e atuar como assistente corporativo para suporte, infraestrutura, seguranca, operacoes e gestao.

## Objetivo Geral

Transformar o GLPI em uma plataforma inteligente capaz de integrar informacoes de sistemas distintos, consolidar automaticamente ativos e dados da empresa em uma estrutura unica e utilizar inteligencia artificial para apoiar tecnicos, gestores e usuarios na tomada de decisao.

## Objetivos Estrategicos

### Centralizacao das informacoes

Concentrar em um unico ambiente os dados da empresa:

- Empresas
- Filiais
- Usuarios
- Equipamentos
- Servidores
- Maquinas virtuais
- Impressoras
- Switches
- Roteadores
- Licencas
- Contratos
- Documentos
- Projetos
- Chamados
- Historico
- Auditorias
- Monitoramento
- Financeiro
- Conhecimento

Tudo organizado em uma arvore logica unica e relacionavel.

### Integracao inteligente

Receber informacoes automaticamente de qualquer sistema externo:

- ERP
- CRM
- APIs
- Bancos de dados
- LDAP
- Active Directory
- Planilhas
- CSV
- JSON
- XML
- Sistemas proprietarios
- Webhooks

O SOLPI deve identificar registros existentes, evitar duplicacoes e manter sincronizacao segura.

### Entity Resolution

Detectar automaticamente quando registros representam a mesma entidade.

Exemplos de aplicacao:

- Empresas
- Usuarios
- Equipamentos
- Licencas
- Contratos
- Documentos

### Base de conhecimento inteligente

Construir uma base de conhecimento automaticamente com os dados do proprio ambiente.

Fontes principais:

- Chamados resolvidos
- Solucoes aplicadas
- Documentacao
- Historico
- Procedimentos
- Incidentes
- Problemas recorrentes

### Inteligencia artificial

Disponibilizar um assistente capaz de:

- Responder perguntas em linguagem natural
- Localizar informacoes rapidamente
- Sugerir solucoes
- Auxiliar tecnicos
- Automatizar tarefas
- Gerar documentacao
- Produzir relatorios
- Classificar chamados
- Detectar padroes
- Identificar riscos

### Automacao

Automatizar processos como:

- Abertura de chamados
- Encerramento de chamados
- Aprovacoes
- Fluxos
- Inventario
- Atualizacao de ativos
- Atualizacao de usuarios
- Atualizacao de licencas
- Integracoes
- Sincronizacoes

### Monitoramento

Integrar com ferramentas como:

- Zabbix
- Grafana
- Microsoft 365
- Evolution API
- WhatsApp
- SMTP
- Active Directory

### Seguranca

Implementar mecanismos avancados de seguranca:

- Controle de acesso por perfil
- Auditoria completa
- Historico de alteracoes
- Registro de eventos
- Protecao contra ataques
- Criptografia de dados sensiveis
- Validacao de entrada
- Boas praticas de desenvolvimento seguro

### Escalabilidade

Projetar a arquitetura para grandes empresas, MSPs, data centers, service desk, NOC e SOC, com suporte a grandes volumes de registros.

## Diferencial do SOLPI

O diferencial nao e apenas gerenciar chamados.

O objetivo e criar um ecossistema inteligente de gestao de TI onde todas as informacoes da empresa estejam conectadas.

Exemplo de visao de relacoes:

Empresa -> Usuarios -> Equipamentos -> Licencas -> Contratos -> Documentos -> Monitoramento -> Projetos -> Chamados -> Conhecimento -> Historico -> Auditoria -> Financeiro -> Inteligencia Artificial

## Principios de Arquitetura

- Nao criar duplicatas.
- Nao perder dados de origem.
- Nao sobrescrever campos criticos sem politica e auditoria.
- Manter ingestao idempotente.
- Registrar trilha auditavel de toda decisao.
- Evoluir por fases pequenas e verificaveis.
- Preservar compatibilidade com GLPI.
- Escalar por filas, workers e checkpoints.

## Mapa do Projeto Atual

### Ja consolidado

- IntegrationEngine como nucleo de ingestao.
- Adapters para REST, SOAP, CSV, JSON, XML, SQL, LDAP, FTP, SFTP, email e webhook.
- Entity resolution, merge policy, review queue, dead letter queue e audit trail.
- Knowledge graph e governanca de qualidade.
- Checkpoints para fontes incrementais.
- Classificacao inicial de chamados e entidades.
- Otimizacoes de lote e paginação para grandes volumes.

### Proximos incrementos

- Aprimorar particionamento e micro-batching por origem.
- Expandir classificacao com contexto historico.
- Melhorar assistente IA com busca semantica e respostas assistidas.
- Fortalecer isolamento multi-tenant e politicas de retencao.
- Evoluir integracoes de monitoramento e automacao operacional.

## Referencias

- Blueprint tecnico: [SOLPI-IntegrationEngine-Architecture.md](SOLPI-IntegrationEngine-Architecture.md)
- Motor de integracao: [src/Modules/IntegrationEngine/README.md](../src/Modules/IntegrationEngine/README.md)
