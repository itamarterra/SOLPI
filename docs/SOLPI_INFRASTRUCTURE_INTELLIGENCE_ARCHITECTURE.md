# SOLPI Infrastructure Intelligence Platform (SIIP) - Arquitetura Enterprise

## 1. Visão Geral
A SIIP é o ecossistema de inteligência do SOLPI projetado para criar e manter um **Digital Twin** dinâmico da infraestrutura. Ela opera através de descoberta multi-protocolo, mapeamento de topologia de rede e correlação semântica de incidentes.

## 2. Princípios de Design
- **Agnóstico de Fornecedor**: Abstração via *Drivers* e *Adapters* (RFCs padrão: SNMP, LLDP, IEEE 802.1AB).
- **Consistência de Estado (Digital Twin)**: Toda entidade possui um estado *Atual* e um *Histórico Versionado*.
- **Confiança Ponderada**: Relacionamentos possuem scores baseados na fonte da verdade.
- **SOLID & Clean Architecture**: Separação clara entre protocolos de rede e lógica de negócio.

## 3. Diagrama de Componentes (SIIP)

```text
[ Discovery Layer ]  <-- (SNMP, SSH, WMI, APIs)
        |
[ Data Normalization ] <-- (Canonical Models)
        |
[ Inference Engine ]   <-- (Relationship, Topology, Dependency)
        |
[ Graph Persistence ]  <-- (Infrastructure Graph / Digital Twin)
        |
[ Intelligence API ]   <-- (AI Queries, UI Viewer)
```

## 4. Estrutura de Módulos (Engines)

### 4.1 Discovery Engine (`SOLPI\Modules\Discovery`)
- `ProtocolManager`: Orquestra as tentativas de conexão (ICMP -> SNMP -> SSH).
- `Adapters`: Drivers específicos por fabricante/tecnologia (CiscoDriver, VMwareDriver, DockerDriver).
- `ScannerService`: Gerencia varreduras de rede e inventário de portas.

### 4.2 Network Topology Engine (`SOLPI\Modules\Topology`)
- `L2Mapper`: Mapeia vizinhanças via LLDP/CDP e tabelas MAC.
- `L3Mapper`: Mapeia roteamento e VLANs.
- `PortMapper`: Vincula portas físicas a equipamentos finais.

### 4.3 Digital Twin Engine (`SOLPI\Modules\DigitalTwin`)
- `StateManager`: Mantém a "Foto" atual da rede.
- `SnapshotService`: Cria versões da infraestrutura para auditoria de mudanças.
- `DifferenceEngine`: Detecta discrepâncias (Ex: IP mudou, Ativo sumiu).

### 4.4 Confidence Engine (`SOLPI\Modules\Intelligence`)
- `ScoringService`: Atribui pesos (1.00 para API oficial, 0.70 para Inferência).

## 5. Estrutura de Banco de Dados (Schema Enterprise)

### `glpi_plugin_solpi_inframap_nodes`
Representa qualquer entidade (Hardware, Software, Humano).
- `uuid`: Identificador único global.
- `external_id`: ID no GLPI ou Zabbix.
- `class`: (Asset, Service, User, NetworkNode).
- `metadata`: JSON com propriedades técnicas.

### `glpi_plugin_solpi_inframap_edges`
Representa as conexões e dependências.
- `source_uuid`, `target_uuid`.
- `relation_type`: (PHYSICAL_LINK, DEPENDS_ON, RUNS_ON, MANAGES).
- `confidence`: float (Score de certeza).
- `source_protocol`: (SNMP, Agent, Manual).

### `glpi_plugin_solpi_inframap_history`
Log de todas as mudanças detectadas pelo Change Detection Engine.

## 6. Estratégia de IA (Natural Language Insights)
A IA não apenas lerá o banco, mas consumirá o **Infrastructure Graph**. 
Ao perguntar "Quem depende do SQL01?", a IA executará um *Traverser* no grafo de arestas `DEPENDS_ON` e retornará a árvore de impacto em linguagem natural.

## 7. Estratégia de Escalabilidade
- Processamento em chunks via `IntegrationEngine` (Jobs).
- Cache de topologia em Redis (opcional) ou tabelas de busca rápida.
- Drivers carregados via *Lazy Loading* apenas quando necessário.

## 8. Roadmap de Implementação
1.  **Fase 1 (Core SIIP)**: Modelos canônicos e Repositories de Grafo com suporte a versionamento. (Concluído)
2.  **Fase 2 (Discovery Base)**: Implementação de SNMP e ICMP Scanner agnóstico. (Concluído)
3.  **Fase 3 (Topology Engine)**: Lógica de vizinhança L2 (LLDP/CDP). (Concluído)
4.  **Fase 4 (Digital Twin & Change)**: Sistema de snapshots e detecção de mudanças. (Concluído)
5.  **Fase 5 (AI Logic)**: Interface de consulta em linguagem natural.
4.  **Fase 4 (Digital Twin & Change)**: Sistema de snapshots e detecção de mudanças.
5.  **Fase 5 (AI Logic)**: Interface de consulta em linguagem natural.
6.  **Fase 6 (Visualizer)**: Mapas interativos (Físico, Lógico, Dependência).

## 9. Segurança
- Credenciais armazenadas via `SecurityHelper` (Criptografia AES-256).
- Respeito total às ACLs do GLPI 11.
