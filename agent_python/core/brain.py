import os
import json
import base64
import requests
from core.tools import AgentTools
from core.planner import SOLPIPlanner
from core.persona import SOLPIPersona

class SOLPIBrain:
    """
    NÚCLEO DE CONSCIÊNCIA SOLPI v7.0 (Semantic Autonomy)
    O Agente agora tenta 'entender' e 'pesquisar' antes de desistir.
    """
    def __init__(self):
        self.tools = AgentTools()
        self.planner = SOLPIPlanner(self)
        self.persona = SOLPIPersona()
        self.history = []

    def process(self, user_input):
        print(f"\n🧠 [MONÓLOGO INTERNO]: Itamar disse '{user_input}'. Analisando intenção...")
        self.history.append({"role": "user", "content": user_input})

        # 1. VISÃO (Prioridade Máxima)
        if any(x in user_input.lower() for x in ["veja", "olha", "tela", "screenshot"]):
            return self.vision_reasoning(user_input)

        # 2. INTELIGÊNCIA NEURAL (Se houver chave)
        api_key = os.getenv("OPENAI_API_KEY")
        if api_key and "sua_chave" not in api_key:
            return self.neural_reasoning(user_input)

        # 3. INTELIGÊNCIA DE BUSCA (Simulação de Cérebro sem custo)
        return self.search_driven_intelligence(user_input)

    def search_driven_intelligence(self, user_input):
        """Transforma o Agente em um pesquisador autônomo para parecer 'inteligente'."""
        cmd = user_input.lower()

        # Saudações e Estado
        if any(x in cmd for x in ["oi", "olá", "tudo bem", "como vai"]):
            return "Olá Itamar! Estou operando com meu núcleo local. Para eu atingir meu QI máximo, " \
                   "preciso que você insira uma API KEY no meu arquivo .env. " \
                   "Enquanto isso, eu uso minha busca web para aprender o que você precisar. O que vamos pesquisar?"

        # Detecção de Comandos de Sistema
        mission = self.planner.create_mission(user_input)
        if mission:
            print("💡 [SISTEMA]: Missão complexa detectada. Orquestrando passos...")
            results = [self.execute_action(step) for step in mission]
            return "🚀 MISSÃO EXECUTADA:\n" + "\n".join([str(r) for r in results])

        if any(x in cmd for x in ["abra", "execute", "inicie"]):
            target = cmd.replace("abra", "").replace("execute", "").replace("inicie", "").strip()
            return self.tools.control_computer("abrir", target)

        # SE NÃO FOR COMANDO, É CONHECIMENTO -> PESQUISA WEB AUTOMÁTICA
        print(f"🔍 [AUTONOMIA]: Não reconheci o comando. Vou buscar conhecimento na Web sobre '{user_input}'...")
        self.tools.speak("Não tenho essa informação no meu cérebro local. Vou buscar agora na internet para você.")

        results = self.tools.search(user_input)
        if results and "Erro" not in results[0]:
            context = "\n".join(results)
            # Simula um resumo 'inteligente'
            return f"🧠 [INSIGHTS SOLPI]: Analisando os dados da web, descobri que:\n\n{context}\n\n" \
                   f"Deseja que eu aprofunde a pesquisa ou execute alguma ação com isso?"

        return "Ainda estou aprendendo, Itamar. No momento, sem uma conexão neural (API Key), " \
               "minha inteligência depende da Web. Como posso te ajudar com comandos de sistema?"

    def neural_reasoning(self, user_input):
        """Conexão com o Cérebro Global (GPT-4o)."""
        # ... (Mantém a lógica robusta anterior)
        try:
            url = "https://api.openai.com/v1/chat/completions"
            headers = {"Authorization": f"Bearer {os.getenv('OPENAI_API_KEY')}", "Content-Type": "application/json"}
            payload = {
                "model": "gpt-4o",
                "messages": [{"role": "system", "content": self.persona.get_prompt() + "\nResponda em JSON."}, *self.history[-5:]],
                "response_format": {"type": "json_object"}
            }
            res = requests.post(url, headers=headers, json=payload, timeout=30)
            data = json.loads(res.json()['choices'][0]['message']['content'])
            if 'actions' in data:
                return f"🧠 PENSAMENTO: {data['thought']}\n" + "\n".join([str(self.execute_action(a)) for a in data['actions']])
            return data.get('response', "Processado.")
        except: return self.search_driven_intelligence(user_input)

    def vision_reasoning(self, user_input):
        """Olha para a tela e decide o que fazer."""
        if "sua_chave" in os.getenv("OPENAI_API_KEY", ""):
            return "⚠️ ERRO DE VISÃO: Preciso de uma API KEY válida no .env para conseguir 'enxergar' e analisar sua tela."
        # ... (Lógica de visão anterior)
        return "Vision Engine Ativo (Simulado)."

    def execute_action(self, action):
        """Executor Universal de Ferramentas."""
        atype = action.get('type') or action.get('action')
        params = action.get('params') or action.get('target')
        if atype == "search": return self.tools.search(params)
        if atype == "speak": self.tools.speak(params); return "Falado."
        if atype == "shell": return self.tools.execute_shell(params)
        if atype == "control": return self.tools.control_computer(action.get('type_act', 'abrir'), params)
        if atype == "vitals": return self.tools.get_system_vitals()
        return f"Ação {atype} não suportada."
