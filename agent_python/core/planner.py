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
                {"type": "search", "params": "checklist boas vindas ti estagiário"},
                {"type": "speak", "params": "Estou preparando o ambiente. Vou pesquisar o checklist ideal."},
                {"type": "control", "target": "notepad", "action": "abrir"},
                {"type": "control", "target": "Documento de Onboarding iniciado pelo SOLPI...", "action": "digitar"}
            ]
        
        if "investigue" in cmd or "resolva" in cmd:
            return [
                {"type": "shell", "params": "netstat -an | findstr LISTENING"},
                {"type": "speak", "params": "Vou verificar as portas abertas no sistema para encontrar anomalias."}
            ]

        if "check-up" in cmd or "saúde do sistema" in cmd:
            return [
                {"type": "speak", "params": "Iniciando diagnóstico completo do sistema."},
                {"type": "vitals", "params": None},
                {"type": "logs", "params": 10},
                {"type": "speak", "params": "Diagnóstico concluído. Verifique o relatório no terminal."}
            ]

        return None # Caso não precise de múltiplos passos
