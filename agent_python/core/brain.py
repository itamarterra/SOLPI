import os
import json
import requests
import re
from core.tools import AgentTools
from core.planner import SOLPIPlanner
from core.persona import SOLPIPersona

class SOLPIBrain:
    def __init__(self):
        self.tools = AgentTools()
        self.planner = SOLPIPlanner(self)
        self.persona = SOLPIPersona()
        self.history = []

    def process(self, user_input):
        # Limpeza e Normalização
        cmd = user_input.lower().strip()
        print(f"\n🧠 [MONÓLOGO INTERNO]: Analisando intenção de '{cmd}'...")

        # 1. COMANDOS DE CONTROLE (ABRIR / EXECUTAR) - Verificação Flexível
        control_triggers = ["abra", "abre", "abrir", "inicie", "iniciar", "execute", "executar", "roda", "rodar", "open"]

        is_control = False
        target = cmd
        for trigger in control_triggers:
            if cmd.startswith(trigger):
                is_control = True
                target = cmd[len(trigger):].strip()
                break

        if is_control and target:
            self.tools.speak(f"Comando de sistema. Iniciando {target}.")
            return self.tools.control_computer("abrir", target)

        # 2. VISÃO
        if any(x in cmd for x in ["veja", "olha", "tela", "screenshot"]):
            return self.vision_reasoning(user_input)

        # 3. INTELIGÊNCIA NEURAL (OpenAI)
        api_key = os.getenv("OPENAI_API_KEY")
        if api_key and "sua_chave" not in api_key:
            return self.neural_reasoning(user_input)

        # 4. INTELIGÊNCIA DE BUSCA (Google)
        return self.search_driven_intelligence(cmd)

    def search_driven_intelligence(self, cmd):
        if any(x in cmd for x in ["oi", "olá", "tudo bem", "como vai"]):
            return "Olá Itamar! Estou online e pronto para agir. O que vamos executar?"

        print(f"🔍 [AUTONOMIA]: Realizando pesquisa profunda sobre '{cmd}'...")
        self.tools.speak(f"Vou pesquisar sobre {cmd}.")
        results = self.tools.search(cmd)
        return "🧠 [INSIGHTS WEB]:\n" + "\n".join(results)

    def neural_reasoning(self, user_input):
        return "Processamento Neural Indisponível."

    def execute_action(self, action):
        atype = action.get('type') or action.get('action')
        params = action.get('params') or action.get('target')
        if atype == "search": return self.tools.search(params)
        if atype == "speak": self.tools.speak(params); return "Falado."
        if atype == "control": return self.tools.control_computer("abrir", params)
        return f"Ação {atype} não suportada."
