import re

class SecurityGatekeeper:
    """
    O Escudo do SOLPI Agent. 
    Analisa intenções maliciosas e protege o Sistema Operacional.
    """
    
    # Comandos e palavras terminantemente proibidas
    BLACKLIST = [
        "rmdir", "del /s", "format", "mkfs", "shutdown", 
        "registry delete", "netsh firewall set", "powershell -enc",
        "rm -rf", ":(){ :|:& };:", "drop table", "truncate"
    ]

    @staticmethod
    def is_safe(input_string):
        """
        Verifica se o comando ou texto contém padrões de ataque conhecidos.
        """
        # 1. Checa contra a lista negra
        for forbidden in SecurityGatekeeper.BLACKLIST:
            if forbidden in input_string.lower():
                return False, f"⚠️ BLOQUEIO DE SEGURANÇA: O termo '{forbidden}' é restrito."

        # 2. Bloqueia tentativas de concatenação de comandos (&&, ||, ;)
        if re.search(r"[&|;]{2,}", input_string):
            return False, "⚠️ BLOQUEIO DE SEGURANÇA: Tentativa de encadeamento de comandos suspeita."

        return True, "Safe"

    @staticmethod
    def sanitize_shell(command):
        """Limpa o comando shell antes da execução."""
        clean_cmd = re.sub(r"[><|&;]", "", command)
        return clean_cmd.strip()
