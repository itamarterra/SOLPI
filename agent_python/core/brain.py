import os
import json
import requests
import re
from core.tools import AgentTools
from core.planner import SOLPIPlanner

class SOLPIBrain:
    def __init__(self):
        self.tools = AgentTools()
        self.planner = SOLPIPlanner(self)
        self.history = []

    def process(self, user_input):
        cmd = user_input.lower().strip()
        # Remove caracteres especiais que podem vir do reconhecimento de voz
        cmd = re.sub(r'[^\w\s]', '', cmd)
        
        print(f"\n🧠 [NÚCLEO]: Processando ordem: '{cmd}'")
        
        # 1. FILTRO DE EXECUÇÃO (Prioridade Zero)
        # Se falar em abrir algo ou citar serviços conhecidos, EXECUTA EM VEZ DE PESQUISAR
        control_triggers = ["abra", "abre", "abrir", "inicie", "execute", "open", "go to", "youtube", "google", "whatsapp", "glpi"]
        
        if any(x in cmd for x in control_triggers):
            target = cmd
            for trigger in ["abra", "abre", "abrir", "inicie", "execute", "open"]:
                target = target.replace(trigger, "")
            target = target.strip()
            
            # Se o comando for apenas "YouTube", o target fica sendo "youtube"
            if not target and "youtube" in cmd: target = "youtube"
            
            self.tools.speak(f"Entendido, Itamar. Executando {target} agora.")
            return self.tools.control_computer("abrir", target)

        # 2. RESPOSTAS SOCIAIS
        if any(x in cmd for x in ["oi", "olá", "tudo bem", "como vai"]):
            return "Olá! Estou online e pronto. Quer que eu abra algum programa ou faça uma pesquisa?"

        # 3. PESQUISA (Apenas se não for um comando de ação)
        print(f"🔍 [PESQUISA]: Nenhuma ação local detectada. Buscando conhecimento sobre '{cmd}'...")
        results = self.tools.search(cmd)
        return "🧠 [INSIGHTS]:\n" + "\n".join(results)

    def execute_action(self, action):
        atype = action.get('type') or action.get('action')
        params = action.get('params') or action.get('target')
        if atype == "speak": self.tools.speak(params); return "OK"
        if atype == "control": return self.tools.control_computer("abrir", params)
        return self.tools.execute_shell(params)
