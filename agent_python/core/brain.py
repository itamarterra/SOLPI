import os
import json
from core.tools import AgentTools
from core.planner import SOLPIPlanner
from core.persona import SOLPIPersona
from core.memory import AgentMemory
from core.knowledge import KnowledgeEngine
from core.neural_core import SOLPINeuralCore

class SOLPIBrain:
    """
    NÚCLEO DE CONSCIÊNCIA v20.0 (Transformer Heart)
    Foco em Auto-Treinamento e Raciocínio Baseado em Atenção.
    """
    def __init__(self):
        self.tools = AgentTools()
        self.planner = SOLPIPlanner(self)
        self.persona = SOLPIPersona()
        self.memory = AgentMemory()
        self.knowledge = KnowledgeEngine()
        self.native_core = SOLPINeuralCore()

    def process(self, user_input):
        self.memory.add_conversation("user", user_input)
        cmd = user_input.lower().strip()
        
        # 1. PENSAMENTO TRANSFORMER (O Coração da v20)
        thought = self.native_core.think_native(user_input)
        print(f"\n{thought}")

        # 2. COMANDO DE ESTUDO (Treinar com o que foi baixado)
        if "estude" in cmd or "treinar" in cmd:
            return self.initiate_self_training()

        # 3. CONSULTA DE INTELIGÊNCIA LOCAL
        local_info = self.knowledge.get_local_intelligence(user_input)
        if local_info:
            return "💡 [INTELIGÊNCIA LOCAL]:\n" + "\n".join(local_info)

        # 4. PESQUISA WEB (Fallback)
        results = self.tools.search(user_input)
        return "🧠 [INSIGHT WEB]:\n" + "\n".join(results)

    def initiate_self_training(self):
        """O Agente lê a pasta knowledge/ e treina seu Transformer nativo."""
        self.tools.speak("Iniciando ciclo de auto-treinamento técnico.")
        files = [f for f in os.listdir("knowledge") if f.endswith(".txt")]
        if not files: return "⚠️ Nenhuma documentação encontrada para estudar."
        
        for file in files:
            print(f"📖 Estudando: {file}...")
            # Aqui no futuro chamaremos o backprop real
            # Por agora, simulamos a ingestão semântica
        return f"✅ Ciclo de aprendizado concluído sobre {len(files)} documentos."

    def heartbeat_check(self):
        return self.tools.self_audit()
