# SOLPI - Checklist de Melhorias Prioritarias

## 1. Ambiente e compatibilidade
- [x] Expor claramente o requisito minimo de PHP 8.3.
- [x] Adicionar checagem de ambiente/CI para falhar cedo quando a versao estiver abaixo do minimo.
- [x] Garantir que a documentacao principal destaque o requisito de runtime.

## 2. Cobertura automatizada
- [x] Expandir testes para lote, checkpoints, paginacao e truncamento.
- [x] Criar validacoes para REST e SOAP.
- [x] Criar validacoes para SQL, CSV e XML.
- [x] Cobrir caminho de paginacao e incremental do SQL adapter.
- [x] Cobrir caminho de paginacao e offset do CSV adapter.
- [x] Cobrir parsing e records_path do XML adapter.
- [x] Cobrir caminhos de erro do worker e da fila.

## 3. Observabilidade operacional
- [x] Expor resumo de jobs, batches, duplicados, truncados e falhas por janela.
- [x] Padronizar metadados de execucao em respostas da API.
- [x] Melhorar diagnostico para suporte e operacao.

## 4. Contratos de API
- [x] Uniformizar respostas de ingestao direta, por adapter, checkpoints e worker.
- [x] Documentar schemas principais de entrada e saida.
- [ ] Revisar consistencia de nomes e metadados.

## 5. Conhecimento e IA
- [ ] Avancar busca semantica sobre Knowledge Graph.
- [ ] Ampliar contexto historico para classificacao.
- [ ] Preparar assistente IA com limites e fallback humano.

## Ordem recomendada de execucao
1. Ambiente e compatibilidade.
2. Cobertura automatizada.
3. Observabilidade operacional.
4. Contratos de API.
5. Conhecimento e IA.
