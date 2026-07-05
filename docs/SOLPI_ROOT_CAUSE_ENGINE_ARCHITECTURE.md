# SOLPI Root Cause Engine - Arquitetura Técnica v1.0

## 1. Visão Geral
O **Root Cause Engine (RCE)** é o módulo analítico do *SOLPI Incident Intelligence Engine*. Ele consome o *Incident Graph* gerado pelo *Relationship Engine* e aplica algoritmos de inferência e IA para determinar a origem primária de falhas, estimar o impacto real no negócio e sugerir ações corretivas.

## 2. Arquitetura de Componentes

### 2.1 Módulos Internos
1.  **Topology Traverser**: Navega recursivamente pelas dependências técnicas (Upstream) para encontrar o ponto de falha mais provável.
2.  **Impact Calculator**: Calcula o raio de alcance da falha (Downstream), listando serviços e departamentos afetados.
3.  **Heuristic Logic**: Aplica regras pré-definidas de infraestrutura (ex: se o Gateway está offline, todos os ativos dependentes estão incomunicáveis).
4.  **AI Inference**: Utiliza LLMs para analisar a semelhança semântica entre erros recentes e incidentes históricos com solução confirmada.

## 3. Fluxo de Análise (Fluxograma)
```text
[ Evento: Alerta ou Chamado ]
       |
       v
[ Mapear no Incident Graph ]
       |
       v
[ Identificar Ativo Central ]
       |
       v
[ Subir Topologia (Upstream) ] ----> [ Gateway/Core Offline? ] --(Sim)--> [ Causa Provável: Infra Base ]
       |                                       |
       |--(Não)---------------------------------
       v
[ Buscar Incidentes Semelhantes ] ----> [ Padrão Detectado? ] --(Sim)--> [ Sugerir Causa Histórica ]
       |
       v
[ Consolidar Resultados ] ----> [ Gerar Insights p/ Técnico ]
```

## 4. Estrutura de Dados (Extensões)

### `glpi_plugin_solpi_root_causes`
Armazena as análises processadas.
- `id`: primary key
- `target_id`: ID da entidade analisada (ex: Ticket:150)
- `target_type`: Tipo
- `suspected_cause_id`: ID do nó suspeito (ex: Computer:40)
- `confidence_score`: float (0.00 a 1.00)
- `impact_summary`: JSON (Serviços e usuários afetados)
- `status`: (PENDING, VALIDATED, REJECTED)

## 5. Classes e Interfaces Principais

### 5.1 Services (`SOLPI\Modules\Intelligence\Services`)
- `RootCauseAnalyzer`: Orquestrador que consolida os dados do grafo e chama a IA.
- `ImpactAnalyzer`: Serviço especializado em medir a profundidade do impacto.
- `HeuristicEngine`: Implementa lógica dedutiva baseada em grafos.

### 5.2 Repositories (`SOLPI\Modules\Intelligence\Repositories`)
- `RootCauseRepository`: Persistência das análises e feedbacks dos técnicos.

## 6. Integração com Zabbix
Ao receber um alerta crítico, o RCE entra em modo "Real-time Insight":
1.  Busca o ativo no Grafo.
2.  Verifica se há alertas em vizinhos de nível superior (Switches/Hosts).
3.  Se houver, agrupa o incidente como "Cascata".
4.  Sugere ao técnico o fechamento em massa ou vinculação ao chamado principal.

## 7. Roadmap de Implementação

- [x] **Módulo 1: Topology Traverser**: Implementação da busca recursiva em grafos. (Concluído)
- [ ] **Módulo 2: Heuristic & Logic**: Criação das regras básicas de causa raiz (Rede, Storage, VM).
- [ ] **Módulo 3: AI Insights**: Integração com OpenAI/Gemini para explicação textual da causa.
- [ ] **Módulo 4: Impact Dashboard**: Painel visual de "Quem está sendo afetado agora".
- [ ] **Módulo 5: Automatic Grouping**: Lógica de agrupamento de chamados duplicados por causa comum.

## 8. Dependências
- `Relationship Engine`: Requisito obrigatório para dados de grafo.
- `AI Module`: Requisito para inferência semântica.
- `Zabbix Integration`: Requisito para análise de eventos vivos.
