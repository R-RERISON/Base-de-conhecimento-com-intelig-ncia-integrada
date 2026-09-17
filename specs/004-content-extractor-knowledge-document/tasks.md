# Tarefas — SPEC-004 Content Extractor e Knowledge Document

## Estado resumido

- R-200: PASS.
- R-210: PASS.
- G-220: PASS.
- G-230/v1: PASS de determinismo; v1 superseded for AI.
- G-240: PASS/CLOSED/main com KD 2.1.0.
- G-245: REBASELINED / IN PROGRESS; PR #4 DRAFT / NÃO MERGEAR.
- ADR-004-001: ACEITA — WordPress Core Blocks como destino editorial canônico.
- T083B Durable Journal Storage: PASS AMBIENTAL.
- T087C writer Elementor: CANCELADO / SUPERSEDED antes de implementação.
- T090: PASS LOCAL / READ-ONLY.
- T091: PASS AMBIENTAL.
- T092: PASS LOCAL / READ-ONLY.
- T093: PASS AMBIENTAL.
- T094: PASS AMBIENTAL.
- T095 Migration Fidelity Source v1: PASS LOCAL / READ-ONLY.
- T096 Lossless Core Block Serialization: PASS AMBIENTAL.
- T097 Static Editorial Parity + stale-source: PASS AMBIENTAL.
- T098 Block Migration Protection: PASS LOCAL / HOMOLOGAÇÃO PENDENTE, 24/24 assertions.
- G-250: NOT_RUN.

## Baseline visual

UX-002 `0.4.0-ux002.3` permanece contrato visual obrigatório.

## Arquitetura

- `WP_Post.post_content` + WordPress Core Blocks = destino editorial canônico futuro.
- plugin Gutenberg = não dependência.
- Elementor = source adapter legado temporário.
- nenhum writer `_elementor_data` como destino.
- KD = modelo semântico/IA/guardrail.
- Migration Fidelity Source + Lossless Serializer = trilha editorial lossless.

## T097 — PASS AMBIENTAL

Evidência: `evidence/g245-editorial-parity-t097-20260917T184418Z.json`.
SHA-256 bruto: `6466fb0830d9ba65e79da9dc69b90da6a08fb4286391517eb0663849b10babec`.

Resultado: 623/623 em duas passagens; Core registry PASS; errors/throwables/safety/parity/stale/manifest mismatches 0; parity pass 611; native_noop 4; not_applicable 3; review_required 5; stale fresh 623; fingerprint editorial unchanged; `t097_static_editorial_parity_pass=true`.

## T098 — Block Migration Protection

Contrato: `block-migration-protection-contract-v1.md`.

Runtime:

- `class-block-migration-journal.php`;
- `class-block-migration-journal-store.php`;
- `class-block-migration-dry-run.php`;
- `class-block-migration-batch-plan.php`;
- `class-block-migration-lock.php`;
- `class-block-migration-readiness-smoke.php`.

Local: 24/24 assertions PASS + lint.

Invariantes:

- writer/execution/migration allowed = false;
- journal obrigatório antes de write futuro;
- stale recheck imediatamente antes de write futuro;
- lock exclusivo obrigatório;
- rollback stale bloqueado;
- source mixed permanece review;
- batch cursor versionado, cohort hash e zero duplicidade;
- T098 smoke não persiste journal e não adquire lock.

Pacote:

- `0.4.0-g245-readiness-t098.1`;
- SHA-256 `df08c57d9c7b7c6df8a66b026ebec1bd6c3d16e993c92ea65afac122e6d63ddf`;
- 50 PHP lint PASS pré/pós ZIP;
- UX-002 byte parity PASS;
- writers OFF.

## Próximos subgates

- [ ] executar T098 e versionar evidência.
- [ ] T099A: journal store + lock smoke com cleanup, sem write editorial.
- [ ] T099B: selecionar 1 `legacy_html` de baixo risco e gerar Authorization Pack.
- [ ] T099C: canário real de 1 artigo + rollback, somente com autorização específica.
- [ ] T100: batches homologados.
- [ ] T101: dependência residual Elementor / gate de retirada.
- [ ] G-250 Lifecycle/RC.

## Regras

1. Core Blocks são o destino canônico.
2. Plugin Gutenberg não é requisito.
3. Elementor permanece até dependência zero.
4. Nenhum writer `_elementor_data` será implementado como destino.
5. Write em `post_content` exige gates e autorização explícitos.
6. KD não é representação editorial lossless.
7. Raw payload não sai em runners.
8. Mixed não é decidido automaticamente.
9. UX-002 não pode regredir.
10. Trabalho incompleto permanece fora de `main`.
