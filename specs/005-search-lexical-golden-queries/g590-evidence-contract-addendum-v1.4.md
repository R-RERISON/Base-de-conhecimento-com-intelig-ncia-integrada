# G-590 — Evidence Contract Addendum v1.4

**Status:** FROZEN PARA DIAGNÓSTICO AMBIENTAL  
**Data:** 2026-09-24  
**Origem:** RC5 ambiental

## Objetivo

RC5 confirmou que o remaining blocker de hierarchy é real e revelou que o probe selector ainda divergia da autorização canônica do Search.

Este addendum corrige somente o **runner/validator de evidência** e amplia o diagnóstico para orientar a reabertura controlada da SPEC-004.

## Probe authorization

O Search canônico permite os statuses:
- publish;
- draft;
- pending;
- private;
- future;

desde que o usuário possa editar o post.

O runner RC5 ainda aceitava candidate somente quando `post_status=publish`.

RC6 passa a usar exatamente:
- `post_status in ALLOWED_STATUSES`;
- `current_user_can('edit_post', post_id)`;
- anchor `generated`.

`required_probe_source_kinds` passa a ser derivado de `generated_probe_eligible_by_source_kind`, não de todos os anchors generated do corpus.

Novos diagnósticos:
- `generated_probe_eligible_by_source_kind`;
- `post_status_by_source_kind`.

## Strong hierarchy diagnostics

T590-15 permanece blocker.

RC6 não altera Search Section Projection.

Novos diagnósticos:
- `strong_by_source_kind`;
- `strong_without_heading_by_source_kind`;
- `strong_by_confidence`;
- `strong_without_heading_by_confidence`;
- `strong_affected_post_count`;
- `strong_top_affected_posts`;
- `structural_recovery_candidate_count`;
- `structural_recovery_candidate_post_count`;
- `structural_recovery_top_posts`.

`structural_recovery_candidate` é somente diagnóstico:
- hierarchy source = numbering_inferred;
- depth > 1;
- confidence = deterministic;
- no heading context;
- fragment kind = paragraph.

Isso não promove fragmentos, não altera KD, não altera Search runtime e não muda fonte editorial.

## Decisão arquitetural pendente

A SPEC-004 é reaberta em **DISCOVERY/read-only** para determinar se a recuperação deve pertencer:
1. ao Content Extractor/KD;
2. a uma projeção estrutural derivada consumida por Search;
3. ou a outra camada compatível com contratos fechados.

Nenhum writer, migration ou alteração de conteúdo editorial é autorizada por este addendum.
