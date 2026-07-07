from core.memory import LongTermMemory
from core.skills import SkillManager
from core.persona import SOLPIPersona
import os

class SOLPIBrain:
    def __init__(self):
        self.memory = LongTermMemory()
        self.skills = SkillManager()
        self.persona = SOLPIPersona()
        self.model = os.getenv("DEFAULT_MODEL", "gpt-4")

    def get_system_context(self):
        """Compila o prompt do sistema com as diretrizes do OpenClaw"""
        prompt = self.persona.get_prompt()
        prompt += f"\nHabilidades Ativas: {', '.join(self.skills.list_skills())}"
        return prompt

    def process(self, user_input):
        # 1. Recuperar contexto da memória (Recall)
        past_memories = self.memory.search(user_input)

        # 2. Criar contexto de IA
        system_context = self.get_system_context()

        # Simulando uma resposta integrada
        response = f"Analisando '{user_input}' com base nas diretrizes OpenClaw e {len(past_memories)} registros de memória."

        # 3. Guardar na memória de longo prazo
        self.memory.store(f"User: {user_input} | Agent: {response}", context="conversation")

        return response

    def learn_new_skill(self, name, description):
        """Implementa a capacidade do Hermes de criar ferramentas sozinho"""
        path = self.skills.create_skill(name, description)
        self.memory.store(f"Nova habilidade aprendida: {name}", context="learning")
        return f"Aprendi a nova habilidade: {name} e salvei em {path}"
