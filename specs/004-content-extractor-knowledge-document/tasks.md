# Tarefas — SPEC-004 Content Extractor e Knowledge Document

## Estado resumido

- R-200: PASS.
- R-210: PASS.
- G-220: PASS.
- G-230/v1: PASS de determinismo; v1 superseded for AI.
- G-240: **PASS / CLOSED / PROMOVIDO PARA `main`** com KD 2.1.0.
- G-245: **REBASELINED / IN PROGRESS** em `spec004-g245-production-readiness`; PR #4 DRAFT / NÃO MERGEAR.
- T080: PASS WITH REVIEW ITEMS.
- T081 Elementor Projection: PASS AMBIENTAL histórico/read-only; destino Elementor agora SUPERSEDED.
- T082–T086: PASS local/contratual; gates defensivos preservados para generalização.
- T083B Durable Journal Storage: **PASS AMBIENTAL**.
- T087A Readiness / T087B-prep Lock: PASS local/read-only.
- T087C writer Elementor: **CANCELADO / SUPERSEDED antes de implementação**.
- T088 Runbook: FROZEN; deve ser generalizado para Block Migration.
- ADR-004-001: **ACEITA** — WordPress Core Blocks como destino editorial canônico.
- T090 Block Projection Contract/Plan: **PASS LOCAL / READ-ONLY**, 23/23 assertions + lint.
- T091 Block Projection full-corpus smoke: **IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE**.
- G-250: NOT_RUN.

## Baseline visual obrigatória

UX-002 `0.4.0-ux002.3` permanece contrato visual obrigatório. G-245 não deve alterar os arquivos visuais canônicos sem UX-SPEC/aceite.

## Decisão arquitetural vigente

`adr/ADR-004-001-wordpress-core-blocks-canonical-editorial-target.md`:

- `WP_Post.post_content` + WordPress Core Blocks = destino editorial futuro;
- plugin Gutenberg = não dependência;
- somente APIs estáveis do Core homologado;
- Elementor = source adapter legado temporário;
- nenhum novo writer em `_elementor_data`;
- remoção do Elementor somente após dependência zero comprovada.

## Gates históricos preservados

### T080/T081

- Preflight e Projection Elementor forneceram diagnóstico real do corpus.
- T081: 622/622 em duas passagens, zero mutação, hashes/canonical JSON estáveis.
- Distribuição consolidada: 536 legacy_html, 41 plain_text, 34 elementor, 5 mixed, 4 gutenberg, 2 empty.

### T083B — Journal Durable Storage

- postmeta privado append-only `_bdc_kb_migration_journal`;
- round-trip byte-exato;
- integrity/readback/cleanup;
- smoke ambiental PASS;
- `gate.t083b_storage_pass=true`;
- `post_content` e `_elementor_data` inalterados.

### Gates reutilizáveis

Journal, stale-source, dry-run, batches, lock, readiness e runbook permanecem conceitos válidos. Nomes/classes Elementor-specific existentes são dívida nominal de transição e não autorizam writer Elementor.

## T090 — Block Projection Contract / Plan — PASS LOCAL

Artefatos:

- `block-projection-contract-v1.md`;
- `includes/class-block-projection-plan.php`;
- `tests/unit/spec004-block-projection-plan.php`.

Allowlist v1:

- heading → `core/heading`;
- paragraph → `core/paragraph`;
- code → `core/code`;
- list → `core/list` + `core/list-item`;
- table simples → `core/table`.

Regras:

- source `gutenberg` pronto → `native_noop`;
- tipos não suportados → `review_required`;
- table rowspan/colspan → `review_required`;
- KD `not_ready` → `blocked`;
- empty → `not_applicable`;
- `block_projection_hash` determinístico;
- `serialized_post_content=null`;
- writer/migration/persistence/network/shortcode/dynamic render = false;
- não depende do plugin Gutenberg.

Validação local: **23/23 assertions PASS + lint PASS**.

## T091 — Block Projection full-corpus smoke

Runner implementado em `includes/class-block-projection-plan-smoke.php`.

Build de homologação:

- `0.4.0-g245-block-projection-smoke.1`;
- SHA-256 `d70b518bc20825a5d64984aef555f67a270ec065587c00bfefa3caad3e67028c`;
- 37 PHP files lint PASS antes e após reextração;
- UX-002 byte parity PASS;
- antigo Elementor Projection smoke disabled;
- Journal smoke disabled;
- Block Projection smoke enabled apenas no pacote de homologação;
- writer Elementor false.

T091 deve executar duas passagens e provar:

- corpus completo nas duas passagens;
- zero errors/throwables;
- zero `block_projection_hash` mismatch;
- zero canonical hash mismatch;
- zero safety violations;
- distribuição por source/status/warnings/block names;
- fingerprint editorial before/after idêntico;
- zero dependência do plugin Gutenberg;
- `gate_result.t091_block_projection_pass=true`.

## Próximos subgates

- [ ] executar T091 em homologação e versionar a evidência.
- [ ] T092: análise de gaps/allowlist a partir da evidência real; sem ampliar allowlist por hipótese.
- [ ] T093: contrato de serialização Core Blocks + round-trip `serialize_blocks`/`parse_blocks`, ainda sem persistência.
- [ ] T094: generalizar dry-run/journal/stale/lock/batches para Block Migration e remover acoplamento nominal Elementor quando seguro.
- [ ] T095: canário Block Migration em 1 artigo de homologação + rollback real, somente após Authorization Pack específico.
- [ ] T096: batches de migração homologados.
- [ ] T097: inventário de dependência residual Elementor e gate de retirada futura.
- [ ] G-250 Lifecycle/RC.

## Regras constitucionais

1. WordPress Core Blocks são o destino canônico futuro.
2. Plugin Gutenberg não é dependência de produção.
3. Elementor permanece até dependência zero; nunca é removido automaticamente.
4. Nenhum writer `_elementor_data` será implementado como destino.
5. Qualquer write em `post_content` exige gates e autorização explícitos.
6. UX-002 não pode regredir.
7. Trabalho incompleto permanece fora da `main`.
