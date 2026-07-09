import json
import os

class AgentMemory:
    def __init__(self, memory_file="memory.json"):
        self.memory_file = memory_file
        self.load()

    def load(self):
        if os.path.exists(self.memory_file):
            with open(self.memory_file, 'r') as f:
                self.data = json.load(f)
        else:
            self.data = {"conversations": [], "facts": {}}

    def save(self):
        with open(self.memory_file, 'w') as f:
            json.dump(self.data, f, indent=4)

    def add_conversation(self, role, content):
        self.data["conversations"].append({"role": role, "content": content})
        if len(self.data["conversations"]) > 50:
            self.data["conversations"] = self.data["conversations"][-50:]
        self.save()
