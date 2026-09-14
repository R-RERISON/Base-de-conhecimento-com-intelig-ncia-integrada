# Riscos, Drifts e Dívidas — SPEC-000

> Estado após T095.

## Riscos anteriores
D-001–D-008 permanecem como memória institucional. B-001–B-007 foram classificados por slice em `fechamento-blockers-t095.md`.

## Novos riscos/decisões T095

### X-081 — renomear meta sem benefício
**Risco:** criar migration/dual-read apenas para “limpar” prefixo legado.  
**Tratamento:** reutilizar `_bdc_es_objective`, `_bdc_es_escalation`, `_bdc_es_important` como storage inicial canônico da SPEC-001.

### X-082 — dois writers concorrentes em produção
**Risco:** GRE legado e novo plugin escreverem os mesmos meta keys com contratos diferentes.  
**Tratamento:** SPEC-001 não remove legado; cutover produtivo exige B-003/preflight e decisão single-writer/coexistência comprovada.

### X-083 — `update_post_meta()` false interpretado como falha
**Risco:** NO_CHANGE ser confundido com erro ou erro real ser mascarado.  
**Tratamento:** sucesso é estado relido == esperado; booleano isolado não define sucesso.

### X-084 — compensação falhar
**Risco:** write multi-campo ficar parcialmente aplicado.  
**Tratamento:** snapshot + compensação best-effort + releitura; estado `PARTIAL_FAILURE_CRITICAL` explícito se restauração incompleta.

### X-085 — post type genérico
**Risco:** tela/handler operar em objeto fora da Base de Conhecimento.  
**Tratamento:** baseline da SPEC-001 enumera post types reais antes do código; request não escolhe tipo arbitrário.

### X-086 — settings/event/history por antecipação
**Risco:** primeiro slice ganhar infraestrutura sem requisito.  
**Tratamento:** nenhum settings page, event bus, history/audit ou runtime version option na SPEC-001 salvo novo requisito que passe princípio de negação.

## Blockers para SPEC-001
- B-006: fechado conceitualmente.
- B-001/B-002/B-004/B-005/B-007: não aplicáveis.
- B-003: posterior ao cutover/removal.

**BLOCKER_SPEC001 aberto: zero.**

## Próximo passo
T096 — relatório final consolidado.