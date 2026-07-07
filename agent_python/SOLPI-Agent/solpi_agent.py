import os
import platform
import psutil
from dotenv import load_dotenv
from core.brain import SOLPIBrain

load_dotenv()

class SOLPIAgent:
    def __init__(self):
        self.brain = SOLPIBrain()
        self.pc_name = platform.node()

    def get_system_status(self):
        cpu = psutil.cpu_percent(interval=1)
        ram = psutil.virtual_memory().percent
        return f"CPU: {cpu}% | RAM: {ram}%"

    def run(self):
        print(f"🚀 SOLPI Agent (Powered by Hermes Engine) - Host: {self.pc_name}")
        print(f"Habilidades disponíveis: {self.brain.skills.list_skills()}")

        while True:
            try:
                user_input = input("\n👤 [Você]: ")
                if user_input.lower() in ['sair', 'exit']:
                    break

                if user_input.lower() == 'status':
                    print(f"🖥️ [Status]: {self.get_system_status()}")
                    continue

                # Processamento via Cérebro Hermes
                response = self.brain.process(user_input)
                print(f"🤖 [SOLPI]: {response}")

            except KeyboardInterrupt:
                break

if __name__ == "__main__":
    agent = SOLPIAgent()
    agent.run()
