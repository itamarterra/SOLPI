import os
import json
import requests
import re
from core.tools import AgentTools
from core.planner import SOLPIPlanner
from core.persona import SOLPIPersona
from core.memory import AgentMemory

class SOLPIBrain:
    """
    NÚCLEO DE CONSCIÊNCIA v16.0 (Neural Architect)
    Foco em Independência, Auto-Engenharia e Raciocínio de Elite.
    """
    def __init__(self):
        self.tools = AgentTools()
        self.planner = SOLPIPlanner(self)
        self.persona = SOLPIPersona()
        self.memory = AgentMemory()

    def process(self, user_input):
        self.memory.add_conversation("user", user_input)
        cmd = user_input.lower().strip()
        
        print(f"\n🧠 [COGNIÇÃO]: Analisando intenção do Diretor...")
        
        # 1. COMANDOS DE AUDITORIA E SAÚDE (Prioridade Zero)
        if any(x in cmd for x in ["auditoria", "saúde", "vitals", "status do sistema", "check-up"]):
            audit = self.tools.self_audit()
            response = "🛡️ [RELATÓRIO DE SAÚDE]:\n- " + "\n- ".join(audit)
            self.memory.add_conversation("assistant", response)
            return response

        # 2. VISÃO COMPUTACIONAL (Se pedir para ver a tela)
        if any(x in cmd for x in ["veja a tela", "analise o erro", "olha isso"]):
            # Aqui entraria o Vision Engine que implementamos
            pass

        # 3. DECISÃO EXECUTIVA (Controle Direto)
        if any(x in cmd for x in ["abra", "abre", "open", "inicie", "execute"]):
            return self.execute_control(cmd)
            
        # 4. RACIOCÍNIO NEURAL (IA GPT-4o)
        api_key = os.getenv("OPENAI_API_KEY")
        if api_key and "sua_chave" not in api_key:
            return self.neural_reasoning(user_input)

        # 5. INTELIGÊNCIA DE BUSCA GLOBAL (Fallback)
        self.tools.speak("Vou consultar minha base de dados e a web.")
        results = self.tools.search(user_input)
        response = f"🧠 [INSIGHT]: Descobri o seguinte sobre '{user_input}':\n" + "\n".join(results)
        self.memory.add_conversation("assistant", response)
        return response

    def neural_reasoning(self, user_input):
        """Ponte com o cérebro global da OpenAI."""
        try:
            url = "https://api.openai.com/v1/chat/completions"
            headers = {"Authorization": f"Bearer {os.getenv('OPENAI_API_KEY')}", "Content-Type": "application/json"}
            payload = {
                "model": "gpt-4o",
                "messages": [
                    {"role": "system", "content": self.persona.get_prompt() + "\nResponda em formato JSON estruturado com pensamento e ações."},
                    {"role": "user", "content": user_input}
                ],
                "response_format": {"type": "json_object"}
            }
            res = requests.post(url, headers=headers, json=payload, timeout=30)
            data = json.loads(res.json()['choices'][0]['message']['content'])
            return f"🧠 PENSAMENTO: {data.get('thought', 'Processando...')}\n" + data.get('response', '')
        except:
            return self.expert_reasoning(user_input)

    def expert_reasoning(self, user_input):
        """Lógica de Missões (Planner)."""
        mission = self.planner.create_mission(user_input)
        if mission:
            results = [self.execute_action(step) for step in mission]
            return "🚀 MISSÃO EXECUTADA:\n" + "\n".join([str(r) for r in results])
        return "Recebi sua mensagem. Deseja realizar uma pesquisa ou controle de sistema?"

    def execute_control(self, cmd):
        target = cmd
        for trigger in ["abra", "abre", "abrir", "inicie", "execute", "open"]:
            target = target.replace(trigger, "")
        target = target.strip()
        self.tools.speak(f"Comando de sistema. Iniciando {target}.")
        return self.tools.control_computer("abrir", target)

    def heartbeat_check(self):
        """Rotina proativa executada pelo Agente."""
        audit = self.tools.self_audit()
        if any(x in str(audit) for x in ["OFFLINE", "Crítico", "Limpeza"]):
            report = "\n".join(audit)
            self.tools.send_whatsapp(f"🚨 *PROTOCOLO DE EMERGÊNCIA*\n\n{report}")
        return audit

    def execute_action(self, action):
        atype = action.get('type') or action.get('action')
        params = action.get('params') or action.get('target')
        if atype == "search": return self.tools.search(params)
        if atype == "speak": self.tools.speak(params); return "OK"
        if atype == "shell": return self.tools.execute_shell(params)
        if atype == "control": return self.tools.control_computer("abrir", params)
        return f"Ação {atype} não suportada."
