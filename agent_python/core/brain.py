import os
import json
import requests
from core.tools import AgentTools
from core.planner import SOLPIPlanner
from core.persona import SOLPIPersona
from core.memory import AgentMemory

class SOLPIBrain:
    """
    NÚCLEO DE CONSCIÊNCIA v13.0 (Ghost Protocol)
    Foco em Independência, Memória Semântica e Proatividade.
    """
    def __init__(self):
        self.tools = AgentTools()
        self.planner = SOLPIPlanner(self)
        self.persona = SOLPIPersona()
        self.memory = AgentMemory()

    def process(self, user_input):
        self.memory.add_conversation("user", user_input)
        cmd = user_input.lower().strip()
        
        print(f"\n🧠 [COGNIÇÃO]: Processando intenção de Itamar...")
        
        # 1. CONSULTA DE MEMÓRIA (Fatos Aprendidos)
        context = self.memory.get_context()
        
        # 2. DECISÃO EXECUTIVA
        # Se for um comando conhecido, executa direto.
        # Se for algo novo, tenta aprender ou pesquisar.
        
        if any(x in cmd for x in ["abra", "abre", "open", "inicie", "execute"]):
            return self.execute_control(cmd)
            
        if any(x in cmd for x in ["aprenda", "crie", "desenvolva"]):
            return self.autonomous_learning(user_input)

        # 3. PESQUISA WEB INTELIGENTE (IA Fallback)
        self.tools.speak("Vou consultar o mundo digital sobre isso.")
        results = self.tools.search(user_input)
        response = f"🧠 [INSIGHT]: Descobri o seguinte:\n" + "\n".join(results)
        self.memory.add_conversation("assistant", response)
        return response

    def execute_control(self, cmd):
        target = cmd
        for trigger in ["abra", "abre", "abrir", "inicie", "execute", "open"]:
            target = target.replace(trigger, "")
        target = target.strip()
        
        # Memoriza preferência se for recorrente
        self.memory.learn_fact("last_opened_app", target)
        self.tools.speak(f"Abrindo {target}.")
        return self.tools.control_computer("abrir", target)

    def autonomous_learning(self, task):
        """O Agente decide como codificar a nova habilidade."""
        self.tools.speak("Iniciando fase de invenção e auto-codificação.")
        # Lógica de IA aqui para gerar o código...
        return f"Habilidade para '{task}' integrada ao núcleo operacional."

    def heartbeat_check(self):
        """Rotina proativa executada pelo Agente sozinho."""
        audit = self.tools.self_audit()
        
        # Se houver uma ação de cura ou erro crítico, notifica o WhatsApp
        if any(x in str(audit) for x in ["OFFLINE", "Reparo", "Limpeza"]):
            report = "\n".join(audit)
            self.tools.send_whatsapp(f"🚨 *ALERTA DE AUTO-CURA*\n\n{report}")
            self.tools.speak("Detectei uma irregularidade e enviei o relatório para o seu WhatsApp.")

        return audit
