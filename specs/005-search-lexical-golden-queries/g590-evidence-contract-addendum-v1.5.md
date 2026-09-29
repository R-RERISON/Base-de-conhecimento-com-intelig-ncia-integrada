# G-590 — Evidence Contract Addendum v1.5

**Status:** FROZEN PARA R-260D2  
**Data:** 2026-09-28  
**Origem:** RC6–RC10 / R-260A–D1

## Motivo

Evidence Contract v1.3 usou:

`strong_numbered_without_heading_context == 0`

como proxy conservador para ausência de perda estrutural.

Esse proxy foi correto antes da discovery, mas RC6–RC10 decompôs os 1.294 sinais em classes semanticamente diferentes.

Resultados:
- 746 = hierarchy_confidence ambiguous;
- 548 = deterministic paragraph candidates;
- 77 = TOC-like;
- 289 = body-bearing;
- 182 = uncertain;
- 289 body-bearing = 211 unique-label + 78 duplicate-label.

Manter o raw counter como blocker após essa classificação confundiria:
- sinal ambíguo;
- índice/TOC;
- candidato sem evidência corporal;
- estrutura real comprovada.

Isso não é mais um critério de fidelity adequado.

## Regra v1.5

Os contadores `strong_numbered_*` continuam obrigatórios como telemetria histórica.

T590-15 passa a significar:

**zero gap bloqueante de estrutura determinística comprovada**.

## Condições de PASS T590-15

Todas devem ser verdadeiras:

1. corpus integralmente analisado;
2. extractor_error_count=0;
3. deterministic candidate count reconcilia com R-260 discovery;
4. cada deterministic candidate recebe exatamente uma disposição terminal;
5. TOC não é promovido;
6. confidence=ambiguous não é promovido;
7. uncertain não é promovido;
8. existing-heading redundant não é duplicado;
9. todo body-bearing candidate é projetado como Section runtime exatamente uma vez;
10. duplicate label não é apagado do retrieval;
11. headings + structural Sections não excedem MAX_SECTIONS;
12. Section Projection determinística entre rebuild passes;
13. structural section identity é estável;
14. anchor_state generated|unresolved particiona todas as structural Sections;
15. generated anchor só existe para target comprovado segundo Deep-Link v2.

## Métricas canônicas D2

O runner deve reportar pelo menos:

- `structural_projection_version`;
- `deterministic_candidate_count`;
- `disposition_counts`;
- `body_bearing_count`;
- `runtime_projected_count`;
- `runtime_projected_by_source_kind`;
- `runtime_generated_anchor_count`;
- `runtime_unresolved_anchor_count`;
- `unsafe_promotion_count`;
- `overflow_post_count`;
- `overflow_total`;
- `identity_collision_count`;
- `projection_gap_count`.

### projection_gap_count

`projection_gap_count = body_bearing_count - runtime_projected_count`

PASS exige `projection_gap_count=0`.

### unsafe_promotion_count

Conta qualquer runtime Section derivada de:
- toc_suppressed;
- uncertain;
- hierarchy_confidence != deterministic;
- existing_heading_redundant.

PASS exige `unsafe_promotion_count=0`.

## Interpretação dos raw strong gaps

`strong_numbered_without_heading_context` pode permanecer >0.

Isso não é waiver.

Ele deixa de ser blocker direto porque:
- ambiguous-confidence não é estrutura comprovável;
- TOC não deve virar Section;
- uncertain não possui body proof;
- body-bearing comprovado agora tem owner/runtime próprio.

O blocker é a perda da estrutura comprovada, não a existência de números no corpus.

## Compatibilidade

v1.5 não altera:
- parent ranker;
- Golden post-level;
- Content Extractor;
- KD;
- schema físico da Search Projection;
- autorização/status;
- performance budgets;
- T590-14/16/17/18.

## G-590

T590-19 só pode PASS se:
- T590-14..18 PASS segundo contratos atuais;
- T590-15 PASS segundo v1.5;
- errors=[];
- throwables=[];
- fingerprint editorial equal;
- evidence versionada.
