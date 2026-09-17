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
- T098 Block Migration Protection: **PASS LOCAL / HOMOLOGAÇÃO PENDENTE**.
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

Ambiente: WordPress 6.9.4 / PHP 8.5.10.
Corpus: 623 posts.

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
SHA-256 bruto: `6466fb0830d9ba65e79da9dc69b90da6a08fb4286391517eb0663849b10babec`.

## T098 — Block Migration Protection

Contrato: `block-migration-protection-contract-v1.md`.

Novas primitivas Core Blocks:

- `Block_Migration_Journal`;
- `Block_Migration_Journal_Store`;
- `Block_Migration_Dry_Run`;
- `Block_Migration_Batch_Plan`;
- `Block_Migration_Lock`;
- `Block_Migration_Readiness_Smoke`.

Validação local: **24/24 assertions PASS** e lint PASS.

O gate T098 permanece read-only. O smoke:

- constrói dry-runs full-corpus;
- prepara journals apenas em memória;
- valida stale-source;
- percorre batches de 25 com cursor íntegro;
- exige zero duplicidade e cobertura integral;
- não persiste journal;
- não adquire lock;
- não escreve `post_content`/`_elementor_data`;
- não renderiza blocos/shortcodes;
- não exporta conteúdo/URLs/post IDs.

Pacote: `0.4.0-g245-readiness-t098.1`.
SHA-256: `df08c57d9c7b7c6df8a66b026ebec1bd6c3d16e993c92ea65afac122e6d63ddf`.

50 PHP files lint PASS pré/pós ZIP; UX-002 byte parity PASS.

## Próximo passo

Executar T098. PASS esperado: `gate_result.t098_block_migration_readiness_pass=true`.

Se PASS: T099A journal-store + lock smoke com cleanup e sem write editorial; depois T099B Authorization Pack de 1 `legacy_html` de baixo risco; somente então T099C canário real com autorização específica.

## Guardrails

- UX-002 não pode regredir;
- plugin Gutenberg não vira dependência;
- Elementor não é removido antes de dependência zero;
- source mixed permanece humano;
- nenhum writer está autorizado;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
