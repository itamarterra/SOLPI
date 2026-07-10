class SOLPIOrchestrator:
    """
    ORQUESTRADOR COGNITIVO v2.0
    Implementa o Ciclo de Planejamento (Etapa 5).
    """
    def __init__(self, brain):
        self.brain = brain
        self.kernel = brain.kernel

    def create_plan(self, user_input):
        """Desenha os passos antes de agir."""
        cmd = user_input.lower()
        plan = []
        
        self.kernel.log_event("PLANNER", f"Criando plano para: {user_input[:30]}")

        # Exemplo de decomposição de plano complexo
        if any(x in cmd for x in ["conserta", "arruma", "problema"]):
            plan = [
                {"step": 1, "action": "AUDIT", "desc": "Verificar saúde do sistema"},
                {"step": 2, "action": "LOG_ANALYSIS", "desc": "Analisar erros de PHP"},
                {"step": 3, "action": "REPAIR", "desc": "Tentar auto-cura"}
            ]
        elif any(x in cmd for x in ["como", "explica", "manual"]):
            plan = [
                {"step": 1, "action": "RAG_SEARCH", "desc": "Consultar base local"},
                {"step": 2, "action": "WEB_RESEARCH", "desc": "Complementar com internet"}
            ]
        else:
            plan = [{"step": 1, "action": "DIRECT_RESPONSE", "desc": "Processamento imediato"}]
            
        return plan

    def route_request(self, user_input):
        """Decide o especialista baseado no plano."""
        plan = self.create_plan(user_input)
        main_action = plan[0]["action"]
        
        if main_action == "RAG_SEARCH": return "KNOWLEDGE_SPECIALIST"
        if main_action == "AUDIT": return "INFRA_SPECIALIST"

        return "GENERAL_REASONER"
