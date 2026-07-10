import os
import json
from core.tools import AgentTools
from core.planner import SOLPIPlanner
from core.persona import SOLPIPersona
from core.memory import AgentMemory
from core.knowledge import KnowledgeEngine

class SOLPIBrain:
    """
    NÚCLEO DE CONSCIÊNCIA v18.0 (Knowledge Powered)
    Integração de base de conhecimento local e download de inteligência.
    """
    def __init__(self):
        self.tools = AgentTools()
        self.planner = SOLPIPlanner(self)
        self.persona = SOLPIPersona()
        self.memory = AgentMemory()
        self.knowledge = KnowledgeEngine()

    def process(self, user_input):
        self.memory.add_conversation("user", user_input)
        cmd = user_input.lower().strip()
        
        print(f"\n🧠 [COGNIÇÃO]: Consultando base de conhecimento...")

        # 1. COMANDO DE DOWNLOAD DE INTELIGÊNCIA
        if "baixe" in cmd and "site" in cmd:
            # Ex: "Baixe o site https://exemplo.com com o nome manual-zabbix"
            import re
            url = re.search(r'https?://\S+', user_input)
            name = re.search(r'nome\s+(\S+)', user_input)
            if url and name:
                return self.knowledge.download_site(url.group(), name.group(1))
            return "❌ Formato: 'Baixe o site [URL] com o nome [NOME]'"

        # 2. CONSULTA LOCAL (Saber o que já foi baixado)
        local_info = self.knowledge.get_local_intelligence(user_input)
        if local_info:
            response = "💡 [INTELIGÊNCIA LOCAL]: Encontrei isso nos meus arquivos baixados:\n" + "\n".join(local_info)
            self.memory.add_conversation("assistant", response)
            return response

        # 3. COMANDOS DE CONTROLE (Prioridade Alta)
        control_triggers = ["abra", "abre", "abrir", "inicie", "execute", "youtube"]
        if any(cmd.startswith(x) for x in control_triggers):
            return self.execute_control(cmd)

        # 4. PESQUISA WEB (Se não souber localmente)
        results = self.tools.search(user_input)
        return "🧠 [INSIGHT WEB]:\n" + "\n".join(results)

    def execute_control(self, cmd):
        target = cmd
        for trigger in ["abra", "abre", "abrir", "inicie", "execute"]:
            if cmd.startswith(trigger):
                target = cmd[len(trigger):].strip()
                break
        return self.tools.control_computer("abrir", target)

    def heartbeat_check(self):
        return self.tools.self_audit()
