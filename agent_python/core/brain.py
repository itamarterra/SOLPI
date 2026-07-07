import os
import json
from core.tools import AgentTools
from core.planner import SOLPIPlanner
from core.persona import SOLPIPersona

class SOLPIBrain:
    """
    NÚCLEO DE CONSCIÊNCIA SOLPI v4.0 (Neural-Logic Bridge)
    Integra a lógica de raciocínio do meu cérebro para o Agente.
    """
    def __init__(self):
        self.tools = AgentTools()
        self.planner = SOLPIPlanner(self)
        self.persona = SOLPIPersona()
        self.history = []

    def process(self, user_input):
        self.history.append({"role": "user", "content": user_input})
        cmd = user_input.lower()
        
        # 1. RACIOCÍNIO DE ALTO NÍVEL (LLM ORCHESTRATOR)
        # Se houver API Key, o Agente usa o Cérebro de IA para planejar.
        # Caso contrário, usa a Lógica Especialista (Expert Rules).

        if os.getenv("OPENAI_API_KEY") and "sua_chave" not in os.getenv("OPENAI_API_KEY"):
            return self.neural_reasoning(user_input)
        else:
            return self.expert_reasoning(user_input)

    def neural_reasoning(self, user_input):
        """Transfere meu 'raciocínio' via LLM para o Agente."""
        import requests
        url = "https://api.openai.com/v1/chat/completions"
        headers = {
            "Authorization": f"Bearer {os.getenv('OPENAI_API_KEY')}",
            "Content-Type": "application/json"
        }

        # O Agente recebe minhas instruções de como agir como eu
        payload = {
            "model": "gpt-4o",
            "messages": [
                {"role": "system", "content": self.persona.get_prompt()},
                *self.history[-5:] # Contexto das últimas 5 interações
            ],
            "response_format": {"type": "json_object"}
        }

        try:
            res = requests.post(url, headers=headers, json=payload, timeout=30)
            thought = res.json()['choices'][0]['message']['content']
            data = json.loads(thought)

            # Execução de múltiplas ferramentas baseada no plano da IA
            if 'actions' in data:
                output = []
                for action in data['actions']:
                    res_act = self.execute_action(action)
                    output.append(f"✅ {action['type']}: {res_act}")
                return f"🧠 PENSAMENTO: {data.get('thought', '')}\n\n" + "\n".join(output)

            return data.get('response', "Não consegui processar o pensamento.")
        except Exception as e:
            return self.expert_reasoning(user_input) # Fallback

    def expert_reasoning(self, user_input):
        """Lógica Especialista (Minha base de conhecimento codificada)."""
        cmd = user_input.lower()

        # Missões Pré-definidas (Skills complexas)
        mission = self.planner.create_mission(user_input)
        if mission:
            results = []
            for step in mission:
                results.append(self.execute_action(step))
            return "🚀 MISSÃO EXECUTADA:\n" + "\n".join([str(r) for r in results])

        # Comandos de Controle Direto
        if any(x in cmd for x in ["abra", "inicie", "execute"]):
            target = cmd.replace("abra", "").replace("inicie", "").replace("execute", "").strip()
            return self.tools.control_computer("abrir", target)

        if "pesquise" in cmd:
            query = cmd.replace("pesquise", "").strip()
            return "\n".join(self.tools.search(query))

        if user_input.startswith("$"):
            return self.tools.execute_shell(user_input[1:].strip())

        return "Cérebro em modo de espera. Aguardando objetivo complexo."

    def execute_action(self, action):
        """Executor Universal de Ferramentas."""
        atype = action.get('type') or action.get('action')
        params = action.get('params') or action.get('target')

        if atype == "search": return self.tools.search(params)
        if atype == "speak": self.tools.speak(params); return "Falado."
        if atype == "shell": return self.tools.execute_shell(params)
        if atype == "control": return self.tools.control_computer(action.get('type_act', 'abrir'), params)
        if atype == "vitals": return self.tools.get_system_vitals()
        if atype == "screenshot": return self.tools.control_computer("screenshot")

        return f"Ação {atype} não suportada."
