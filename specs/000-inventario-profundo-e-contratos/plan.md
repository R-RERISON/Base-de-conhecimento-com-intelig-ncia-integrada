# Plano — SPEC-000 Inventário Profundo e Contratos

## Estado
T000–T059 + T090–T095 concluídos documentalmente. Restam T096 e T097.

## Candidato SPEC-001 após T095
**Core mínimo + Summary narrativo** para Analista de Conhecimento.

Campos canônicos iniciais:
- `_bdc_es_objective`;
- `_bdc_es_escalation`;
- `_bdc_es_important`.

A decisão reutiliza dados GRE comprovados e evita migration/dual-write.

## B-006 fechado conceitualmente
Estratégia:
`authorize -> validate all -> snapshot -> write only changes -> read-after-write -> compare -> success OU compensate -> reread -> safe failure/critical partial state`.

Sucesso é definido pelo estado relido, não pelo booleano isolado de `update_post_meta()`.

## Blockers da SPEC-001
Abertos: **zero**.

B-001/B-002/B-004/B-005/B-007 pertencem a outros slices. B-003 permanece para cutover/removal, não para desenvolvimento/homologação do novo Summary.

## Definition of Ready documental
- usuário/problema/owner definidos;
- storage definido;
- auth `edit_post` por objeto;
- POST + nonce + allowlist/sanitização/escaping;
- consistência B-006 definida;
- Matriz QA T093 definida;
- zero write editorial;
- sem schema/migration/Search/Analytics/IA;
- rollback por deactivation preservando meta;
- post types reais serão enumerados na baseline da SPEC-001 antes de código.

## Próximo passo — T096
Emitir relatório final consolidando a SPEC-000 e recomendação objetiva para o gate T097.

Nenhum runtime antes de T097.