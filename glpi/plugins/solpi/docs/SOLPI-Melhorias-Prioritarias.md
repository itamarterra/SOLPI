# SOLPI - Checklist de Melhorias Prioritarias

## 1. Ambiente e compatibilidade
- [x] Expor claramente o requisito minimo de PHP 8.3.
- [ ] Adicionar checagem de ambiente/CI para falhar cedo quando a versao estiver abaixo do minimo.
- [ ] Garantir que a documentacao principal destaque o requisito de runtime.

## 2. Cobertura automatizada
- [ ] Expandir testes para lote, checkpoints, paginacao e truncamento.
- [ ] Criar validacoes para REST, SQL, CSV, XML e SOAP.
- [ ] Cobrir caminhos de erro do worker e da fila.

## 3. Observabilidade operacional
- [x] Expor resumo de jobs, batches, duplicados, truncados e falhas por janela.
- [ ] Padronizar metadados de execucao em respostas da API.
- [ ] Melhorar diagnostico para suporte e operacao.

## 4. Contratos de API
- [x] Uniformizar respostas de ingestao direta, por adapter, checkpoints e worker.
- [ ] Documentar schemas principais de entrada e saida.
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
