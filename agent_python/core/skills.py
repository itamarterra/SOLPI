import os
import importlib.util
import sys

class SkillManager:
    """
    DOCK DE HABILIDADES VIVAS v2.0
    Permite que o SOLPI crie e execute suas próprias ferramentas Python.
    """
    def __init__(self, skills_dir="skills"):
        self.skills_dir = skills_dir
        os.makedirs(self.skills_dir, exist_ok=True)
        self.loaded_skills = {}

    def create_and_install_skill(self, name, code):
        """O Agente escreve e instala uma nova ferramenta em si mesmo."""
        folder = os.path.join(self.skills_dir, name)
        os.makedirs(folder, exist_ok=True)
        
        path = os.path.join(folder, "logic.py")
        with open(path, "w", encoding="utf-8") as f:
            f.write(code)
            
        return f"✅ Habilidade '{name}' codificada e instalada em {path}"

    def run_skill(self, name, *args, **kwargs):
        """Executa uma habilidade dinâmica."""
        path = os.path.join(self.skills_dir, name, "logic.py")
        if not os.path.exists(path):
            return "❌ Habilidade não encontrada."
            
        try:
            spec = importlib.util.spec_from_file_location(name, path)
            module = importlib.util.module_from_spec(spec)
            spec.loader.exec_module(module)
            # Assume que toda skill tem uma função 'execute'
            return module.execute(*args, **kwargs)
        except Exception as e:
            return f"❌ Erro na execução da Skill {name}: {str(e)}"
