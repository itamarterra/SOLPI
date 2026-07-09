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
        
        print(f"🧬 SOLPI AGENT: EVOLUTION MODE (v5.1)")
        print("-" * 50)
        print(f"Status do Docker/DB: {db_status}")
        print(f"Cérebro Cognitivo: ATIVO 🧠")
        print("-" * 50)
        
        self.brain.tools.speak(f"Olá Itamar! Iniciei o protocolo de evolução. Estou vigiando o sistema.")

        # Inicia o Observador Proativo em segundo plano
        observer = threading.Thread(target=self.proactive_observer, daemon=True)
        observer.start()

        while self.active:
            try:
                print("\n[Peça uma tarefa, pesquisa ou use '$' para comandos]")
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
        """O Agente vigia o sistema e fala com você se notar algo importante."""
        while self.active:
            # 1. Verifica integridade do Banco
            if not self.brain.tools.is_db_online():
                pass
            
            # 2. Exemplo: Verificar carga do sistema (PSUTIL)
            try:
                import psutil
                cpu = psutil.cpu_percent()
                if cpu > 90:
                    self.brain.tools.speak("Atenção Itamar, o uso do processador está muito alto. Quer que eu verifique os processos?")
            except: pass
            
            time.sleep(60) # Verifica a cada minuto

if __name__ == "__main__":
    agent = SOLPIAgent()
    agent.run()
