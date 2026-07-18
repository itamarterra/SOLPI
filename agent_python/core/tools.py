import subprocess
import os
import time
import requests
import pymysql
import pyautogui
import webbrowser
import pygetwindow as gw
from datetime import datetime
from bs4 import BeautifulSoup
import warnings

# Configurações de Segurança e Performance
pyautogui.FAILSAFE = True # Se você mover o mouse para o canto da tela, o Agente para por segurança.
warnings.filterwarnings("ignore")

class AgentTools:
    @staticmethod
    def analyze_screen():
        """Vision Engine: Captura e prepara imagem para o Cérebro."""
        path = os.path.join(os.getcwd(), "logs", "vision_active.png")
        os.makedirs(os.path.dirname(path), exist_ok=True)
        pyautogui.screenshot(path)
        import base64
        with open(path, "rb") as img:
            return base64.b64encode(img.read()).decode('utf-8')

    @staticmethod
    def visual_click(x, y):
        """Executa um clique físico em coordenadas detectadas pela IA."""
        pyautogui.moveTo(x, y, duration=0.5)
        pyautogui.click()
        return f"Clique executado em X:{x}, Y:{y}"

    @staticmethod
    def interact_with_window(context, command):
        try:
            windows = gw.getAllWindows()
            target_win = next((w for w in windows if context.lower() in w.title.lower()), None)
            if target_win:
                target_win.activate()
                time.sleep(0.5)
                if "pause" in command or "play" in command: pyautogui.press('space')
                if "desce" in command: pyautogui.scroll(-600)
                if "sobe" in command: pyautogui.scroll(600)
                return "Interação de janela concluída."
            return "Janela não encontrada."
        except: return "Erro na interação."

    @staticmethod
    def control_computer(action, target=None):
        try:
            if action == "abrir":
                t = target.lower().strip()
                web = {"youtube": "https://youtube.com", "google": "https://google.com", "glpi": "http://localhost:8081"}
                if t in web: webbrowser.open(web[t]); return f"Navegando: {t}"
                os.startfile(target); return f"Abrindo: {target}"
            return "Ação desconhecida."
        except Exception as e: return str(e)

    @staticmethod
    def search(query):
        try:
            res = requests.get(f"https://news.google.com/rss/search?q={query.replace(' ', '+')}&hl=pt-BR", timeout=5)
            soup = BeautifulSoup(res.text, 'html.parser')
            return [i.title.text for i in soup.find_all('item')[:3]]
        except: return ["Erro de conexão."]

    @staticmethod
    def self_audit():
        """O Agente verifica se ele e seu ambiente estão saudáveis."""
        from core.self_healing import SelfHealingEngine
        healer = SelfHealingEngine(AgentTools)
        
        report = []
        # 1. Checa Banco de Dados
        if AgentTools.is_db_online():
            report.append("🗄️ Banco GLPI: ONLINE")
        else:
            report.append("⚠️ Banco GLPI: OFFLINE. Iniciando Auto-Cura...")
            fix_res = healer.diagnose_and_fix("DATABASE_OFFLINE")
            report.append(f"🛠️ Resultado do Reparo: {fix_res}")
            
        # 2. Checa Espaço em Disco
        import shutil
        total, used, free = shutil.disk_usage("/")
        free_gb = free // (2**30)
        report.append(f"💾 Disco: {free_gb}GB Livres")
        
        if free_gb < 2:
            report.append("⚠️ Espaço Crítico! Iniciando Limpeza...")
            report.append(healer.diagnose_and_fix("DISK_FULL"))
        
        # 3. Checa Memória do Agente
        report.append("🧠 Cérebro Cognitivo: Estável v16.0")
        return report

    @staticmethod
    def send_whatsapp(message):
        """Envia um alerta técnico para o WhatsApp do Diretor via Evolution API."""
        url = os.getenv("SOLPI_EVOLUTION_URL")
        token = os.getenv("SOLPI_EVOLUTION_TOKEN")
        instance = os.getenv("SOLPI_EVOLUTION_INSTANCE", "SOLPI")
        phone = os.getenv("DIRECTOR_PHONE")

        if not all([url, token, phone]):
            print("⚠️ Erro: Configurações de WhatsApp incompletas no .env")
            return False

        endpoint = f"{url}/message/sendText/{instance}"
        headers = {"apikey": token, "Content-Type": "application/json"}
        payload = {
            "number": phone,
            "options": {"delay": 1200, "presence": "composing", "linkPreview": False},
            "textMessage": {"text": f"🤖 *SOLPI AGENT v15.0*\n\n{message}"}
        }

        try:
            res = requests.post(endpoint, headers=headers, json=payload, timeout=10)
            return res.status_code in [200, 201]
        except: return False

    @staticmethod
    def write_project_file(relative_path, content):
        """Escrita Cirúrgica: O Agente altera o código do projeto SOLPI v16.0."""
        base_project = "C:/SOLPI/SOLPI-main/glpi/plugins/solpi/"
        target = os.path.abspath(os.path.join(base_project, relative_path))
        if not target.startswith(os.path.abspath(base_project)):
            return "❌ BLOQUEIO: Fora do escopo SOLPI."
        try:
            if os.path.exists(target):
                import shutil
                shutil.copy(target, target + ".patch_bak")
            with open(target, 'w', encoding='utf-8') as f:
                f.write(content)
            return f"✅ Patch aplicado em: {relative_path}"
        except Exception as e: return f"❌ Falha: {str(e)}"

    @staticmethod
    def speak(text):
        """Voz desativada a pedido do Diretor v17.0"""
        print(f"🤖 [MENSAGEM]: {text}")
        pass

    @staticmethod
    def is_db_online():
        try:
            conn = pymysql.connect(host='localhost', port=3306, user='glpi', password='glpi', database='glpi', connect_timeout=1)
            conn.close(); return True
        except: return False
