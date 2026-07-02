# SOLPI - Plano de 90 Dias

Este plano organiza o caminho mais consistente para o SOLPI evoluir de forma segura, escalavel e util para o futuro do projeto.

## Objetivo dos 90 dias

Consolidar a base enterprise do SOLPI, fortalecer a camada de ingestao e observabilidade, preparar o motor de conhecimento e deixar o terreno pronto para IA e automacao com confiabilidade.

## Direcao estrategica

1. Fechar a camada de escala da ingestao.
2. Tornar a observabilidade operacional e auditavel.
3. Expandir conhecimento contextual e busca semantica.
4. Evoluir classificacao e assistencia IA com seguranca.
5. Preparar automacoes e integracoes externas.

## Fase 1 - Dias 1 a 30

### Foco
Escala, estabilidade e controle operacional.

### Entregas
- Refinar micro-batching por origem.
- Consolidar stop conditions em REST, SQL, CSV, XML e SOAP.
- Padronizar metadados de lote em jobs e auditoria.
- Expandir checkpoints e replays por adaptador.
- Reforcar smoke tests para cargas grandes.

### Resultado esperado
- Ingestoes grandes ficam previsiveis.
- O operador consegue entender exatamente o que foi processado por lote.
- O sistema reduz chamadas e processamento desnecessario.

## Fase 2 - Dias 31 a 60

### Foco
Conhecimento, contexto e busca inteligente.

### Entregas
- Expandir Knowledge Graph para tickets, ativos, usuarios, contratos e documentos.
- Criar ligacao entre chamados resolvidos e solucoes aplicadas.
- Melhorar ranking semantico de candidatos e recomendacoes.
- Estruturar curadoria de conhecimento para revisao humana.
- Aumentar a rastreabilidade entre evento de origem e conhecimento derivado.

### Resultado esperado
- O SOLPI passa a aprender melhor com a operacao real.
- A base de conhecimento deixa de ser passiva e vira ativo util.
- Buscas e sugestoes ficam mais proximas do contexto do negocio.

## Fase 3 - Dias 61 a 90

### Foco
IA assistiva, automacao segura e integracoes operacionais.

### Entregas
- Melhorar classificacao com contexto historico.
- Evoluir o assistente IA para respostas, resumo e recomendacoes.
- Implementar gates de seguranca para acoes sensiveis.
- Estruturar automacoes de ticket, ativo e usuario.
- Avancar integracoes com Zabbix, Evolution API, WhatsApp e SMTP.

### Resultado esperado
- O SOLPI ajuda mais, sem perder controle.
- Automatizacoes relevantes entram em producao com auditoria.
- O projeto ganha visibilidade como plataforma enterprise, nao apenas plugin.

## Prioridades tecnicas permanentes

- Nao criar duplicatas.
- Nao perder dados de origem.
- Nao sobrescrever campos criticos sem politica.
- Manter audit trail completo.
- Preferir pequenas entregas validaveis.
- Manter compatibilidade com GLPI.

## Indicadores de sucesso

- Menos chamadas redundantes em ingestao.
- Mais jobs rastreaveis com contexto de lote.
- Menor necessidade de revisao manual para entidades claras.
- Mais conhecimento reaproveitado a partir da operacao.
- Mais automacoes com seguranca e rastreabilidade.

## Referencias

- [SOLPI Enterprise Vision and Roadmap](SOLPI-Enterprise-Vision-Roadmap.md)
- [SOLPI Technical Roadmap](SOLPI-Technical-Roadmap.md)
- [Integration Engine Architecture Blueprint](SOLPI-IntegrationEngine-Architecture.md)
