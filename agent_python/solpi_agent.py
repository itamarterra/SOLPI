import os
import threading
import time
from dotenv import load_dotenv
from core.brain import SOLPIBrain

load_dotenv()

class SOLPIOS:
    """
    SOLPI-OS: ENTERPRISE AI OPERATING SYSTEM (v28.0)
    Gerenciador Central de Agentes e Recursos.
    """
    def __init__(self):
        self.brain = SOLPIBrain()
        self.active = True

    def run(self):
        os.system('cls' if os.name == 'nt' else 'clear')
        print(f"🚀 SOLPI-OS ENTERPRISE v28.0 (Kernel: {self.brain.kernel.version})")
        print("=" * 60)
        print(f"SISTEMA: ONLINE | MOTOR: TRANSFORMER RMSNORM")
        print(f"UPTIME: {self.brain.kernel.get_uptime()}")
        print("=" * 60)
        
        # Monitoramento Proativo (Vigilância)
        observer = threading.Thread(target=self.proactive_monitor, daemon=True)
        observer.start()

        while self.active:
            try:
                user_input = input("\n[SOLPI-OS] 👤 Itamar: ").strip()
                if user_input.lower() in ['exit', 'shutdown', 'poweroff']: break
                
                response = self.brain.process(user_input)
                print(f"\n[SOLPI-OS] 🧠 AI-RUNTIME:\n{response}")

            except KeyboardInterrupt: break
            except Exception as e: print(f"🚨 KERNEL PANIC: {e}")

    def proactive_monitor(self):
        while self.active:
            self.brain.heartbeat_check()
            time.sleep(300)

if __name__ == "__main__":
    os_instance = SOLPIOS()
    os_instance.run()
