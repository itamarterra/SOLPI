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

# Configurações globais de HCI
pyautogui.FAILSAFE = False

class AgentTools:
    @staticmethod
    def search(query):
        url = f"https://news.google.com/rss/search?q={query.replace(' ', '+')}&hl=pt-BR&gl=BR&ceid=BR:pt-419"
        try:
            res = requests.get(url, timeout=5)
            soup = BeautifulSoup(res.text, 'html.parser')
            items = soup.find_all('item')[:3]
            return [item.title.text for item in items] if items else ["Sem resultados."]
        except: return ["Erro de rede."]

    @staticmethod
    def interact_with_window(context, command):
        """Interage com janelas abertas baseado no contexto."""
        try:
            # Tenta achar a janela do navegador (Chrome, Edge, etc)
            windows = gw.getAllWindows()
            target_win = None
            
            # Procura por janelas que contenham o contexto no título
            for w in windows:
                if context.lower() in w.title.lower() or "chrome" in w.title.lower() or "edge" in w.title.lower():
                    target_win = w
                    break
            
            if target_win:
                target_win.activate()
                time.sleep(0.5)
                
                if "pause" in command or "para" in command or "continua" in command or "play" in command:
                    pyautogui.press('space')
                    return "Ação de Reprodução executada."
                
                if "desce" in command or "rola" in command:
                    pyautogui.scroll(-500)
                    return "Rolagem para baixo executada."
                    
                if "sobe" in command:
                    pyautogui.scroll(500)
                    return "Rolagem para cima executada."
                
                return "Janela focada, mas comando não mapeado para HCI."
            return f"Não consegui encontrar a janela de {context} para interagir."
        except Exception as e: return f"Erro HCI: {str(e)}"

    @staticmethod
    def control_computer(action, target=None):
        try:
            if action == "abrir":
                t = target.lower().strip()
                for art in [" o ", " a ", " os ", " as "]: t = t.replace(art, " ")
                t = t.strip()

                if "youtube" in t:
                    query = t.replace("youtube", "").strip()
                    url = f"https://www.youtube.com/results?search_query={query.replace(' ', '+')}" if query else "https://www.youtube.com"
                    webbrowser.open(url)
                    return f"Iniciando YouTube: {query}"

                web = {"google": "https://google.com", "whatsapp": "https://web.whatsapp.com", "glpi": "http://localhost:8081"}
                if t in web:
                    webbrowser.open(web[t])
                    return f"Navegando para {t}."
                
                os.startfile(target)
                return f"Disparando {target}."
            return "Ação desconhecida."
        except Exception as e: return f"Falha: {str(e)}"

    @staticmethod
    def speak(text):
        try:
            import pyttsx3
            engine = pyttsx3.init()
            engine.say(text)
            engine.runAndWait()
        except: print(f"🤖 [VOZ]: {text}")

    @staticmethod
    def is_db_online():
        try:
            conn = pymysql.connect(host='localhost', port=3306, user='glpi', password='glpi', database='glpi', connect_timeout=1)
            conn.close()
            return True
        except: return False
