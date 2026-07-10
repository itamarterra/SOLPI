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
    NÚCLEO DE CONSCIÊNCIA v17.0 (Silent Intelligence)
    Foco em Inteligência Autônoma sem interrupções sonoras.
    """
    def __init__(self):
        self.tools = AgentTools()
        self.planner = SOLPIPlanner(self)
        self.persona = SOLPIPersona()
        self.memory = AgentMemory()

    def process(self, user_input):
        self.memory.add_conversation("user", user_input)
        cmd = user_input.lower().strip()
        
        print(f"\n🧠 [COGNIÇÃO]: Analisando intenção complexa do Diretor...")
        
        # 1. COMANDOS DE AUDITORIA E AUTO-CURA (Prioridade Zero)
        if any(x in cmd for x in ["auditoria", "saúde", "vitals", "status", "check-up", "conserta", "arruma"]):
            audit = self.tools.self_audit()
            response = "🛡️ [SISTEMA]: Auto-auditoria e cura executadas.\n- " + "\n- ".join(audit)
            self.memory.add_conversation("assistant", response)
            return response

        # 2. DECISÃO EXECUTIVA (Controle Direto com suporte a variações)
        control_triggers = ["abra", "abre", "abrir", "inicie", "execute", "open", "roda", "rodar"]
        if any(cmd.startswith(x) for x in control_triggers):
            return self.execute_control(cmd)
            
        # 3. RACIOCÍNIO NEURAL (IA GPT-4o) - O Coração da Inteligência
        api_key = os.getenv("OPENAI_API_KEY")
        if api_key and "sua_chave" not in api_key:
            return self.neural_reasoning(user_input)

        # 4. INTELIGÊNCIA DE BUSCA E PADRÕES (Fallback Inteligente)
        # Se não houver IA, tentamos entender o contexto via Planner ou Busca
        mission = self.planner.create_mission(user_input)
        if mission:
            results = [self.execute_action(step) for step in mission]
            return "🚀 MISSÃO EXECUTADA:\n" + "\n".join([str(r) for r in results])

        # Se for uma pergunta, busca na web
        if "?" in cmd or any(x in cmd for x in ["o que", "como", "quem", "por que"]):
            results = self.tools.search(user_input)
            response = f"🧠 [CONHECIMENTO]: {user_input}\n" + "\n".join(results)
            self.memory.add_conversation("assistant", response)
            return response

        return "Recebi sua mensagem. Minha inteligência v17.0 está focada em manter o sistema estável. O que deseja que eu realize agora?"

    def neural_reasoning(self, user_input):
        """Ponte com o cérebro global da OpenAI para decisões complexas."""
        try:
            url = "https://api.openai.com/v1/chat/completions"
            headers = {"Authorization": f"Bearer {os.getenv('OPENAI_API_KEY')}", "Content-Type": "application/json"}
            
            # Forçamos a IA a agir de forma mais autônoma
            payload = {
                "model": "gpt-4o",
                "messages": [
                    {"role": "system", "content": self.persona.get_prompt() + "\nVocê deve decidir quais ferramentas usar para resolver o pedido. Responda APENAS JSON: {\"thought\": \"...\", \"actions\": [{\"type\": \"shell/control/search\", \"params\": \"...\"}], \"response\": \"...\"}"},
                    {"role": "user", "content": user_input}
                ],
                "response_format": {"type": "json_object"}
            }
            res = requests.post(url, headers=headers, json=payload, timeout=30)
            data = json.loads(res.json()['choices'][0]['message']['content'])
            
            if 'actions' in data and data['actions']:
                output = []
                for action in data['actions']:
                    output.append(str(self.execute_action(action)))
                return f"🧠 [PENSAMENTO]: {data.get('thought')}\n" + "\n".join(output)
            
            return data.get('response', "Ação concluída.")
        except Exception as e:
            print(f"⚠️ Erro Neural: {e}")
            return self.expert_reasoning(user_input)

    def execute_control(self, cmd):
        target = cmd
        for trigger in ["abra", "abre", "abrir", "inicie", "execute", "open", "roda", "rodar"]:
            if cmd.startswith(trigger):
                target = cmd[len(trigger):].strip()
                break
        return self.tools.control_computer("abrir", target)

    def heartbeat_check(self):
        """Rotina proativa autônoma."""
        audit = self.tools.self_audit()
        if any(x in str(audit) for x in ["OFFLINE", "Crítico", "Limpeza"]):
            report = "\n".join(audit)
            self.tools.send_whatsapp(f"🚨 *PROTOCOLO DE EMERGÊNCIA v17.0*\n\n{report}")
        return audit

    def execute_action(self, action):
        atype = action.get('type') or action.get('action')
        params = action.get('params') or action.get('target')
        
        # Mapeamento dinâmico de ferramentas
        if atype == "search": return self.tools.search(params)
        if atype == "shell": return self.tools.execute_shell(params)
        if atype == "control": return self.tools.control_computer(action.get('action', 'abrir'), params)
        if atype == "vitals": return self.tools.get_system_vitals()
        if atype == "logs": return self.tools.read_logs(params if isinstance(params, int) else 10)
        
        return f"Ação {atype} processada silenciosamente."
