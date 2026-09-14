# Pesquisa Consolidada — SPEC-000

## Estado após T095
A arquitetura e as revisões T090–T094 foram confrontadas contra o candidato concreto de SPEC-001.

## Resultado T095
Artefato: `fechamento-blockers-t095.md`.

### Blockers B-001–B-007
- B-001: Search/RAG, não SPEC-001.
- B-002: Classificação/cutover, não SPEC-001.
- B-003: cutover/aliases/removal; não bloqueia dev/homologação do Summary sem retirada de legado.
- B-004: Analytics, não SPEC-001.
- B-005: item/deep-link, não SPEC-001.
- B-006: único aplicável; fechado conceitualmente.
- B-007: async/queue, não SPEC-001.

### Storage Summary
T095 decide reutilizar como canônicas iniciais as chaves GRE já comprovadas:
- `_bdc_es_objective`;
- `_bdc_es_escalation`;
- `_bdc_es_important`.

Razão: mesma semântica, preservação de dados e ausência de ganho em renomear/migrar três metas.

### B-006
Fluxo definido:
1. autorização/método/nonce;
2. validar todos inputs;
3. snapshot anterior;
4. diff/NO_CHANGE;
5. writes mínimos;
6. read-after-write;
7. sucesso somente se estado == esperado;
8. mismatch -> falha + compensação best-effort;
9. releitura pós-compensação;
10. partial failure crítico explícito se restauração incompleta.

### Superfície SPEC-001
- `edit_post` por objeto;
- wp-admin server-rendered;
- POST + nonce;
- sem REST/AJAX/SPA;
- sem settings/event bus/history/audit/migration/schema;
- activation mínima;
- deactivate/uninstall não destroem postmeta.

### Unknown de post type
A futura SPEC deve enumerar os post types reais da Base de Conhecimento na baseline antes do código. Request não escolhe post type arbitrário. Isso é gate de Ready da implementação, não blocker de criação da SPEC.

## Resultado
`BLOCKER_SPEC001 = 0` documentalmente. T096 pode emitir relatório final e T097 decidir autorização formal.