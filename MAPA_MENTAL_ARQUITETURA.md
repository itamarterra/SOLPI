# 🗺️ MAPA MENTAL - ARQUITETURA SOLPI v2.0

## Diagrama Geral (Microserviços Lógicos)

```mermaid
graph TB
    subgraph "ENTRADA" 
        A1["CSV/JSON/XML/PDF<br/>DOCX/Excel/HTML"]
        A2["REST/SOAP/SQL<br/>LDAP/FTP/SFTP/Email"]
        A3["WhatsApp/Evolution<br/>Webhook"]
    end
    
    subgraph "ETL (Integration Engine)"
        B1["11 Adapters<br/>(Factory pattern)"]
        B2["Validation<br/>Envelope"]
        B3["Entity Resolver<br/>(Semantic + Matchers)"]
        B4["Field Merge<br/>(Conflict Resolution)"]
        B5["Audit Log<br/>+ DLQ"]
    end
    
    subgraph "KNOWLEDGE GRAPH"
        C1["7 Parsers"]
        C2["9 Extractors"]
        C3["Graph Structure<br/>(Node + Edge)"]
        C4["Repository<br/>(DB Persist)"]
    end
    
    subgraph "AI/RAG LAYER"
        D1["Embedding Service<br/>(EmbeddingService)"]
        D2["Vector Memory<br/>(Store + Retrieval)"]
        D3["LLM Providers<br/>(6 types)"]
        D4["RAG Pipeline<br/>(Cosine Similarity)"]
    end
    
    subgraph "DOMAIN MODULES"
        E1["Dashboard<br/>(12 files)"]
        E2["Tickets<br/>+ WhatsApp<br/>(bidi)"]
        E3["Zabbix<br/>Webhook"]
        E4["Notifications<br/>Settings"]
    end
    
    subgraph "PERSISTENCE"
        F1["Assets"]
        F2["Companies"]
        F3["Contracts"]
        F4["Documents<br/>Licenses<br/>Users"]
        F5["Audit Trail"]
    end
    
    subgraph "API LAYER"
        G1["REST API<br/>(/api)"]
        G2["AJAX<br/>handlers"]
        G3["Front-end<br/>(17 pages)"]
    end

    A1 --> B1
    A2 --> B1
    A3 --> B1
    
    B1 --> B2
    B2 --> B3
    B3 --> B4
    B4 --> B5
    
    B5 --> C1
    C1 --> C2
    C2 --> C3
    C3 --> C4
    
    C3 -.-> D1
    D1 --> D2
    D2 --> D3
    D4 --> D3
    
    B4 --> F1
    B4 --> F2
    B4 --> F3
    B4 --> F4
    
    F1 --> E1
    F2 --> E1
    F1 --> E2
    F2 --> E2
    E2 --> E3
    
    E1 --> G1
    E2 --> G1
    G1 --> G3
    G2 --> G3
    
    B5 --> F5
```

---

## Stack de Tecnologia

```mermaid
graph LR
    subgraph "Backend"
        A["PHP 8.3+<br/>Strict Types"]
        B["MySQL/MariaDB<br/>(via GLPI)"]
        C["Docker<br/>Compose"]
    end
    
    subgraph "Libraries"
        D["Ramsey UUID<br/>(v6)"]
        E["PHPSpreadsheet<br/>(Excel)"]
        F["Monolog<br/>(Logging)"]
        G["Custom<br/>(No frameworks)"]
    end
    
    subgraph "Integrations"
        H["OpenAI<br/>Claude<br/>Azure<br/>Gemini<br/>Ollama"]
        I["Evolution<br/>WhatsApp"]
        J["Zabbix<br/>Webhooks"]
    end
    
    subgraph "Frontend"
        K["PHP Templates"]
        L["jQuery/AJAX"]
        M["Twig?<br/>(Check)"]
    end

    A --> B
    A --> C
    A --> D
    A --> E
    A --> F
    A --> G
    A --> H
    A --> I
    A --> J
    A --> K
    A --> L
```

---

## Fluxo de Dados (Detalhado)

```mermaid
sequenceDiagram
    participant Source as "Data Source"
    participant Adapter as "11 Adapters"
    participant Envelope as "Validation"
    participant Resolver as "Entity Resolver"
    participant Merge as "Field Merge"
    participant Queue as "Review Queue"
    participant Persist as "Domain Persist"
    participant Graph as "Knowledge Graph"
    participant AI as "AI/RAG"
    
    Source ->> Adapter: Import CSV/REST/etc
    Adapter ->> Envelope: Create ingestion_envelope
    Envelope ->> Resolver: Resolve identity<br/>(semantic + matchers)
    Resolver ->> Merge: Calculate merge_score
    
    alt score > threshold
        Merge ->> Persist: Auto-persist
    else score <= threshold
        Merge ->> Queue: Send to review
        Queue ->> Persist: Manual approval
    end
    
    Persist ->> Graph: Project nodes/edges
    Graph ->> AI: Available for RAG
    AI ->> AI: Answer questions
```

---

## Dependências Entre Módulos

```mermaid
graph TB
    IntegrationEngine["IntegrationEngine<br/>(81 files)<br/>⭐ MOTOR"]
    Knowledge["Knowledge<br/>(67 files)<br/>📚"]
    AI["AI/RAG<br/>(31 files)<br/>🤖"]
    Core["Core<br/>(69 files)<br/>🏗️"]
    Dashboard["Dashboard<br/>(12 files)"]
    Tickets["Tickets<br/>(4)"]
    WhatsApp["WhatsApp<br/>(4)"]
    Zabbix["Zabbix<br/>(5)"]
    
    IntegrationEngine --> Core
    IntegrationEngine --> Knowledge
    IntegrationEngine --> Dashboard
    
    Knowledge --> AI
    Knowledge --> Core
    
    AI --> Core
    
    Dashboard --> Core
    Dashboard -.->|Bidi| Tickets
    Tickets -.-->|Bidi| WhatsApp
    
    Zabbix --> Core
    
    style IntegrationEngine fill:#f99
    style Knowledge fill:#99f
    style AI fill:#9f9
    style Core fill:#ff9
    style Dashboard fill:#f9f
```

---

## Status de Implementação (Heat Map)

```mermaid
graph LR
    A["✅ 100%<br/>IntegrationEngine<br/>Knowledge<br/>AI/RAG<br/>Bootstrap<br/>Database"]
    
    B["🟡 60-80%<br/>Core (40% falta)<br/>Dashboard<br/>Tickets<br/>WhatsApp<br/>Zabbix"]
    
    C["⚠️ 30-50%<br/>Http (Core)<br/>Router<br/>Settings"]
    
    D["❌ 0-30%<br/>PDF/DOCX Parsers<br/>Traits++<br/>JS/CSS Assets"]
    
    E["🗑️ Deprecated<br/>_legacy/ (26 files)<br/>Remove ou Archive"]
    
    style A fill:#90EE90
    style B fill:#FFD700
    style C fill:#FFA500
    style D fill:#FF6B6B
    style E fill:#8B8B8B
```

---

## Recomendações de Cleanup

```mermaid
mindmap
  root((SOLPI<br/>v2.0))
    CRÍTICO
      Completar Core.php
        QueryBuilder::execute()
        Config validation
      Remove _legacy/
        26 arquivos deprecated
      Testes integrados
        Integration tests
        E2E tests
    IMPORTANTE
      Enriquecer generics
        Notifications domain
        Settings domain
      Traits extras
        Database trait
        Cache trait
        Validation trait
      Rate limiting
        WhatsApp adapter
    DESEJÁVEL
      JS/CSS Assets
        Minify
        Documentation
      Coverage > 50%
      API documentation
        OpenAPI/Swagger
```

