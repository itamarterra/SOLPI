import os
import glob

class SkillManager:
    def __init__(self, skills_dir="skills"):
        self.skills_dir = skills_dir
        self.skills = {}
        self.load_skills()

    def load_skills(self):
        os.makedirs(self.skills_dir, exist_ok=True)
        skill_files = glob.glob(os.path.join(self.skills_dir, "*.md"))

        for file_path in skill_files:
            skill_name = os.path.basename(file_path).replace(".md", "")
            with open(file_path, "r", encoding="utf-8") as f:
                content = f.read()
                self.skills[skill_name] = content

        print(f"🛠️ {len(self.skills)} habilidades carregadas.")

    def get_skill(self, name):
        return self.skills.get(name)

    def list_skills(self):
        return list(self.skills.keys())

    def create_skill(self, name, instruction):
        """O Agente aprende e cria sua própria habilidade (Self-Improvement)"""
        file_path = os.path.join(self.skills_dir, f"{name}.md")
        content = f"# Skill: {name}\n\n## Instruction\n{instruction}"
        with open(file_path, "w", encoding="utf-8") as f:
            f.write(content)
        self.skills[name] = content
        return file_path
