# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline e gates

- SPEC-001/002/003: concluídas.
- UX-001/UX-002: concluídas; UX-002 `0.4.0-ux002.3` é contrato visual obrigatório.
- G-240: PASS / CLOSED / promovido para `main`.
- KD 2.1.0: PASS técnico full-corpus + PASS humano 8/8.
- G-245: **REBASELINED / IN PROGRESS** em `spec004-g245-production-readiness`; PR #4 DRAFT / NÃO MERGEAR.
- ADR-004-001: ACEITA — Core Blocks como destino editorial canônico.
- T091: PASS AMBIENTAL.
- T092: PASS LOCAL.
- T093: PASS AMBIENTAL.
- T094 Editorial Fidelity: PASS AMBIENTAL.
- T095 Migration Fidelity Source v1: PASS LOCAL / READ-ONLY.
- T096 Lossless Core Block Serialization: **PASS AMBIENTAL**.
- T097 Static Editorial Parity + stale-source: **PASS LOCAL / HOMOLOGAÇÃO PENDENTE**.
- G-250: NOT_RUN.

## Arquitetura editorial vigente

Canônica futura: `WP_Post.post_content` + WordPress Core Blocks.

- plugin Gutenberg não é dependência;
- Elementor é source adapter legado/read-only durante transição;
- `_elementor_data` é preservado enquanto houver dependência;
- nenhum novo writer usa `_elementor_data` como destino.

## Baseline ambiental

Ambiente comprovado: WordPress 6.9.4 / PHP 8.5.10 / Elementor 4.1.0.

Corpus: **623 posts**:

- legacy_html 536;
- plain_text 41;
- elementor 34;
- mixed 5;
- gutenberg 4;
- empty 3.

## T096 — PASS AMBIENTAL

Evidência:
`evidence/g245-lossless-t096-summary-20260917T182930Z.json`.

SHA-256 bruto:
`7c71a436db3393fbf9be8f0add11fd32590d3a9804452e57173090e425925192`.

Resultado:

- 623/623 em duas passagens;
- errors/throwables/safety violations = 0;
- raw round-trip mismatch = 0;
- parse/serialize mismatch = 0;
- fidelity/serialization hash mismatch = 0;
- source ready = 615;
- source review_required = 5;
- not_applicable = 3;
- serialized_in_memory = 611;
- native_noop = 4;
- `core/freeform` = 611;
- `core/shortcode` = 1;
- 5 source `mixed` continuam fail-closed;
- corpus unchanged;
- fingerprint editorial igual;
- `t096_lossless_roundtrip_pass=true`.

## Decisão de migração lossless

Duas projeções complementares:

`fonte editorial -> Content Extractor -> Knowledge Document`  
Uso: busca, IA, hierarquia, qualidade e guardrail semântico.

`fonte editorial -> Migration Fidelity Source -> Lossless Core Block Serializer`  
Uso: migração sem perda.

Primeira canonicalização:

- Gutenberg nativo -> `native_noop`;
- legacy HTML/plain text -> `core/freeform` com raw exato;
- Elementor text-editor -> `core/freeform` com `settings.editor` exato;
- Elementor shortcode -> `core/shortcode` com texto exato;
- mixed/unsupported -> review_required.

## T097 — Static Editorial Parity

Contrato:
`core-block-editorial-parity-contract-v1.md`.

Runtime:

- `class-block-migration-stale-source-guard.php`;
- `class-core-block-editorial-parity.php`;
- `class-core-block-editorial-parity-smoke.php`.

Validação local: **20/20 assertions PASS + lint PASS**.

O gate ambiental verifica:

- `core/freeform` e `core/shortcode` registrados no Core;
- parsed block name correto;
- parsed `innerHTML` SHA-256 igual ao raw editorial original;
- `native_noop` byte-equivalent;
- stale-source por fidelity/source/material hashes;
- determinismo em duas passagens;
- corpus/fingerprint unchanged;
- sem render, shortcode execution, network ou persistência.

Pacote:

- `0.4.0-g245-parity-t097.1`;
- SHA-256 `79d82b6e8f7929247a4121c9ccbc2f94972902eab3039b12770b0714f99f11e0`;
- 44 PHP files lint PASS pré/pós ZIP;
- UX-002 byte parity PASS;
- writer/migration OFF.

## Próximo passo

Executar T097 em homologação. PASS esperado:

`gate_result.t097_static_editorial_parity_pass=true`.

Se PASS, T098 generaliza journal/dry-run/lock/batch para Block Migration. Só depois será produzido o Authorization Pack do primeiro canário mutável.

## Guardrails

- UX-002 não pode regredir;
- plugin Gutenberg não pode virar dependência silenciosa;
- Elementor não pode ser removido antes de dependência zero;
- KD não é fonte editorial lossless;
- raw payload não sai em evidência diagnóstica;
- mixed não é decidido automaticamente;
- gates read-only não renderizam shortcodes;
- nenhum writer está autorizado;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
