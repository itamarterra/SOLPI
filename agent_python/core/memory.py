import json
import os
from datetime import datetime

class AgentMemory:
    """
    MEMÓRIA MULTICAMADAS v3.0 (Enterprise Standard)
    """
    def __init__(self):
        self.short_term = []      # Contexto da conversa atual
        self.medium_term = {}    # Histórico de interações recentes
        self.long_term = {}      # Fatos autorizados persistentes
        self.corporate = {}      # Bases oficiais baixadas
        self.load()

    def load(self):
        if os.path.exists("long_term_memory.json"):
            with open("long_term_memory.json", "r") as f:
                self.long_term = json.load(f)

    def save(self):
        with open("long_term_memory.json", "w") as f:
            json.dump(self.long_term, f, indent=4)

    def add_episodic(self, role, content):
        """Memória de Curto Prazo (Interação atual)"""
        self.short_term.append({"t": datetime.now().isoformat(), "r": role, "c": content})
        if len(self.short_term) > 20: self.short_term.pop(0)

    def learn_fact(self, key, value):
        """Memória de Longo Prazo (Fatos autorizados)"""
        self.long_term[key] = value
        self.save()
