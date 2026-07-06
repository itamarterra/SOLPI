# SOLPI Discovery Engine - UX & Architecture Blueprint v5.0

## 1. Conceito de Design
A interface será baseada no **"Command Center Pattern"**. O foco é a densidade de informação inteligente sem poluição visual. Utilizaremos o framework **Tabler** (nativo do GLPI 11) com camadas customizadas de **Glassmorphism** e **Neumorphism** para destacar os cards de métricas.

## 2. Wireframe / Estrutura da Página (Layout 12 Colunas)

### 2.1 Dashboard Superior (Métricas de Impacto)
- **Grid 4x4**: 
    - Card 1: **Cérebro da Rede** (Total de Equipamentos + % de Ativos Monitorados).
    - Card 2: **Vizinhança Ativa** (Online vs Offline com gráfico de rosca interno).
    - Card 3: **Composição** (Ícones dinâmicos para Servidores, Switches, Impressoras).
    - Card 4: **Performance do Scan** (Tempo decorrido + Velocidade de processamento/s).

### 2.2 Painel Central (O Coração da Descoberta)
- **Coluna Esquerda (8 colunas)**:
    - **Discovery Config**: Tabs para "Faixa de IP", "CIDR" e "Auto-Detecção".
    - **Protocol Selector**: Chip-tags interativos para habilitar SNMP, WMI, SSH, etc.
    - **Real-time Results**: Tabela dinâmica com **Virtual Scrolling** para suportar milhares de ativos sem lentidão.
    - **Confidence Bar**: Indicador visual de 0-100% no nível de precisão de cada linha da tabela.

- **Coluna Direita / Sidebar (4 colunas)**:
    - **Digital Twin Monitor**: Resumo de mudanças detectadas (Novos IPs, MACs alterados).
    - **Incident Mini-Graph**: Renderização em tempo real do grafo de vizinhança do item selecionado.
    - **AI Quick Insights**: Caixa de chat flutuante para perguntas rápidas sobre o mapeamento.
    - **Event Stream**: Log de eventos "human-readable" (ex: "Novo Switch Core detectado no Rack A").

## 3. Fluxo de Experiência do Usuário (User Journey)
1.  **Inteligência Inicial**: O usuário entra e clica em "Detectar minha rede". O SOLPI preenche automaticamente IP, Gateway e DNS.
2.  **Configuração de Precisão**: O usuário escolhe entre "Scan Rápido" (Ping/ARP) ou "Deep Scan" (SNMP/WMI).
3.  **Execução Visual**: Uma barra de progresso em gradiente percorre o topo enquanto os cards de métricas "pulsa" a cada novo dispositivo encontrado.
4.  **Ação Imediata**: O usuário clica em um dispositivo na tabela; a Sidebar lateral desliza mostrando a topologia daquele item e sugere: "Deseja sincronizar com o Zabbix?".
5.  **Finalização**: Botão flutuante "Construir Digital Twin" consolida todos os dados no Infrastructure Graph.

## 4. Arquitetura de Componentes UI (Frontend)
- **`Component.MetricCard`**: Animado com counter-up para números.
- **`Component.DiscoveryTable`**: Baseada em DataTables.js com filtros avançados por classe e confiança.
- **`Component.TopologyMap`**: Instância dedicada de Vis.js otimizada para a visualização de vizinhos.
- **`Component.ConfidenceShield`**: Ícones coloridos (Verde/Amarelo/Vermelho) representando a fonte da verdade.

## 5. Estratégia de Dados e API
- **SSE (Server-Sent Events)**: Para atualizar a tabela e o progresso em tempo real sem refresh.
- **`DiscoveryOrchestrator`**: Service PHP que gerencia a fila de threads de varredura.
- **`ConfidenceScoringLogic`**: Algoritmo que cruza dados (ex: se SNMP e Zabbix batem, Confiança = 100%).

## 6. Roadmap de Implementação da Interface
1.  **Módulo UI.1**: Implementação do Dashboard Superior e Sidebar.
2.  **Módulo UI.2**: Criação do formulário de configuração "Smart Discovery".
3.  **Módulo UI.3**: Desenvolvimento da Tabela Dinâmica com exportadores.
4.  **Módulo UI.4**: Integração do Mapa de Topologia em tempo real.
5.  **Módulo UI.5**: Camada de IA e Comandos Rápidos.

## 7. Paleta de Cores (Dark/Light)
- **Primary**: Indigo (#4f46e5) - Autoridade e Inteligência.
- **Success**: Emerald (#10b981) - Ativos Saudáveis.
- **Warning**: Amber (#f59e0b) - Mudanças Detectadas.
- **Danger**: Rose (#ef4444) - Falhas de Conexão.
