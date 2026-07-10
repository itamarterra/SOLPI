class SOLPIOrchestrator:
    """
    ORQUESTRADOR v1.0
    Decide qual ferramenta e especialista usar para cada intenção.
    """
    def __init__(self, brain):
        self.brain = brain
        self.kernel = brain.kernel

    def route_request(self, user_input):
        cmd = user_input.lower()
        self.kernel.log_event("ORCHESTRATOR", f"Roteando pedido: {user_input[:30]}...")

        # 1. Decisão de Memória Corporativa (RAG - Camada 3)
        if any(x in cmd for x in ["como", "procedimento", "manual", "documento"]):
            return "KNOWLEDGE_SPECIALIST"

        # 2. Decisão de Ação em Infraestrutura (Camada 6)
        if any(x in cmd for x in ["servidor", "banco", "glpi", "zabbix", "reinicie"]):
            return "INFRA_SPECIALIST"

        # 3. Decisão de Desenvolvimento (Código)
        if any(x in cmd for x in ["python", "php", "corrija", "código"]):
            return "DEV_SPECIALIST"

        return "GENERAL_REASONER"
