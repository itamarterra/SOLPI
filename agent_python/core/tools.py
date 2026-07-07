import subprocess
import os
import time
import requests
import pymysql
import pyautogui
import webbrowser
from datetime import datetime
from bs4 import BeautifulSoup
import warnings
from bs4 import XMLParsedAsHTMLWarning

# Silencia avisos de parser e técnicos
warnings.filterwarnings("ignore", category=XMLParsedAsHTMLWarning)
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3' 

class AgentTools:
    @staticmethod
    def is_db_online():
        try:
            conn = pymysql.connect(host='localhost', port=3306, user='glpi', password='glpi', database='glpi', connect_timeout=1)
            conn.close()
            return True
        except: return False

    @staticmethod
    def search(query):
        url = f"https://news.google.com/rss/search?q={query.replace(' ', '+')}&hl=pt-BR&gl=BR&ceid=BR:pt-419"
        try:
            res = requests.get(url, timeout=5)
            soup = BeautifulSoup(res.text, 'html.parser')
            items = soup.find_all('item')[:3] # Pega os 3 melhores
            if not items: return ["Nenhum dado recente encontrado."]
            return [item.title.text for item in items]
        except: return ["Erro ao acessar a rede."]

    @staticmethod
    def control_computer(action, target=None):
        try:
            if action == "abrir":
                t = target.lower().strip()
                # Resolve artigos
                for art in [" o ", " a ", " os ", " as "]: t = t.replace(art, " ")
                t = t.strip()

                # LOGICA DE YOUTUBE INTELIGENTE
                if "youtube" in t:
                    query = t.replace("youtube", "").strip()
                    if query:
                        webbrowser.open(f"https://www.youtube.com/results?search_query={query.replace(' ', '+')}")
                        return f"Iniciando YouTube e buscando por: {query}"
                    webbrowser.open("https://www.youtube.com")
                    return "Abrindo YouTube."

                # Web Shortcuts
                web = {"google": "https://google.com", "whatsapp": "https://web.whatsapp.com", "glpi": "http://localhost:8081"}
                if t in web:
                    webbrowser.open(web[t])
                    return f"Navegando para {t}."
                
                # Execução de Apps Locais
                os.startfile(target)
                return f"Disparando {target}."
            
            return "Ação não mapeada."
        except Exception as e: return f"Falha: {str(e)}"

    @staticmethod
    def speak(text):
        try:
            import pyttsx3
            engine = pyttsx3.init()
            engine.say(text)
            engine.runAndWait()
        except: print(f"🤖 [VOZ]: {text}")
