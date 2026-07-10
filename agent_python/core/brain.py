import os
from core.kernel import SOLPIKernel
from core.orchestrator import SOLPISupervisor
from core.memory import AgentMemory
from core.tools import AgentTools
from core.neural_core import SOLPINeuralCore
from core.knowledge import KnowledgeEngine

class SOLPIBrain:
    """
    INTERFACE COGNITIVA v29.0 (MoE & Multi-Agent)
    Arquitetura de Supervisor com Especialistas internos e externos.
    """
    def __init__(self):
        self.kernel = SOLPIKernel()
        self.memory = AgentMemory()
        self.tools = AgentTools()
        self.knowledge = KnowledgeEngine()
        self.native_core = SOLPINeuralCore() # Agora com MoE!
        self.supervisor = SOLPISupervisor(self) # Supervisor de Agentes

    def process(self, user_input):
        self.memory.add_episodic("user", user_input)
        
        # 1. SUPERVISÃO (Camada 10 - Orquestração)
        expert, mission_desc = self.supervisor.delegate(user_input)
        print(f"👮 [SUPERVISOR]: Delegado para {expert}. Missão: {mission_desc}")

        # 2. PENSAMENTO MOE (Camada 1 - Arquitetura Sparse MoE)
        thought = self.native_core.think_native(user_input)
        print(f"{thought}")

        # 3. EXECUÇÃO PELO ESPECIALISTA
        if expert == "INFRA_EXPERT":
            return "📡 [INFRA]: " + "\n- ".join(self.tools.self_audit())
            
        if expert == "KNOWLEDGE_EXPERT":
            return "📚 [RAG]: " + ("\n".join(self.knowledge.get_local_intelligence(user_input)) or "Sem dados locais.")

        # 4. FALLBACK (Pesquisa Externa)
        return "🧠 [GLOBAL]: " + "\n".join(self.tools.search(user_input)[:2])

    def heartbeat_check(self):
        return self.tools.self_audit()
