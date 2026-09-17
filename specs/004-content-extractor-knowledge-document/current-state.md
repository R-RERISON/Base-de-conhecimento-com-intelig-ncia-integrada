# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline e gates

- SPEC-001/002/003: concluídas.
- UX-001/UX-002: concluídas; UX-002 `0.4.0-ux002.3` é contrato visual obrigatório.
- G-240: PASS / CLOSED / promovido para `main`.
- KD 2.1.0: PASS técnico full-corpus + PASS humano 8/8.
- G-245: **REBASELINED / IN PROGRESS** em `spec004-g245-production-readiness`; PR #4 DRAFT / NÃO MERGEAR.
- ADR-004-001: ACEITA — Core Blocks como destino editorial canônico.
- T091/T093/T094/T096/T097: PASS AMBIENTAL.
- T095: PASS LOCAL / READ-ONLY.
- T098.1 Block Migration Readiness: **FAIL CONTROLADO / SEM MUTAÇÃO**.
- T098.2 Block Migration Readiness: **CORRIGIDO / REHOMOLOGAÇÃO PENDENTE**.
- G-250: NOT_RUN.

## Arquitetura editorial vigente

Canônica futura: `WP_Post.post_content` + WordPress Core Blocks.

- plugin Gutenberg não é dependência;
- Elementor é source adapter legado/read-only durante transição;
- `_elementor_data` é preservado enquanto houver dependência;
- nenhum novo writer usa `_elementor_data` como destino;
- KD é modelo semântico, não representação editorial lossless;
- migração usa `Migration Fidelity Source -> Lossless Core Block Serializer`.

## T097 — PASS AMBIENTAL

Ambiente: WordPress 6.9.4 / PHP 8.5.10. Corpus: 623 posts.

- Core Block Registry: `core/freeform` e `core/shortcode` presentes;
- duas passagens 623/623;
- errors/throwables/safety/parity/stale/manifest mismatches = 0;
- parity pass 611;
- native_noop 4;
- not_applicable 3;
- review_required 5;
- stale fresh 623;
- fingerprint editorial before/after idêntico;
- `t097_static_editorial_parity_pass=true`.

Evidência: `evidence/g245-editorial-parity-t097-20260917T184418Z.json`.

## T098.1 — FAIL CONTROLADO

Evidência: `evidence/g245-t098-readiness-fail-20260917T190620Z.json`.
SHA-256 bruto: `b85e22c338d72f52dc11b3d113058618108b9dd20778b67a3062839c8eaba44d`.

Resultado ambiental:

- corpus 623;
- first/second pass = 0;
- first/second throwables = 623;
- safety violations = 0;
- corpus unchanged;
- fingerprint editorial unchanged;
- `t098_block_migration_readiness_pass=false`.

Causa raiz: mismatch de integração entre o novo dry-run e os contratos T097 já homologados:

1. chamada inexistente `Core_Block_Editorial_Parity::validate()`; contrato real: `assess(source, serialization, parsed_blocks)`;
2. chamada inexistente `Block_Migration_Stale_Source_Guard::inspect_post()`; contrato real: `assess(planned, current)` / `assert_fresh()`;
3. expectativa indevida do campo `is_fresh`; contrato real usa `status=fresh|stale`.

Não houve falha arquitetural nem regressão editorial.

## T098.2 — correção

Pipeline corrigido:

`Migration Fidelity Source -> Lossless Serializer -> parse_blocks() -> Editorial_Parity::assess() -> rebuild current Migration Fidelity Source -> Stale_Source_Guard::assess()`.

Freshness passa a ser decidida por `status=fresh`.

O runner também exporta assinatura agregada de throwable (`classe + basename:linha + hash curto da mensagem`) sem conteúdo editorial/URL/post ID.

Validação local:

- 28/28 assertions PASS;
- 50 PHP files lint PASS pré/pós ZIP;
- UX-002 byte parity PASS;
- writer/migration OFF.

Pacote: `0.4.0-g245-readiness-t098.2`.
SHA-256: `a37ebc3ff018f71c96546e56e2b8434db53ea148b7d9d253db0332b1d389cef7`.

## Próximo passo

Reexecutar T098.2 em homologação. PASS esperado: `gate_result.t098_block_migration_readiness_pass=true`.

Se PASS: T099A journal-store + lock smoke com cleanup e sem write editorial; depois T099B Authorization Pack de 1 `legacy_html` de baixo risco; somente então T099C canário real com autorização específica.

## Guardrails

- UX-002 não pode regredir;
- plugin Gutenberg não vira dependência;
- Elementor não é removido antes de dependência zero;
- source mixed permanece humano;
- nenhum writer está autorizado;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
