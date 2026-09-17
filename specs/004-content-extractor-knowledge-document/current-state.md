# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline e gates

- SPEC-001/002/003: concluídas.
- UX-001/UX-002: concluídas; UX-002 `0.4.0-ux002.3` é contrato visual obrigatório.
- G-240: PASS / CLOSED / promovido para `main`.
- KD 2.1.0: PASS técnico full-corpus + PASS humano 8/8.
- G-245: **REBASELINED / IN PROGRESS** em `spec004-g245-production-readiness`; PR #4 DRAFT / NÃO MERGEAR.
- ADR-004-001: **ACEITA**.
- T090 Block Projection v1.0: PASS LOCAL / READ-ONLY.
- T091 Block Projection full-corpus: **PASS AMBIENTAL**.
- T092 Block Projection v1.1: **PASS LOCAL / READ-ONLY**.
- T093 full-corpus v1.1 + diagnóstico KD: **IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE**.
- G-250: NOT_RUN.

## Arquitetura editorial vigente

Canônica futura: `WP_Post.post_content` + WordPress Core Blocks.

- plugin Gutenberg não é dependência;
- somente APIs estáveis presentes no WordPress Core homologado;
- Elementor é source adapter legado durante a transição;
- `_elementor_data` deve ser preservado enquanto houver dependência;
- nenhum novo writer usa `_elementor_data` como destino.

ADR: `adr/ADR-004-001-wordpress-core-blocks-canonical-editorial-target.md`.

## Baseline ambiental atual

T091 executado em WordPress `6.9.4`, PHP `8.5.10`, Elementor `4.1.0` e plugin `0.4.0-g245-block-projection-smoke.1`.

O corpus observado passou de 622 para **623 posts** entre T081 e T091. Isso é drift de baseline entre execuções, não mutação causada pelo gate: T091 confirmou corpus/fingerprint estáveis durante as duas passagens.

Source kinds T091:

- legacy_html 536;
- plain_text 41;
- elementor 34;
- mixed 5;
- gutenberg 4;
- empty 3.

Plan status:

- projectable 347;
- review_required 269;
- native_noop 4;
- not_applicable 3.

T091: duas passagens 623/623, zero errors/throwables/hash mismatches/canonical mismatches/safety violations, fingerprint before/after idêntico e `gate_result.t091_block_projection_pass=true`.

Evidência: `evidence/g245-block-projection-t091-20260917T172515Z.json`.

## Gaps reais observados em T091

- `KNOWLEDGE_DOCUMENT_REVIEW_REQUIRED`: 233;
- `BLOCK_PROJECTION_UNSUPPORTED_KIND:image`: 40;
- `BLOCK_PROJECTION_TABLE_SPAN_REVIEW`: 32;
- `BLOCK_PROJECTION_UNSUPPORTED_KIND:quote`: 8.

Esses dados substituem hipótese por backlog quantitativo.

## T092 — Block Projection 1.1

Build de desenvolvimento: `0.4.0-g245-block-projection.2`.

Contrato: `block-projection-contract-v1.1.md`.

Mudanças:

- `quote` passa a `core/quote`;
- schema de Block Projection passa a `1.1.0`;
- imagem permanece review porque o KD atual não carrega referência canônica de mídia suficiente para construir `core/image` sem inventar dados;
- tabelas com spans permanecem review.

Validação local: **25/25 assertions PASS + PHP lint PASS**.

## T093 — diagnóstico full-corpus v1.1

O mesmo runner read-only foi evoluído para agregar:

- status de `ai_readiness` do KD;
- reasons do KD somente para `review_required|not_ready`;
- matriz source kind × plan status;
- warnings e block names.

O runner continua sem exportar conteúdo editorial ou post IDs e sem serialização/persistência.

## Investimentos preservados

Permanecem válidos: Content Extractor, KD 2.1, adapters, Canonical JSON/hashes, journal durable, stale-source guard, dry-run, batch plan, lock, canary/rollback methodology e runbook.

T083B Journal Durable Storage segue PASS ambiental e reutilizável para futura Block Migration.

## Itens SUPERSEDED

- Elementor como destino editorial futuro;
- T087C writer Elementor;
- writer em `_elementor_data`;
- Elementor Projection Plan como plano de migração final.

## Próximo passo técnico

Executar T093 na homologação e analisar o JSON. O diagnóstico resultante define T094, principalmente:

1. causas dos 233 KD reviews;
2. decisão sobre enriquecimento seguro de mídia para imagens;
3. tratamento/fallback explícito para table spans;
4. contrato de serialização Core Blocks in-memory.

Nenhum writer é autorizado por T092/T093.

## Guardrails

- UX-002 não pode regredir;
- plugin Gutenberg não pode virar dependência silenciosa;
- Elementor não pode ser removido antes de dependência zero;
- nenhuma migração automática em activation/update;
- nenhum writer está autorizado;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
