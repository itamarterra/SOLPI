import json
import os
from datetime import datetime

class AgentMemory:
    """
    MEMÓRIA SEMÂNTICA v2.0
    Armazena conversas, fatos aprendidos e preferências do Diretor.
    """
    def __init__(self, memory_file="memory.json"):
        self.memory_file = memory_file
        self.data = {"conversations": [], "facts": {}, "preferences": {}}
        self.load()

    def load(self):
        if os.path.exists(self.memory_file):
            try:
                with open(self.memory_file, 'r', encoding='utf-8') as f:
                    self.data = json.load(f)
            except: pass

    def save(self):
        with open(self.memory_file, 'w', encoding='utf-8') as f:
            json.dump(self.data, f, indent=4, ensure_ascii=False)

    def add_conversation(self, role, content):
        self.data["conversations"].append({
            "timestamp": datetime.now().isoformat(),
            "role": role, 
            "content": content
        })
        if len(self.data["conversations"]) > 100:
            self.data["conversations"] = self.data["conversations"][-100:]
        self.save()

    def learn_fact(self, key, value):
        self.data["facts"][key] = {
            "value": value,
            "learned_at": datetime.now().isoformat()
        }
        self.save()

    def get_context(self):
        """Retorna os últimos fatos e conversas para o cérebro."""
        return {
            "recent_history": self.data["conversations"][-5:],
            "important_facts": self.data["facts"]
        }
