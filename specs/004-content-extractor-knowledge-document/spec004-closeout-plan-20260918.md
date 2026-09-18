# SPEC-004 — Closeout Plan 2026-09-18

**Objetivo:** encerrar a SPEC-004 hoje sem abrir nova feature.

## Estado de entrada

- G-240: PASS/CLOSED.
- G-245: arquitetura e migração canônica comprovadas.
- T100D: PASS ambiental persistente.
- T100E E1-E5: concluídos.
- HE5-001: PASS ambiental.
- UX-003: PASS ambiental.
- PR #4: DRAFT.

## Sequência de fechamento

### C1 — T100E-E6 Workspace Regression Matrix

Cobrir, sem write:
- Gutenberg/Core Blocks nativo;
- legacy_html;
- plain_text;
- Elementor;
- mixed;
- journal terminal;
- sem journal;
- lock held/free;
- source drift;
- review_required.

Saída: matriz PASS/REVIEW/BLOCK com evidência sanitizada.

### C2 — T100E-E7 Production Readiness Exit

Exigir:
- runner único PASS;
- runtime classificado;
- build determinístico reproduzível;
- UX-003 PASS;
- HE5-001 PASS;
- dívida residual documentada;
- zero mudança editorial não autorizada.

### C3 — G-250 Lifecycle / RC

Exigir:
- package clean RC;
- remover ferramentas temporárias do artefato instalável;
- source/artifact manifest;
- lint/syntax;
- install/upgrade sobre versão atual;
- deactivate/activate;
- smoke SPEC-001/002/003;
- smoke Content Extractor/KD read-only;
- smoke Workspace;
- checksum e relatório final.

### C4 — Residual Elementor Inventory

A SPEC-004 não exige remover Elementor. Exige conhecer a dependência residual.

Fechamento deve declarar:
- quantidade atual por source kind;
- dependências Elementor/mixed restantes;
- Elementor Adapter permanece;
- remoção física fica para gate futuro quando dependência = zero.

## Critério final

SPEC-004 fecha quando E6 + E7 + G-250 PASS e a dependência residual Elementor estiver explicitamente documentada.

Nenhuma IA, busca, embeddings ou nova feature entra antes do fechamento.
