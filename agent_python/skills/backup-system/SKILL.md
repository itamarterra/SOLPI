---
name: backup-system
description: "Gestão proativa de snapshots, backups compactados e restauração de segurança."
---

# Backup System

Este módulo protege a integridade intelectual do projeto SOLPI.

## Funções
1. **Compressão:** Cria arquivos ZIP da pasta raiz do projeto.
2. **Histórico:** Mantém os últimos backups na pasta `backups/`.
3. **Automação:** Pode ser agendado para rodar em intervalos fixos.

## Regras
- Sempre verifique o espaço em disco antes de iniciar um backup grande.
- Backups com erro devem disparar um alerta de voz para o Itamar.
