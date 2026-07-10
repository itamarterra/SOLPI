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
    NÚCLEO DE CONSCIÊNCIA v19.0 (Native Core Genesis)
    Alternância entre Raciocínio Neural Nativo e Externo.
    """
    def __init__(self):
        self.tools = AgentTools()
        self.planner = SOLPIPlanner(self)
        self.persona = SOLPIPersona()
        self.memory = AgentMemory()
        self.knowledge = KnowledgeEngine()
        self.native_core = SOLPINeuralCore() # Nosso próprio motor!

    def process(self, user_input):
        self.memory.add_conversation("user", user_input)
        cmd = user_input.lower().strip()
        
        print(f"\n🧠 [COGNIÇÃO]: Ativando Núcleo Nativo...")

        # 1. PENSAMENTO NATIVO (Etapa 11)
        # O SOLPI agora processa tudo matematicamente de forma local primeiro
        thought_stats = self.native_core.think_native(user_input)
        print(thought_stats)

        # 2. MISSÃO DE PESQUISA ESTRATÉGICA
        if any(x in cmd for x in ["pesquisa por toda a internet", "varredura estratégica"]):
            return self.strategic_mission(user_input)

        # 3. CONSULTA DE CONHECIMENTO BAIXADO
        local_info = self.knowledge.get_local_intelligence(user_input)
        if local_info:
            return "💡 [INTELIGÊNCIA LOCAL]:\n" + "\n".join(local_info)

        # 4. COMANDOS DE CONTROLE
        if any(x in cmd for x in ["abra", "abre", "open", "execute"]):
            return self.execute_control(cmd)

        # 5. IA EXTERNA (Se local não for suficiente)
        return "🧠 [SOLPI]: Entendi seu comando. Processando via motor híbrido."

    def strategic_mission(self, user_input):
        targets = ["Zabbix 7.4 API", "GLPI 11 REST API", "Evolution API WhatsApp", "SNMP MIBs Library"]
        report = ["📡 [VARREDURA ESTRATÉGICA]:"]
        for t in targets:
            results = self.tools.search(t)
            report.append(f"\n📂 *Tópico: {t}*\n" + "\n".join(results[:1]))
        return "\n".join(report)

    def execute_control(self, cmd):
        target = cmd
        for trigger in ["abra", "abre", "abrir", "inicie", "execute"]:
            if cmd.startswith(trigger):
                target = cmd[len(trigger):].strip()
                break
        return self.tools.control_computer("abrir", target)
