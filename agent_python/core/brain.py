import os
import json
import re
from core.tools import AgentTools
from core.planner import SOLPIPlanner

class SOLPIBrain:
    def __init__(self):
        self.tools = AgentTools()
        self.planner = SOLPIPlanner(self)
        self.last_context = None # Memória de curto prazo para interligação

    def process(self, user_input):
        cmd = user_input.lower().strip()
        cmd = re.sub(r'[^\w\s]', '', cmd)
        
        print(f"\n🧠 [NÚCLEO]: Processando ordem: '{cmd}'")
        
        # 1. COMANDOS DE INTERAÇÃO COM O CONTEXTO ATUAL (Já aberto)
        interaction_triggers = ["pause", "play", "para", "continua", "desce", "sobe", "pula"]
        if any(x in cmd for x in interaction_triggers) and self.last_context:
            self.tools.speak(f"Interagindo com {self.last_context}...")
            return self.tools.interact_with_window(self.last_context, cmd)

        # 2. COMANDOS DE ABERTURA (Nova Janela)
        control_triggers = ["abra", "abre", "abrir", "inicie", "execute", "youtube", "google", "whatsapp"]
        if any(x in cmd for x in control_triggers):
            target = cmd
            for trigger in ["abra", "abre", "abrir", "inicie", "execute"]:
                target = target.replace(trigger, "")
            target = target.strip()
            if not target and "youtube" in cmd: target = "youtube"
            
            self.last_context = target # Salva o que abriu
            self.tools.speak(f"Abrindo {target}. Estou monitorando esta janela agora.")
            return self.tools.control_computer("abrir", target)

        # 3. PESQUISA (Fallback)
        self.last_context = "web"
        results = self.tools.search(cmd)
        return "🧠 [INSIGHTS]:\n" + "\n".join(results)
