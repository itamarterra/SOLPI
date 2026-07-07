import os
import json
import base64
import requests
from core.tools import AgentTools
from core.planner import SOLPIPlanner
from core.persona import SOLPIPersona

class SOLPIBrain:
    """
    NÚCLEO DE CONSCIÊNCIA SOLPI v7.3 (Precision & Intelligent Search)
    """
    def __init__(self):
        self.tools = AgentTools()
        self.planner = SOLPIPlanner(self)
        self.persona = SOLPIPersona()
        self.history = []

    def process(self, user_input):
        # Normalização e Limpeza
        import re
        clean_input = re.sub(r'[^\w\s\.]', '', user_input)
        cmd = clean_input.lower().strip()

        print(f"\n🧠 [MONÓLOGO INTERNO]: Analisando intenção de '{cmd}'...")
        self.history.append({"role": "user", "content": user_input})

        # 1. VISÃO
        if any(x in cmd for x in ["veja", "olha", "tela", "screenshot"]):
            return self.vision_reasoning(user_input)

        # 2. COMANDOS DE CONTROLE (ABRIR / EXECUTAR)
        control_triggers = ["abra", "abrir", "inicie", "iniciar", "execute", "executar", "open"]
        if any(cmd.startswith(x) for x in control_triggers):
            target = cmd
            for trigger in control_triggers:
                target = target.replace(trigger, "")
            target = target.strip()

            self.tools.speak(f"Entendido. Iniciando {target}.")
            return self.tools.control_computer("abrir", target)

        # 3. INTELIGÊNCIA NEURAL (OpenAI)
        api_key = os.getenv("OPENAI_API_KEY")
        if api_key and "sua_chave" not in api_key:
            return self.neural_reasoning(user_input)

        # 4. INTELIGÊNCIA DE BUSCA GLOBAL (Google/YouTube)
        return self.search_driven_intelligence(cmd)

    def search_driven_intelligence(self, cmd):
        # Respostas curtas
        if any(x in cmd for x in ["oi", "olá", "tudo bem", "como vai"]):
            return "Olá Itamar! Estou online. O que vamos fazer hoje?"

        # Fallback de Pesquisa
        print(f"🔍 [AUTONOMIA]: Vou pesquisar sobre '{cmd}' para você.")
        self.tools.speak(f"Vou buscar informações sobre {cmd}.")
        results = self.tools.search(cmd)
        return "🧠 [INSIGHTS WEB]:\n" + "\n".join(results)

    def neural_reasoning(self, user_input):
        # ... (Mantém a lógica de IA se houver chave)
        return "Processamento Neural Indisponível."

    def execute_action(self, action):
        atype = action.get('type') or action.get('action')
        params = action.get('params') or action.get('target')
        if atype == "search": return self.tools.search(params)
        if atype == "speak": self.tools.speak(params); return "Falado."
        if atype == "shell": return self.tools.execute_shell(params)
        if atype == "control": return self.tools.control_computer("abrir", params)
        return f"Ação {atype} não suportada."
