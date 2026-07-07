---
name: system-control
description: "Controle o sistema operacional, tire prints e execute comandos via terminal."
---

# System Control

Habilidade de "mãos" do agente para interagir diretamente com o Windows.

## Ferramentas Disponíveis
1. `take_screenshot`: Tira uma foto da tela atual. Use quando o usuário pedir para ver algo ou reportar erro visual.
2. `execute_shell`: Roda comandos (dir, ipconfig, netstat, etc).
3. `read_logs`: Verifica os logs do SOLPI/GLPI para diagnóstico.

## Regras de Segurança
- Nunca execute comandos de deleção total (`del /s`, `rm -rf`) sem confirmação explícita.
- Ao tirar um print, informe o caminho do arquivo salvo.
- Comandos que demoram mais de 30 segundos serão interrompidos.
