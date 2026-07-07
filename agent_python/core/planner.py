import json

class SOLPIPlanner:
    """
    O Cérebro de Planejamento do Agente.
    Decide a sequência de ferramentas necessárias para completar uma missão.
    """
    def __init__(self, brain):
        self.brain = brain

    def create_mission(self, user_input):
        cmd = user_input.lower()

        # Exemplo de decomposição de tarefa complexa
        if "prepara" in cmd and "estagiário" in cmd:
            return [
                {"action": "search", "params": "checklist boas vindas ti estagiário"},
                {"action": "speak", "params": "Estou preparando o ambiente. Vou pesquisar o checklist ideal."},
                {"action": "control", "target": "notepad", "type": "abrir"},
                {"action": "control", "target": "Documento de Onboarding iniciado pelo SOLPI...", "type": "digitar"}
            ]

        if "investigue" in cmd or "resolva" in cmd:
            return [
                {"action": "shell", "params": "netstat -an | findstr LISTENING"},
                {"action": "speak", "params": "Vou verificar as portas abertas no sistema para encontrar anomalias."}
            ]

        if "check-up" in cmd or "saúde do sistema" in cmd:
            return [
                {"action": "speak", "params": "Iniciando diagnóstico completo do sistema."},
                {"action": "vitals", "params": None},
                {"action": "logs", "params": 10},
                {"action": "speak", "params": "Diagnóstico concluído. Verifique o relatório no terminal."}
            ]

        return None # Caso não precise de múltiplos passos
