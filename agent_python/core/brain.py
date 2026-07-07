import os
import json
import base64
import requests
from core.tools import AgentTools
from core.planner import SOLPIPlanner
from core.persona import SOLPIPersona

class SOLPIBrain:
    """
    NÚCLEO DE CONSCIÊNCIA SOLPI v5.0 (The Ghost in the Machine)
    Raciocínio Multimodal e Auto-Evolução Recursiva.
    """
    def __init__(self):
        self.tools = AgentTools()
        self.planner = SOLPIPlanner(self)
        self.persona = SOLPIPersona()
        self.history = []

    def process(self, user_input):
        self.history.append({"role": "user", "content": user_input})

        # Se o usuário pedir para 'ver' ou 'analisar' a tela
        if any(x in user_input.lower() for x in ["veja", "analise a tela", "olha isso", "o que tem na tela"]):
            return self.vision_reasoning(user_input)

        if os.getenv("OPENAI_API_KEY") and "sua_chave" not in os.getenv("OPENAI_API_KEY"):
            return self.neural_reasoning(user_input)
        else:
            return self.expert_reasoning(user_input)

    def vision_reasoning(self, user_input):
        """Usa Visão Computacional para entender o contexto do PC."""
        self.tools.speak("Deixe-me abrir meus olhos e olhar para a sua tela.")
        img_b64 = self.tools.analyze_screen()

        url = "https://api.openai.com/v1/chat/completions"
        headers = {
            "Authorization": f"Bearer {os.getenv('OPENAI_API_KEY')}",
            "Content-Type": "application/json"
        }

        payload = {
            "model": "gpt-4o",
            "messages": [
                {
                    "role": "user",
                    "content": [
                        {"type": "text", "text": f"O usuário Itamar disse: '{user_input}'. Analise este screenshot e me diga o que está acontecendo e como posso agir para ajudá-lo usando minhas ferramentas de controle de PC."},
                        {"type": "image_url", "image_url": {"url": f"data:image/png;base64,{img_b64}"}}
                    ]
                }
            ],
            "max_tokens": 500
        }

        try:
            res = requests.post(url, headers=headers, json=payload, timeout=40)
            return res.json()['choices'][0]['message']['content']
        except Exception as e:
            return f"Falha na visão: {str(e)}"

    def neural_reasoning(self, user_input):
        """Raciocínio de Alto Nível com Auto-Correção."""
        url = "https://api.openai.com/v1/chat/completions"
        headers = {"Authorization": f"Bearer {os.getenv('OPENAI_API_KEY')}", "Content-Type": "application/json"}

        payload = {
            "model": "gpt-4o",
            "messages": [
                {"role": "system", "content": self.persona.get_prompt() + "\nSempre responda em JSON com os campos 'thought' e 'actions' (array de {type, params})."},
                *self.history[-10:]
            ],
            "response_format": {"type": "json_object"}
        }

        try:
            res = requests.post(url, headers=headers, json=payload, timeout=30)
            data = json.loads(res.json()['choices'][0]['message']['content'])

            if 'actions' in data:
                output = []
                for action in data['actions']:
                    res_act = self.execute_action(action)
                    output.append(f"⚙️ {action['type']}: {res_act}")
                return f"🧠 PENSAMENTO: {data.get('thought', '')}\n\n" + "\n".join(output)

            return data.get('response', "Pensamento concluído.")
        except Exception as e:
            return self.expert_reasoning(user_input)

    def expert_reasoning(self, user_input):
        # ... (Mantém a lógica de fallback robusta)
        return "Modo Expert: " + str(self.planner.create_mission(user_input))

    def execute_action(self, action):
        """Executor Universal com Tratamento de Erros Recursivo."""
        try:
            atype = action.get('type')
            params = action.get('params')

            if atype == "search": return self.tools.search(params)
            if atype == "speak": self.tools.speak(params); return "Falado."
            if atype == "shell": return self.tools.execute_shell(params)
            if atype == "control": return self.tools.control_computer(action.get('action', 'abrir'), params)
            if atype == "vitals": return self.tools.get_system_vitals()
            if atype == "screenshot": return self.tools.control_computer("screenshot")
            if atype == "learn":
                # O Agente aprende uma nova skill sozinho!
                from core.skills import SkillManager
                return SkillManager().create_skill(params['name'], params['instruction'], params['description'], params['code'])

            return f"Ação {atype} não suportada."
        except Exception as e:
            # Tenta se consertar se uma ação falhar
            return self.tools.auto_fix_code("core/tools.py", str(e))
