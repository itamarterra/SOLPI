---
name: coding-agent
description: "Delegue tarefas de codificação, revisões de PR e refatorações complexas."
metadata:
  emoji: "🧩"
---

# Coding Agent (SOLPI Edition)

Use para construção de funcionalidades, revisão de código PHP/Python e ciclos de correção de bugs.

## Regras de Ouro
- Priorize PHP 8.3 e tipos estritos (PHPStan Nível 6).
- Use `requests` para chamadas de API.
- Sempre valide se o código gerado segue os padrões do SOLPI Core.

## Fluxo de Trabalho
1. Analise os arquivos existentes.
2. Proponha a mudança.
3. Execute testes (PHPStan/Lint).
4. Aplique via `write_file` ou `replace_file_content`.
