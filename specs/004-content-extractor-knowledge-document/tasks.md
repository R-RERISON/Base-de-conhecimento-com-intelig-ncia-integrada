# Tarefas — SPEC-004 Content Extractor e Knowledge Document

## Estado resumido

- R-200: PASS.
- R-210: PASS.
- G-220: PASS.
- G-230/v1: PASS de determinismo; v1 superseded for AI.
- G-240: **PASS / CLOSED / PROMOVIDO PARA `main`** com KD 2.1.0.
- G-245: **REBASELINED / IN PROGRESS** em `spec004-g245-production-readiness`; PR #4 DRAFT / NÃO MERGEAR.
- ADR-004-001: **ACEITA** — WordPress Core Blocks como destino editorial canônico.
- T083B Durable Journal Storage: **PASS AMBIENTAL**.
- T087C writer Elementor: **CANCELADO / SUPERSEDED antes de implementação**.
- T090 Block Projection v1.0: **PASS LOCAL / READ-ONLY**.
- T091 Block Projection full-corpus: **PASS AMBIENTAL**.
- T092 Block Projection v1.1: **PASS LOCAL / READ-ONLY**.
- T093 full-corpus + diagnóstico KD: **PASS AMBIENTAL**.
- T094 Editorial Fidelity Inventory: **PASS AMBIENTAL**.
- T095 Migration Fidelity Source v1: **PASS LOCAL / READ-ONLY**.
- T096 Lossless Core Block Serialization + round-trip: **PASS AMBIENTAL**.
- T097 Static Editorial Parity + generic stale-source: **PASS AMBIENTAL**.
- T098 Block Migration Protection: **PASS LOCAL / HOMOLOGAÇÃO PENDENTE**, 24/24 assertions + lint.
- G-250: NOT_RUN.

## Baseline visual obrigatória

UX-002 `0.4.0-ux002.3` permanece contrato visual obrigatório. G-245 não altera arquivos visuais canônicos sem UX-SPEC/aceite.

## Arquitetura editorial vigente

- `WP_Post.post_content` + WordPress Core Blocks = destino editorial futuro;
- plugin Gutenberg = não dependência;
- Elementor = source adapter legado temporário;
- nenhum novo writer em `_elementor_data`;
- KD 2.1 = modelo semântico/IA, não representação editorial lossless;
- Migration Fidelity Source + Lossless Core Serializer = trilha de migração fiel.

## Baseline ambiental — T096/T097

Corpus: **623 posts**.

Source kinds: legacy_html 536; plain_text 41; elementor 34; mixed 5; gutenberg 4; empty 3.

T096:

- source `ready`: 615;
- `review_required`: 5;
- `not_applicable`: 3;
- `serialized_in_memory`: 611;
- `native_noop`: 4;
- zero errors/throwables/safety violations;
- zero raw round-trip / parse-serialize / hash mismatches;
- `t096_lossless_roundtrip_pass=true`.

T097:

- Core registry: `core/freeform` e `core/shortcode` presentes;
- parity `pass`: 611;
- `native_noop`: 4;
- `not_applicable`: 3;
- `review_required`: 5;
- stale `fresh`: 623;
- zero parity mismatch, zero stale source, zero manifest hash mismatch;
- fingerprint editorial before/after idêntico;
- `t097_static_editorial_parity_pass=true`.

Evidência:
`evidence/g245-editorial-parity-t097-20260917T184418Z.json`.

SHA-256 do JSON bruto recebido:
`6466fb0830d9ba65e79da9dc69b90da6a08fb4286391517eb0663849b10babec`.

## T098 — Block Migration Protection

Contrato: `block-migration-protection-contract-v1.md`.

Runtime:

- `class-block-migration-journal.php`;
- `class-block-migration-journal-store.php`;
- `class-block-migration-dry-run.php`;
- `class-block-migration-batch-plan.php`;
- `class-block-migration-lock.php`;
- `class-block-migration-readiness-smoke.php`.

Validação local: **24/24 assertions PASS** + lint das novas classes.

### Invariantes

- nenhum writer foi implementado;
- `execution_allowed=false`;
- `writer_allowed=false`;
- `migration_execution_allowed=false`;
- source `mixed` continua review;
- journal deve preceder write futuro;
- stale recheck deve ocorrer imediatamente antes de write futuro;
- lock exclusivo obrigatório;
- rollback bloqueia alvo alterado;
- batch planner usa cursor versionado, cohort hash, sem duplicidade;
- T098 smoke não persiste journal e não adquire lock.

Pacote de homologação:

- `0.4.0-g245-readiness-t098.1`;
- SHA-256 `df08c57d9c7b7c6df8a66b026ebec1bd6c3d16e993c92ea65afac122e6d63ddf`;
- 50 PHP files lint PASS pré/pós ZIP;
- UX-002 byte parity PASS;
- T097 smoke OFF;
- T098 smoke ON;
- writers OFF.

## Próximos subgates

- [ ] executar T098 e versionar evidência.
- [ ] T099A: smoke mutável apenas do novo journal store + lock em 1 artigo de homologação, com cleanup imediato e sem write editorial.
- [ ] T099B: selecionar automaticamente 1 `legacy_html` de baixo risco e gerar Authorization Pack completo.
- [ ] T099C: canário real de 1 artigo + verificação + rollback comprovado.
- [ ] T100: batches homologados.
- [ ] T101: inventário de dependência residual Elementor e gate de retirada futura.
- [ ] G-250 Lifecycle/RC.

## Regras constitucionais

1. WordPress Core Blocks são o destino canônico futuro.
2. Plugin Gutenberg não é dependência de produção.
3. Elementor permanece até dependência zero; nunca é removido automaticamente.
4. Nenhum writer `_elementor_data` será implementado como destino.
5. Qualquer write em `post_content` exige gates e autorização explícitos.
6. KD não pode ser tratado como representação editorial lossless.
7. Payload editorial lossless não pode sair em runners.
8. Mixed source não pode ser decidido automaticamente.
9. UX-002 não pode regredir.
10. Trabalho incompleto permanece fora da `main`.
