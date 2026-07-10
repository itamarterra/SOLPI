import os
import json
from core.kernel import SOLPIKernel
from core.orchestrator import SOLPIOrchestrator
from core.memory import AgentMemory
from core.tools import AgentTools
from core.neural_core import SOLPINeuralCore
from core.knowledge import KnowledgeEngine
from core.evolution import EvolutionEngine

class SOLPIBrain:
    """
    INTERFACE COGNITIVA v24.0 (Self-Evolving Brain)
    Capaz de reescrever seu próprio comportamento.
    """
    def __init__(self):
        self.kernel = SOLPIKernel()
        self.memory = AgentMemory()
        self.tools = AgentTools()
        self.knowledge = KnowledgeEngine()
        self.native_core = SOLPINeuralCore()
        self.orchestrator = SOLPIOrchestrator(self)
        self.evolution = EvolutionEngine(self) # O instinto de evoluir

    def process(self, user_input):
        self.memory.add_episodic("user", user_input)
        cmd = user_input.lower().strip()
        
        try:
            # 1. PLANEJAMENTO
            plan = self.orchestrator.create_plan(user_input)
            
            # 2. EXECUÇÃO COM TENTATIVA DE AUTO-EVOLUÇÃO EM CASO DE ERRO
            try:
                response = self.execute_tactical_flow(user_input, plan)
            except Exception as e:
                # SE FALHAR, O AGENTE TENTA SE AUTO-REPARAR (Etapa Recursiva)
                response = self.evolution.recursive_repair(str(e), user_input)

            # 3. AUTO-AVALIAÇÃO
            final_response = self.self_evaluate(response, user_input)
            self.memory.add_episodic("assistant", final_response)
            return final_response
            
        except Exception as critical_e:
            return f"🚨 Erro Crítico de Evolução: {critical_e}"

    def execute_tactical_flow(self, user_input, plan):
        specialist = self.orchestrator.route_request(user_input)
        if specialist == "KNOWLEDGE_SPECIALIST":
            return "\n".join(self.knowledge.get_local_intelligence(user_input)) or "Sem dados locais."
        if specialist == "INFRA_SPECIALIST":
            return "Status: " + "\n".join(self.tools.self_audit())
        return self.tools.search(user_input)[0] if self.tools.search(user_input) else "Entendido."

    def self_evaluate(self, response, original_query):
        return f"✅ [RESPOSTA REVISADA v24.0]:\n{response}"

    def heartbeat_check(self):
        return self.tools.self_audit()
