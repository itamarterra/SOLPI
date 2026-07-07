import subprocess
import os
import shutil
import time
import requests
import pymysql
import pyautogui
import webbrowser
from datetime import datetime
from bs4 import BeautifulSoup
import warnings
from bs4 import XMLParsedAsHTMLWarning

# Silencia avisos de parser
warnings.filterwarnings("ignore", category=XMLParsedAsHTMLWarning)

class AgentTools:
    @staticmethod
    def is_db_online():
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
    def search(query):
        print(f"🌍 [RESEARCH]: Pesquisando sobre '{query}'...")
        url = f"https://news.google.com/rss/search?q={query.replace(' ', '+')}&hl=pt-BR&gl=BR&ceid=BR:pt-419"
        try:
            res = requests.get(url, timeout=10)
            soup = BeautifulSoup(res.text, 'html.parser')
            items = soup.find_all('item')[:5]
            if not items: return [f"Nenhum resultado para '{query}'."]
            return [f"{item.title.text} (Fonte: {item.source.text if item.source else 'Web'})" for item in items]
        except Exception as e: return [f"Erro: {str(e)}"]

    @staticmethod
    def control_computer(action, target=None):
        import pygetwindow as gw
        try:
            if action == "abrir":
                # Limpeza inteligente de alvo (remove artigos isolados)
                clean_target = " " + target.lower() + " "
                for word in [" o ", " a ", " os ", " as ", " um ", " uma "]:
                    clean_target = clean_target.replace(word, " ")
                clean_target = clean_target.strip()

                # Suporte nativo a YouTube com pesquisa interna
                if "youtube" in clean_target:
                    search_term = clean_target.replace("youtube", "").strip()
                    if search_term:
                        url = f"https://www.youtube.com/results?search_query={search_term.replace(' ', '+')}"
                        webbrowser.open(url)
                        return f"Pesquisando '{search_term}' no YouTube..."
                    webbrowser.open("https://www.youtube.com")
                    return "Abrindo YouTube..."

                # Web Shortcuts
                web_targets = {
                    "google": "https://www.google.com",
                    "gmail": "https://mail.google.com",
                    "whatsapp": "https://web.whatsapp.com",
                    "glpi": "http://localhost:8081"
                }

                if clean_target in web_targets:
                    webbrowser.open(web_targets[clean_target])
                    return f"Navegando para {clean_target}..."

                # Se não for Web, abre como Software local
                os.startfile(target)
                return f"Iniciando {target} localmente..."

            if action == "screenshot":
                path = os.path.join(os.getcwd(), "logs", f"screenshot_{int(time.time())}.png")
                os.makedirs(os.path.dirname(path), exist_ok=True)
                pyautogui.screenshot(path)
                return f"Screenshot salvo em {path}"

            return "Ação desconhecida."
        except Exception as e: return f"Erro no controle: {str(e)}"

    @staticmethod
    def speak(text):
        try:
            import pyttsx3
            engine = pyttsx3.init()
            engine.say(text)
            engine.runAndWait()
        except: print(f"🤖: {text}")

    @staticmethod
    def git_sync(message="Auto-sync"):
        try:
            subprocess.run("git add .", shell=True)
            subprocess.run(f'git commit -m "{message}"', shell=True)
            subprocess.run("git push origin main", shell=True)
            return "✅ GitHub sincronizado!"
        except: return "❌ Erro no Git."

    @staticmethod
    def get_system_vitals():
        import psutil
        try:
            return {"cpu": psutil.cpu_percent(), "mem": psutil.virtual_memory().percent}
        except: return {}

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
