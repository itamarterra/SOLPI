import os
from core.kernel import SOLPIKernel
from core.orchestrator import SOLPIOrchestrator
from core.memory import AgentMemory
from core.tools import AgentTools
from core.neural_core import SOLPINeuralCore
from core.knowledge import KnowledgeEngine

class SOLPIBrain:
    """
    INTERFACE COGNITIVA v23.0 (Thinking & Review Loop)
    Implementa: Planejamento, Ação, Avaliação e Refinamento.
    """
    def __init__(self):
        self.kernel = SOLPIKernel()
        self.memory = AgentMemory()
        self.tools = AgentTools()
        self.knowledge = KnowledgeEngine()
        self.native_core = SOLPINeuralCore()
        self.orchestrator = SOLPIOrchestrator(self)

    def process(self, user_input):
        self.memory.add_episodic("user", user_input)
        
        # 1. PLANEJAMENTO (Etapa 5)
        plan = self.orchestrator.create_plan(user_input)
        print(f"📋 [PLANO]: {plan[0]['desc']}")

        # 2. EXECUÇÃO TÁTICA (Etapa 7)
        raw_response = self.execute_tactical_flow(user_input, plan)

        # 3. AUTO-AVALIAÇÃO (Etapa 6)
        final_response = self.self_evaluate(raw_response, user_input)
        
        self.memory.add_episodic("assistant", final_response)
        return final_response

    def execute_tactical_flow(self, user_input, plan):
        """Executa as ferramentas com base no plano gerado."""
        specialist = self.orchestrator.route_request(user_input)
        
        if specialist == "KNOWLEDGE_SPECIALIST":
            return "\n".join(self.knowledge.get_local_intelligence(user_input)) or "Sem dados locais."
            
        if specialist == "INFRA_SPECIALIST":
            return "Relatório de Infra:\n" + "\n".join(self.tools.self_audit())

        return self.tools.search(user_input)[0] if self.tools.search(user_input) else "Entendido."

    def self_evaluate(self, response, original_query):
        """A IA critica a própria resposta antes de entregar (Etapa 6)."""
        self.kernel.log_event("EVALUATOR", "Iniciando revisão da resposta...")
        
        # Lógica de refinamento (Simulada)
        if len(response) < 10:
            return f"Refinei minha análise: {response}. Deseja que eu aprofunde?"
            
        # Garante que o estilo seja mantido (Etapa 10)
        return f"✅ [RESPOSTA REVISADA]:\n{response}"

    def heartbeat_check(self):
        return self.tools.self_audit()
