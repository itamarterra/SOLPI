# SOLPI Relationship Engine - Arquitetura Técnica v1.0

## 1. Visão Geral
O **Relationship Engine (RE)** é o componente central do *SOLPI Incident Intelligence Engine*. Sua função é transcender a estrutura linear de chamados do GLPI, construindo um **Grafo de Incidentes** multi-dimensional que mapeia dependências técnicas, semânticas e de negócio em tempo real.

## 2. Arquitetura de Componentes

### 2.1 Camadas do Motor
1.  **Ingestion Layer**: Captura eventos do Zabbix Webhook e Hooks nativos do GLPI 11.
2.  **Semantic Layer**: Utiliza o módulo `SOLPI\AI` para gerar vetores de similaridade de textos (problemas e soluções).
3.  **Topology Layer**: Mapeia a árvore de ativos (CMDB) e serviços do GLPI.
4.  **Inference Engine**: O algoritmo que cruza os dados das camadas anteriores para detectar relações indiretas e causas raiz.
5.  **Persistence Layer**: Armazena o grafo em tabelas de relacionamentos ponderados (scores).

## 3. Diagrama de Relacionamentos (Lógica do Grafo)
```text
[ Ativo ] <---(Monitora)--- [ Zabbix Alert ]
    |                           |
(Depende)                (Gera/Relaciona)
    |                           |
[ Ativo Pai ]           [ Chamado SOLPI ] <---(Semelhança)---> [ Chamado Histórico ]
    |                           |
(Suporta)                (Afeta)
    |                           |
[ Serviço ] <---------- [ Usuário / Departamento ]
```

## 4. Estrutura de Dados (Novas Tabelas)

### `glpi_plugin_solpi_incident_graphs`
Mapeia as conexões detectadas entre entidades.
- `id`: primary key
- `source_id`: ID da entidade origem
- `source_type`: Tipo (Ticket, Item_Computer, User, etc)
- `target_id`: ID da entidade destino
- `target_type`: Tipo
- `relation_type`: (PARENT, CHILD, SIMILAR, DEPENDENCY, ROOT_CAUSE)
- `score`: float (0.00 a 1.00) - Força do relacionamento
- `is_manual`: boolean (se foi validado por um técnico)

### `glpi_plugin_solpi_embeddings`
Armazena a representação vetorial para cálculos de similaridade ultra-rápidos.
- `itemtype`: String
- `items_id`: Integer
- `vector`: LongBLOB/JSON (Dependendo do DB)
- `last_update`: DateTime

## 5. Classes e Interfaces Principais

### 5.1 Services (`SOLPI\Modules\Intelligence\Services`)
- `RelationshipManager`: Orquestrador principal do grafo.
- `SimilarityService`: Calcula a distância entre chamados usando o motor de IA.
- `DependencyResolver`: Varre o CMDB do GLPI e alertas do Zabbix para montar a árvore de impacto.
- `RootCauseAnalyzer`: Aplica lógica dedutiva para sugerir a origem de incidentes em cascata.

### 5.2 Repositories (`SOLPI\Modules\Intelligence\Repositories`)
- `GraphRepository`: Operações CRUD no Grafo de Incidentes.
- `VectorRepository`: Gestão de memória semântica (Embeddings).

## 6. Fluxo de Operação: Alerta Zabbix -> Inteligência

1.  **Recebimento**: O Webhook recebe um alerta de "High CPU" no `Server_SQL_01`.
2.  **Identificação**: O `RelationshipManager` localiza o ativo no GLPI.
3.  **Expansão**: O `DependencyResolver` identifica que `Server_SQL_01` suporta o `Serviço_ERP`.
4.  **Cruzamento**: O motor busca chamados abertos nos últimos 30 minutos para usuários do departamento `Financeiro` (que usa o ERP).
5.  **Inferência**: O sistema detecta 5 chamados de "Lentidão no Sistema".
6.  **Ação**: O SOLPI sugere ao técnico: *"Estes 5 chamados são reflexo do Alerta Zabbix #123. Deseja criar um Chamado Pai?"*

## 7. Roadmap de Implementação

- [x] **Módulo 1: Graph Base**: Criação das tabelas e Repositories de relacionamento. (Concluído)
- [x] **Módulo 2: Semantic Memory**: Implementação dos Embeddings para similaridade de chamados. (Concluído)
- [x] **Módulo 3: Dependency Mapper**: Crawler de ativos e serviços nativos do GLPI. (Concluído)
- [x] **Módulo 4: Zabbix Link**: Integração do Webhook com a lógica de descoberta de ativos. (Concluído)
- [x] **Módulo 5: UI Insight**: Nova aba no Ticket do GLPI exibindo o "Mapa do Incidente". (Concluído)

## 8. Segurança e Performance
- Uso intensivo de **Prepared Statements** (via QueryBuilder atualizado).
- Cache de relacionamentos para evitar recursividade infinita no banco de dados.
- Compatibilidade total com a API de permissões do GLPI 11.
