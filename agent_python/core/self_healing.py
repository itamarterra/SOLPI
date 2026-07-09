import os
import subprocess
import time

class SelfHealingEngine:
    """
    MOTOR DE AUTO-CURA v1.0
    Responsável por detectar falhas e aplicar correções automáticas.
    """
    def __init__(self, tools):
        self.tools = tools

    def diagnose_and_fix(self, issue_type):
        print(f"🛠️ [SELF-HEALING]: Iniciando protocolo de reparo para {issue_type}...")
        
        if issue_type == "DATABASE_OFFLINE":
            return self.fix_database()
        
        if issue_type == "DISK_FULL":
            return self.clean_disk()
            
        return "Nenhum protocolo de cura encontrado para este problema."

    def fix_database(self):
        self.tools.speak("Detectei falha no banco de dados. Tentando reiniciar o container de infraestrutura.")
        # Tenta reiniciar via Docker Compose na pasta oficial
        try:
            cmd = "cd C:/SOLPI/SOLPI-main/glpi && docker compose restart glpi-db"
            res = subprocess.run(cmd, shell=True, capture_output=True, text=True)
            if res.returncode == 0:
                return "✅ Sucesso: O Banco de Dados foi reiniciado com sucesso."
            return f"❌ Falha ao reiniciar: {res.stderr}"
        except Exception as e:
            return f"❌ Erro crítico no protocolo: {str(e)}"

    def clean_disk(self):
        self.tools.speak("O disco está perigosamente cheio. Iniciando limpeza de logs temporários.")
        # Exemplo de limpeza de lixo do sistema/agente
        try:
            log_dir = os.path.join(os.getcwd(), "logs")
            files = os.listdir(log_dir)
            for f in files:
                if f.endswith(".png") or f.endswith(".log"):
                    os.remove(os.path.join(log_dir, f))
            return "✅ Limpeza de emergência concluída. Espaço recuperado."
        except: return "❌ Falha na limpeza."
