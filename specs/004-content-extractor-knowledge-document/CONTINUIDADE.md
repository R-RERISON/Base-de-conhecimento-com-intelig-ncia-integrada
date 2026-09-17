# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Repositório / branch

- Repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`.
- Branch ativa: `spec004-g245-production-readiness`.
- PR #4: DRAFT / NÃO MERGEAR.
- SPEC ativa: SPEC-004.

Antes de qualquer alteração, reler `AGENTS.md`, `.specify/PROJECT_MANIFEST.md`, `.specify/memory/constitution.md`, SPEC-004, ADRs vigentes e `docs/DEFINITION-OF-DONE.md`.

## Estado atual

- UX-002 `0.4.0-ux002.3`: PASS/CLOSED, contrato visual obrigatório.
- G-240: PASS/CLOSED/main.
- KD 2.1.0: PASS técnico + humano 8/8.
- ADR-004-001: ACEITA — Core Blocks como destino editorial canônico.
- T083B Durable Journal Storage: PASS AMBIENTAL.
- T091/T093/T094/T096/T097: PASS AMBIENTAL.
- T095 Migration Fidelity Source v1: PASS LOCAL / READ-ONLY.
- T098 Block Migration Protection: **PASS LOCAL / HOMOLOGAÇÃO PENDENTE**, 24/24 assertions.
- Nenhum writer/migration está autorizado.

## Decisão arquitetural vigente

`WP_Post.post_content` + WordPress Core Blocks são o destino editorial canônico futuro.

- plugin Gutenberg NÃO é dependência de produção;
- somente APIs estáveis do WordPress Core homologado;
- Elementor é fonte legada temporária/read-only;
- `_elementor_data` deve ser preservado durante a transição;
- nenhum writer futuro usa `_elementor_data` como destino;
- KD é guardrail semântico, não fonte editorial lossless.

Constituição da branch: v1.3.0.

## T097 — PASS AMBIENTAL

Evidência:
`evidence/g245-editorial-parity-t097-20260917T184418Z.json`.

SHA-256 do JSON bruto:
`6466fb0830d9ba65e79da9dc69b90da6a08fb4286391517eb0663849b10babec`.

Resultado:

- corpus 623;
- duas passagens 623/623;
- Core Registry com `core/freeform` e `core/shortcode` presente;
- errors 0;
- throwables 0;
- safety violations 0;
- parity mismatches 0;
- stale sources 0;
- manifest hash mismatches 0;
- parity pass 611;
- native_noop 4;
- not_applicable 3;
- review_required 5;
- stale fresh 623;
- fingerprint editorial before/after igual;
- `gate_result.t097_static_editorial_parity_pass=true`.

## T098 — Block Migration Protection

Contrato:
`block-migration-protection-contract-v1.md`.

Runtime novo:

- `includes/class-block-migration-journal.php`;
- `includes/class-block-migration-journal-store.php`;
- `includes/class-block-migration-dry-run.php`;
- `includes/class-block-migration-batch-plan.php`;
- `includes/class-block-migration-lock.php`;
- `includes/class-block-migration-readiness-smoke.php`.

Validação local:

- 24/24 assertions PASS;
- PHP lint PASS das novas classes;
- rollback stale bloqueado;
- journal adulterado bloqueado;
- dry-run stale bloqueado;
- source review permanece review;
- native Core Blocks viram noop;
- batch planner cobre cohort sem duplicidade;
- cursor adulterado/stale bloqueado.

O smoke T098 é full-corpus e read-only. Ele prepara journal somente em memória e não chama:

- `Block_Migration_Journal_Store::persist_prepared()`;
- `Block_Migration_Lock::acquire()`;
- qualquer writer.

Ele exige duas passagens determinísticas, batch size 25, cobertura integral do cohort, zero duplicidade, zero cursor failure e fingerprint editorial unchanged.

## Pacote T098

- `0.4.0-g245-readiness-t098.1`;
- SHA-256 `df08c57d9c7b7c6df8a66b026ebec1bd6c3d16e993c92ea65afac122e6d63ddf`;
- 50 PHP files lint PASS pré/pós ZIP;
- UX-002 byte parity PASS;
- T097 smoke OFF;
- T098 smoke ON;
- writer OFF.

## Próximo passo exato

1. instalar T098 em homologação;
2. abrir `Base de Conhecimento > Block Migration Readiness G-245`;
3. executar `Executar T098 e baixar JSON`;
4. devolver o JSON;
5. versionar evidência;
6. se PASS, T099A testa somente journal store + lock com cleanup, sem write editorial;
7. T099B seleciona 1 `legacy_html` de baixo risco e gera Authorization Pack;
8. somente depois T099C pode executar canário real, com autorização específica e rollback.

PASS esperado:

`gate_result.t098_block_migration_readiness_pass=true`.

## Guardrails absolutos

- não mexer na UX-002 sem UX-SPEC;
- não instalar/declarar plugin Gutenberg como requisito;
- não remover Elementor agora;
- não escrever `_elementor_data`;
- não escrever `post_content` sem Authorization Pack específico;
- não usar KD como fonte editorial lossless;
- não exportar raw payload em runners;
- não decidir mixed source automaticamente;
- não executar shortcodes nos gates read-only;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
