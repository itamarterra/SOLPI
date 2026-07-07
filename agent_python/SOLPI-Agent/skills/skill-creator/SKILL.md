---
name: skill-creator
description: "Crie, edite ou audite novos arquivos SKILL.md para o SOLPI Agent."
---

# Skill Creator

Habilidades são fluxos de trabalho acionáveis. Este módulo permite que o SOLPI Agent aprenda novas tarefas autonomamente.

## Estrutura de uma Skill
```text
nome-da-skill/
  SKILL.md
  scripts/      (opcional) scripts python/php
  references/   (opcional) documentação de apoio
```

## Regras
- Mantenha o `SKILL.md` enxuto.
- Use frontmatter YAML para metadados.
- Valide o YAML após edições.
