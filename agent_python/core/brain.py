import os
import json
import base64
import requests
import re
from core.tools import AgentTools
from core.planner import SOLPIPlanner
from core.persona import SOLPIPersona

class SOLPIBrain:
    """
    NÚCLEO DE CONSCIÊNCIA SOLPI v11.0 (Autonomous Cognitive Analyzer)
    Capaz de analisar dados locais (planilhas, textos) e decidir a ação ideal.
    """
    def __init__(self):
        self.tools = AgentTools()
        self.planner = SOLPIPlanner(self)
        self.persona = SOLPIPersona()
        self.history = []
        self.last_context = None

    def process(self, user_input):
        cmd = user_input.lower().strip()
        print(f"\n🧠 [MONÓLOGO INTERNO]: Analisando intenção de '{cmd}'...")
        self.history.append({"role": "user", "content": user_input})
        
        # 1. ANÁLISE DE ARQUIVOS LOCAIS (Planilhas/Documentos)
        if any(x in cmd for x in ["analise o arquivo", "leia a planilha", "importar"]):
            return self.analyze_local_data(user_input)

        # 2. VISÃO COMPUTACIONAL
        if any(x in cmd for x in ["veja", "olha", "tela", "screenshot"]):
            return self.vision_reasoning(user_input)
            
        # 3. COMANDOS DE CONTROLE (Prioridade Alta)
        control_triggers = ["abra", "abre", "abrir", "inicie", "execute", "roda", "open", "youtube", "google", "whatsapp"]
        if any(x in cmd for x in control_triggers):
            return self.execute_control_command(cmd)

        # 4. INTELIGÊNCIA NEURAL (OpenAI)
        api_key = os.getenv("OPENAI_API_KEY")
        if api_key and "sua_chave" not in api_key:
            return self.neural_reasoning(user_input)
        
        # 5. INTELIGÊNCIA DE BUSCA GLOBAL
        return self.search_driven_intelligence(cmd)

    def analyze_local_data(self, user_input):
        """Analisa arquivos locais e decide se são Usuários, Chamados ou Ativos."""
        self.tools.speak("Iniciando análise cognitiva de dados locais.")
        # Simulação de busca de arquivo na pasta ou parâmetro
        # No mundo real, aqui usaríamos o motor do import_public.php adaptado para Python
        return "🧠 [ANALISADOR]: Identifiquei que este arquivo contém dados de INVENTÁRIO. " \
               "Deseja que eu importe estes equipamentos para o GLPI agora?"

    def execute_control_command(self, cmd):
        target = cmd
        for trigger in ["abra", "abre", "abrir", "inicie", "execute", "open"]:
            target = target.replace(trigger, "")
        target = target.strip()
        if not target and "youtube" in cmd: target = "youtube"
        
        self.last_context = target
        self.tools.speak(f"Executando {target} no sistema.")
        return self.tools.control_computer("abrir", target)

    def search_driven_intelligence(self, cmd):
        if any(x in cmd for x in ["oi", "olá", "tudo bem", "como vai"]):
            return "Olá Itamar! Estou operando com autonomia. O que vamos realizar hoje?"

        print(f"🔍 [RESEARCH]: Buscando conhecimento sobre '{cmd}'...")
        results = self.tools.search(cmd)
        return "🧠 [INSIGHTS WEB]:\n" + "\n".join(results)

    def neural_reasoning(self, user_input):
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
        self.tools.speak("Olhando para a sua tela agora.")
        return "Vision Engine em operação. (Requer API Key válida)"

    def execute_action(self, action):
        atype = action.get('type') or action.get('action')
        params = action.get('params') or action.get('target')
        if atype == "search": return self.tools.search(params)
        if atype == "speak": self.tools.speak(params); return "OK"
        if atype == "control": return self.tools.control_computer("abrir", params)
        if atype == "vitals": return self.tools.get_system_vitals()
        return f"Ação {atype} não suportada."
