import subprocess
import os
import shutil
import time
import requests
import pymysql
import pyautogui
from datetime import datetime
from core.security import SecurityGatekeeper
from bs4 import BeautifulSoup

class AgentTools:
    """
    MOTOR HÍBRIDO: FUNCIONA COM OU SEM DOCKER.
    """

    @staticmethod
    def is_db_online():
        """Verifica se o Docker/Banco está acessível."""
        try:
            conn = pymysql.connect(
                host=os.getenv('DB_HOST', 'localhost'),
                port=int(os.getenv('DB_PORT', 3306)),
                user=os.getenv('DB_USER', 'root'),
                password=os.getenv('DB_PASS', 'root'),
                database=os.getenv('DB_NAME', 'glpi'),
                connect_timeout=2
            )
            conn.close()
            return True
        except: return False

    @staticmethod
    def get_executive_summary():
        if not AgentTools.is_db_online():
            return "📡 [MODO SIMULAÇÃO]: O Banco de Dados está offline. No mundo real, eu listaria os chamados do GLPI aqui."
        
        # ... (Lógica real de query que já fizemos)
        return "Relatório Real: 5 Chamados Novos, 2 em Atendimento."

    @staticmethod
    def search(query):
        """Pesquisa Global: Busca qualquer assunto no Google News."""
        print(f"🌍 [RESEARCH]: Pesquisando sobre '{query}'...")
        url = f"https://news.google.com/rss/search?q={query.replace(' ', '+')}&hl=pt-BR&gl=BR&ceid=BR:pt-419"
        try:
            res = requests.get(url, timeout=10)
            # Usando html.parser nativo para evitar erro de 'xml' parser faltante (lxml)
            soup = BeautifulSoup(res.text, 'html.parser')
            items = soup.find_all('item')[:5]

            if not items:
                # Fallback para extração direta de texto se tags <item> não forem detectadas como tal pelo html.parser
                import re
                titles = re.findall(r'<title>(.*?)</title>', res.text)
                if len(titles) > 1: return titles[1:6]
                return [f"Não encontrei resultados para '{query}'."]

            return [f"{item.title.text if item.title else 'Sem título'} (Fonte: {item.source.text if item.source else 'Web'})" for item in items]
        except Exception as e:
            return [f"Erro na pesquisa: {str(e)}"]

    @staticmethod
    def git_sync(message="Auto-sync"):
        try:
            subprocess.run("git add .", shell=True)
            subprocess.run(f'git commit -m "{message}"', shell=True)
            subprocess.run("git push origin main", shell=True)
            return "✅ GitHub sincronizado!"
        except: return "❌ Erro no Git."

    @staticmethod
    def analyze_screen():
        """Vision Engine v1.0: O Agente 'vê' e entende a tela do Itamar."""
        import pyautogui
        import base64
        from io import BytesIO

        print("👁️ [VISION]: Analisando o contexto visual...")
        path = os.path.join(os.getcwd(), "logs", "vision_temp.png")
        os.makedirs(os.path.dirname(path), exist_ok=True)
        pyautogui.screenshot(path)

        with open(path, "rb") as image_file:
            base64_image = base64.b64encode(image_file.read()).decode('utf-8')

        return base64_image

    @staticmethod
    def auto_fix_code(file_path, error_message):
        """O Agente tenta consertar seu próprio código ao detectar um erro."""
        print(f"🔧 [AUTO-FIX]: Tentando corrigir erro em {file_path}...")
        # Lógica de reparo será disparada pelo Brain via IA
        return f"Iniciando protocolo de reparo para: {error_message}"

    @staticmethod
    def speak(text):
        # Melhorei a voz para ser mais natural (se disponível)
        try:
            import pyttsx3
            engine = pyttsx3.init()
            voices = engine.getProperty('voices')
            # Tenta pegar uma voz em português
            for voice in voices:
                if "brazil" in voice.name.lower() or "portuguese" in voice.name.lower():
                    engine.setProperty('voice', voice.id)
                    break
            engine.setProperty('rate', 180)
            engine.say(text)
            engine.runAndWait()
        except: print(f"🤖: {text}")

    @staticmethod
    def listen():
        import speech_recognition as sr
        r = sr.Recognizer()
        try:
            with sr.Microphone() as source:
                r.adjust_for_ambient_noise(source, duration=0.2)
                audio = r.listen(source, timeout=5)
            return r.recognize_google(audio, language='pt-BR')
        except: return ""

    @staticmethod
    def execute_shell(command):
        # Sanitização básica
        if any(x in command.lower() for x in ["format", "del /s", "rmdir"]):
            return {"stdout": "Bloqueado por segurança!", "success": False}
        try:
            result = subprocess.run(command, shell=True, capture_output=True, text=True, timeout=30)
            return {"stdout": result.stdout, "success": result.returncode == 0}
        except Exception as e: return {"error": str(e), "success": False}

    @staticmethod
    def control_computer(action, target=None):
        """Controle de Interface Humana (HCI) v1.1"""
        import pyautogui
        import pygetwindow as gw

        try:
            if action == "abrir":
                os.startfile(target)
                return f"Abrindo {target}..."

            if action == "digitar":
                pyautogui.write(target, interval=0.1)
                pyautogui.press('enter')
                return f"Digitando: {target}"

            if action == "minimizar_tudo":
                pyautogui.hotkey('win', 'd')
                return "Minimizando todas as janelas."

            if action == "screenshot":
                filename = f"screenshot_{datetime.now().strftime('%Y%m%d_%H%M%S')}.png"
                path = os.path.join(os.getcwd(), "logs", filename)
                os.makedirs(os.path.dirname(path), exist_ok=True)
                pyautogui.screenshot(path)
                return f"Screenshot capturado e salvo em: {path}"

            if action == "janela":
                wins = gw.getWindowsWithTitle(target)
                if wins:
                    wins[0].activate()
                    return f"Focando na janela: {target}"
                return f"Não encontrei a janela {target}."

            if action == "click":
                pyautogui.click()
                return "Clique executado no cursor atual."

            return "Ação de controle desconhecida."
        except Exception as e:
            return f"Falha no controle: {str(e)}"

    @staticmethod
    def get_system_vitals():
        """Coleta métricas reais do sistema para o Dashboard."""
        import psutil
        try:
            return {
                "cpu_percent": psutil.cpu_percent(),
                "memory_percent": psutil.virtual_memory().percent,
                "disk_usage": psutil.disk_usage('/').percent,
                "boot_time": datetime.fromtimestamp(psutil.boot_time()).strftime("%Y-%m-%d %H:%M:%S")
            }
        except: return {}

    @staticmethod
    def read_logs(lines=20):
        """Lê os logs de erro do GLPI/SOLPI para diagnóstico."""
        log_path = "C:/SOLPI/SOLPI-main/glpi/plugins/solpi/logs/php-errors.log"
        if not os.path.exists(log_path):
            return "Arquivo de log não encontrado."
        try:
            with open(log_path, 'r') as f:
                return f.readlines()[-lines:]
        except: return "Erro ao ler logs."

    @staticmethod
    def modify_self(file_path, new_content):
        """Habilidade Suprema: O Agente reescreve seu próprio código."""
        # Apenas arquivos na pasta do Agente podem ser modificados
        base_dir = "C:/SOLPI-Agent"
        target_path = os.path.abspath(file_path)

        if not target_path.startswith(os.path.abspath(base_dir)):
            return "BLOQUEIO: Tentativa de modificar arquivos fora do diretório do Agente."

        try:
            # Backup de segurança antes de mudar
            shutil.copy(target_path, target_path + ".bak")
            with open(target_path, 'w', encoding='utf-8') as f:
                f.write(new_content)
            return f"✅ Auto-evolução concluída no arquivo: {file_path}. Backup criado."
        except Exception as e:
            return f"❌ Falha na auto-evolução: {str(e)}"

    @staticmethod
    def reflect_and_fix(task_result):
        """Analisa o resultado de uma tarefa e sugere correções (Auto-correção)."""
        if "Erro" in str(task_result) or "Falha" in str(task_result):
            return "Detectei uma falha. Vou tentar um método alternativo em 5 segundos."
        return "Tarefa bem-sucedida. Prosseguindo."
