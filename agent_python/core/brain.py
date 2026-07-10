import os
from core.kernel import SOLPIKernel
from core.orchestrator import SOLPIOrchestrator
from core.memory import AgentMemory
from core.tools import AgentTools
from core.neural_core import SOLPINeuralCore
from core.knowledge import KnowledgeEngine

class SOLPIBrain:
    """
    INTERFACE OPERACIONAL v22.0 (AI Operating System)
    """
    def __init__(self):
        self.kernel = SOLPIKernel() # Kernel do Sistema
        self.memory = AgentMemory() # Memória Multicamadas
        self.tools = AgentTools()
        self.knowledge = KnowledgeEngine()
        self.native_core = SOLPINeuralCore() # Motor Transformer
        self.orchestrator = SOLPIOrchestrator(self) # Orquestrador

    def process(self, user_input):
        self.memory.add_episodic("user", user_input)
        
        # 1. ORQUESTRAÇÃO (Camada 5)
        specialist = self.orchestrator.route_request(user_input)
        self.kernel.log_event("BRAIN", f"Ativando Especialista: {specialist}")

        # 2. PENSAMENTO NATIVO (O motor Transformer v21 sempre valida o fluxo)
        thought = self.native_core.think_native(user_input)
        print(f"\n{thought}")

        # 3. FLUXO DE EXECUÇÃO POR ESPECIALISTA
        if specialist == "KNOWLEDGE_SPECIALIST":
            return self.knowledge.get_local_intelligence(user_input) or "Consultando base corporativa..."
            
        if specialist == "INFRA_SPECIALIST":
            audit = self.tools.self_audit()
            return "📡 [ESPECIALISTA INFRA]:\n- " + "\n- ".join(audit)

        # 4. PESQUISA WEB (Camada 6 - Ferramenta Externa)
        results = self.tools.search(user_input)
        return "🧠 [ORQUESTRADOR]: Busca externa concluída:\n" + "\n".join(results)

    def heartbeat_check(self):
        self.kernel.log_event("KERNEL", "Heartbeat Proativo Iniciado.")
        return self.tools.self_audit()
