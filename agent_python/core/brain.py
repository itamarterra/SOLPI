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

        # 1. MISSÃO DE PESQUISA ESTRATÉGICA (v18.1)
        if any(x in cmd for x in ["pesquisa por toda a internet", "documentos que precisamos", "varredura estratégica"]):
            self.tools.speak("Iniciando varredura estratégica global para coletar documentação técnica do SOLPI.")
            
            targets = [
                "Zabbix 7.4 API documentation triggers manual",
                "GLPI 11 developer documentation REST API",
                "Evolution API WhatsApp interactive buttons documentation",
                "Mikrotik Cisco HP SNMP MIBs library for network mapping",
                "Digital Twin ITSM architecture whitepapers 2026"
            ]
            
            report = ["📡 [RELATÓRIO DE VARREDURA ESTRATÉGICA]:"]
            for target in targets:
                print(f"🔍 [SEARCHING]: {target}...")
                results = self.tools.search(target)
                report.append(f"\n📂 *Tópico: {target}*")
                report.append("\n".join(results[:2])) # Pega os 2 melhores de cada
                
            response = "\n".join(report)
            self.tools.speak("Varredura concluída. Encontrei as fontes necessárias para nossa evolução.")
            return response

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
