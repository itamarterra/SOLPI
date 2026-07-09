import os
import glob
import yaml

class SkillManager:
    """
    Gerenciador de Habilidades (Plugins) do SOLPI Agent.
    Suporta carregamento dinâmico e metadados.
    """
    def __init__(self, skills_dir="skills"):
        self.skills_dir = skills_dir
        self.skills = {}
        self.load_skills()

    def load_skills(self):
        os.makedirs(self.skills_dir, exist_ok=True)
        # Busca todas as pastas de skills
        skill_folders = [f.path for f in os.scandir(self.skills_dir) if f.is_dir()]
        
        for folder in skill_folders:
            skill_file = os.path.join(folder, "SKILL.md")
            if os.path.exists(skill_file):
                skill_name = os.path.basename(folder)
                with open(skill_file, "r", encoding="utf-8") as f:
                    content = f.read()
                    metadata = self._extract_metadata(content)
                    self.skills[skill_name] = {
                        "content": content,
                        "description": metadata.get("description", "Sem descrição"),
                        "active": True
                    }
        print(f"🛠️  Dock de Plugins: {len(self.skills)} habilidades prontas.")

    def _extract_metadata(self, content):
        """Extrai metadados YAML do topo do arquivo SKILL.md"""
        if content.startswith("---"):
            try:
                parts = content.split("---")
                if len(parts) >= 3:
                    return yaml.safe_load(parts[1])
            except: pass
        return {}

    def list_dock(self):
        """Retorna uma lista formatada de todos os plugins no Dock."""
        dock_info = []
        for name, info in self.skills.items():
            status = "✅" if info["active"] else "❌"
            dock_info.append(f"{status} {name}: {info['description']}")
        return dock_info

    def create_skill(self, name, instruction, description="Criada automaticamente", code=None):
        """Permite que o Agente aprenda e salve uma nova skill com código vivo."""
        folder_path = os.path.join(self.skills_dir, name)
        os.makedirs(folder_path, exist_ok=True)
        
        # 1. Salva o Manifesto (SKILL.md)
        file_path = os.path.join(folder_path, "SKILL.md")
        content = f"---\nname: {name}\ndescription: \"{description}\"\n---\n\n# {name}\n\n{instruction}"
        with open(file_path, "w", encoding="utf-8") as f:
            f.write(content)
        
        # 2. Salva o Código Vivo (logic.py) se fornecido
        if code:
            logic_path = os.path.join(folder_path, "logic.py")
            with open(logic_path, "w", encoding="utf-8") as f:
                f.write(code)
            self.skills[name] = {"content": content, "description": description, "active": True, "logic": logic_path}
        else:
            self.skills[name] = {"content": content, "description": description, "active": True}
            
        return folder_path
