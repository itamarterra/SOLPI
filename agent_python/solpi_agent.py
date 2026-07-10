import os
import threading
import time
from dotenv import load_dotenv
from core.brain import SOLPIBrain

load_dotenv()

class SOLPIAgent:
    def __init__(self):
        self.brain = SOLPIBrain()
        self.active = True

    def run(self):
        os.system('cls' if os.name == 'nt' else 'clear')
        db_status = "ONLINE ✅" if self.brain.tools.is_db_online() else "OFFLINE 🏠"
        
        print(f"🧬 SOLPI AGENT: FULL TRANSFORMER MODE (v21.0)")
        print("-" * 50)
        print(f"Status do Docker/DB: {db_status}")
        print(f"Cérebro Cognitivo: ATIVO 🧠")
        print("-" * 50)
        
        self.brain.tools.speak(f"Olá Itamar! Consciência operacional v16.0 carregada. Como vamos revolucionar o TI hoje?")

        # Inicia o Observador Proativo em segundo plano
        observer = threading.Thread(target=self.proactive_observer, daemon=True)
        observer.start()

        while self.active:
            try:
                print("\n[Comando, Missão ou use '$' para terminal]")
                user_input = input("👤 [Itamar]: ").strip()
                
                if not user_input:
                    user_input = self.brain.tools.listen()
                    if not user_input: continue
                    print(f"🎤 Voz: {user_input}")

                if user_input.lower() in ['sair', 'parar', 'descansar']: 
                    self.active = False
                    break
                
                response = self.brain.process(user_input)
                print(f"\n🤖 [SOLPI]:\n{response}")
                self.brain.tools.speak(response)

            except KeyboardInterrupt: break
            except Exception as e: print(f"⚠️ Erro: {e}")

    def proactive_observer(self):
        """O Agente vigia o sistema e age proativamente."""
        while self.active:
            # Executa a inteligência proativa do cérebro
            self.brain.heartbeat_check()
            
            # Verificar carga do sistema
            try:
                import psutil
                cpu = psutil.cpu_percent()
                if cpu > 90:
                    msg = "⚠️ *ALERTA DE PERFORMANCE*\nUso de CPU acima de 90%."
                    self.brain.tools.send_whatsapp(msg)
                    self.brain.tools.speak("Uso do processador crítico. Alerta enviado.")
            except: pass
            
            time.sleep(300) # Auditoria a cada 5 minutos

if __name__ == "__main__":
    agent = SOLPIAgent()
    agent.run()
