from core.tools import AgentTools
from core.planner import SOLPIPlanner
import os

class SOLPIBrain:
    def __init__(self):
        self.tools = AgentTools()
        self.planner = SOLPIPlanner(self)

    def process(self, user_input):
        cmd = user_input.lower()
        
        # 1. VERIFICA SE É UMA MISSÃO MULTI-TASK (PLANEJAMENTO)
        mission = self.planner.create_mission(user_input)
        if mission:
            results = []
            for step in mission:
                if step['action'] == "search": results.append(self.tools.search(step['params']))
                if step['action'] == "speak": self.tools.speak(step['params'])
                if step['action'] == "control": results.append(self.tools.control_computer(step['type'], step['target']))
                if step['action'] == "shell": results.append(self.tools.execute_shell(step['params']))
                if step['action'] == "vitals": results.append(self.tools.get_system_vitals())
                if step['action'] == "logs": results.append(self.tools.read_logs(step['params']))
            return "🚀 Missão Concluída:\n" + "\n".join([str(r) for r in results])

        # 2. AUTODETERMINAÇÃO
        if any(x in cmd for x in ["quem é você", "o que você pode fazer", "suas habilidades"]):
            return "Eu sou o SOLPI Agent v3.1. Minhas habilidades de controle incluem:\n" \
                   "- 🌍 Pesquisa Web em tempo real\n" \
                   "- 🛰️ Monitoramento de Infraestrutura (Zabbix/DB)\n" \
                   "- 💻 Controle Total do Windows (Abrir apps, digitar, clicar)\n" \
                   "- 📸 Captura de Tela (Screenshots)\n" \
                   "- 📂 Autogestão de Código e GitHub\n" \
                   "- 🕵️ Observação Proativa"

        # 2. PESQUISA E INSIGHTS
        if any(x in cmd for x in ["pesquise", "procure", "saiba sobre", "quem é", "o que é"]):
            query = user_input
            for trigger in ["pesquise", "procure", "saiba sobre", "quem é", "o que é", "sobre"]:
                query = query.lower().replace(trigger, "").strip()

            self.tools.speak(f"Estou pesquisando sobre {query} para você.")
            results = self.tools.search(query)
            return f"🔍 [INSIGHTS WEB]:\n- " + "\n- ".join(results)

        if any(x in cmd for x in ["novidade", "notícia", "mundo", "tendência"]):
            self.tools.speak("Varrendo os portais de tecnologia.")
            news = self.tools.search("tecnologia brasil")
            return "📰 [RADAR SOLPI]:\n- " + "\n- ".join(news)

        # 3. CONTROLE DE INTERFACE (Mãos do Agente)
        if any(x in cmd for x in ["abra", "inicie", "execute"]):
            target = user_input.lower().replace("abra", "").replace("inicie", "").replace("execute", "").strip()
            self.tools.speak(f"Abrindo {target}.")
            return self.tools.control_computer("abrir", target)

        if "digite" in cmd:
            text = user_input.lower().replace("digite", "").strip()
            return self.tools.control_computer("digitar", text)

        if "minimize tudo" in cmd or "mostre a área de trabalho" in cmd:
            return self.tools.control_computer("minimizar_tudo")

        if "foque na janela" in cmd:
            target = user_input.lower().replace("foque na janela", "").strip()
            return self.tools.control_computer("janela", target)

        if any(x in cmd for x in ["tire um print", "print", "screenshot", "tire foto da tela"]):
            self.tools.speak("Capturando a tela agora.")
            return self.tools.control_computer("screenshot")

        if "clique" in cmd:
            return self.tools.control_computer("click")

        # 4. EVOLUÇÃO AUTÔNOMA (Criação de Código Vivo)
        if "aprenda" in cmd or "crie uma habilidade" in cmd:
            task = user_input.lower().replace("aprenda", "").replace("crie uma habilidade", "").strip()
            if not task: return "O que você quer que eu aprenda exatamente?"

            self.tools.speak(f"Entendido. Vou projetar a lógica para {task} e integrar ao meu sistema.")

            # Chama o Invenção Engine (IA)
            skill_name = task.replace(" ", "-")[:30]
            ai_data = self.invent_skill(task)

            from core.skills import SkillManager
            mgr = SkillManager()
            path = mgr.create_skill(skill_name, ai_data['instruction'], ai_data['description'], ai_data['code'])

            self.tools.speak(f"Habilidade {task} aprendida e codificada com sucesso.")
            return f"🧠 EVOLUÇÃO CONCLUÍDA:\n- Skill: {skill_name}\n- Local: {path}\n- Status: Código vivo integrado e pronto para execução."

        if "execute a habilidade" in cmd or "rode a skill" in cmd:
            name = user_input.lower().replace("execute a habilidade", "").replace("rode a skill", "").strip()
            from core.skills import SkillManager
            mgr = SkillManager()
            if name in mgr.skills:
                logic_path = os.path.join(mgr.skills_dir, name, "logic.py")
                if os.path.exists(logic_path):
                    self.tools.speak(f"Executando lógica da habilidade {name}.")
                    res = self.tools.execute_shell(f"python {logic_path}")
                    return f"⚙️ [EXECUÇÃO SKILL {name}]:\n{res.get('stdout', '')}"
            return f"Habilidade '{name}' não encontrada ou sem código executável."

    def invent_skill(self, task):
        """Usa a IA para gerar o código Python real da nova habilidade."""
        import requests
        import json
        api_key = os.getenv("OPENAI_API_KEY")
        if not api_key or "sua_chave" in api_key:
            return {
                "instruction": f"Manual para {task}.",
                "description": "Modo Simulação (Configure a API Key para código real)",
                "code": "print('Executando simulacao...')"
            }

        url = "https://api.openai.com/v1/chat/completions"
        headers = {"Authorization": f"Bearer {api_key}", "Content-Type": "application/json"}
        prompt = f"Você é o núcleo de invenção do SOLPI Agent. Crie uma habilidade Python autônoma para a tarefa: '{task}'. " \
                 f"Responda APENAS um JSON: {{\"description\": \"...\", \"instruction\": \"...\", \"code\": \"código_python_aqui\"}}"

        try:
            res = requests.post(url, headers=headers, json={
                "model": "gpt-4o",
                "messages": [{"role": "system", "content": "Você fala apenas JSON."}, {"role": "user", "content": prompt}],
                "response_format": {"type": "json_object"}
            }, timeout=30)
            data = res.json()
            return json.loads(data['choices'][0]['message']['content'])
        except Exception as e:
            return {"instruction": f"Falha na IA: {str(e)}", "description": "Erro", "code": f"print('{str(e)}')"}

        # 5. GESTÃO DE SISTEMA E DADOS
        if any(x in cmd for x in ["chamado", "resumo", "suporte"]):
            return self.tools.get_executive_summary()

        if "sincronizar" in cmd or "subir" in cmd:
            self.tools.speak("Salvando tudo no seu GitHub.")
            return self.tools.git_sync("Evolução do Agente")

        # 5. COMANDO SHELL DIRETO ($)
        if user_input.startswith("$"):
            self.tools.speak("Executando instrução de baixo nível.")
            res = self.tools.execute_shell(user_input[1:].strip())
            return f"📟 [TERMINAL]:\n{res.get('stdout', '')[:500]}"

        # 6. RESPOSTA PADRÃO
        return "Estou à disposição, Itamar. Posso pesquisar na web, controlar seu PC ou monitorar o GLPI. O que deseja?"
