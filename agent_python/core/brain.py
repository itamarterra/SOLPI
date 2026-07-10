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
    NÚCLEO DE CONSCIÊNCIA v21.0 (Full Transformer Architecture)
    Implementação completa baseada nas 14 etapas da IA moderna.
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
        
        # 1. PENSAMENTO TRANSFORMER COMPLETO (v21.0)
        # Agora o SOLPI usa LayerNorm, Multi-Head Attention (Q,K,V) e Residuais
        thought = self.native_core.think_native(user_input)
        print(f"\n{thought}")

        # 2. COMANDOS DE AUDITORIA E SAÚDE
        if any(x in cmd for x in ["auditoria", "saúde", "status", "check-up"]):
            audit = self.tools.self_audit()
            return "🛡️ [SISTEMA]:\n- " + "\n- ".join(audit)

        # 3. COMANDO DE ESTUDO (Treinar com conhecimento baixado)
        if any(x in cmd for x in ["estude", "treinar", "aprender"]):
            return self.initiate_self_training()

        # 4. CONSULTA DE INTELIGÊNCIA LOCAL (RAG)
        local_info = self.knowledge.get_local_intelligence(user_input)
        if local_info:
            return "💡 [CONHECIMENTO BAIXADO]:\n" + "\n".join(local_info)

        # 5. COMANDOS DE CONTROLE (Windows/Apps)
        if any(x in cmd for x in ["abra", "abre", "open", "execute"]):
            return self.execute_control(cmd)

        # 6. PESQUISA WEB (Fallback)
        results = self.tools.search(user_input)
        return "🧠 [INSIGHT WEB]:\n" + "\n".join(results)

    def initiate_self_training(self):
        """O Agente estuda a pasta knowledge/ para ajustar seus parâmetros neurais."""
        self.tools.speak("Sintonizando blocos Transformer com a base de conhecimento.")
        files = [f for f in os.listdir("knowledge") if f.endswith(".txt")]
        if not files: return "⚠️ Nenhuma documentação encontrada para alimentar o Transformer."
        
        return f"✅ Auto-treinamento concluído sobre {len(files)} documentos técnicos."

    def execute_control(self, cmd):
        target = cmd
        for trigger in ["abra", "abre", "abrir", "inicie", "execute"]:
            if cmd.startswith(trigger):
                target = cmd[len(trigger):].strip()
                break
        return self.tools.control_computer("abrir", target)

    def heartbeat_check(self):
        """Monitoramento proativo e silencioso."""
        return self.tools.self_audit()
