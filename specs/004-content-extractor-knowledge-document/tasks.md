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
- T092 Block Projection v1.1: **PASS LOCAL / READ-ONLY**, 25/25 assertions + lint.
- T093 full-corpus v1.1 + diagnóstico KD: **IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE**.
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

## Baseline ambiental atual

T091 foi executado em 2026-09-17 sobre **623 posts**. O baseline anterior de 622 permanece evidência histórica; o corpus ganhou 1 post antes do T091. Durante o T091 o corpus e o fingerprint editorial permaneceram estáveis.

Distribuição T091:

- 536 `legacy_html`;
- 41 `plain_text`;
- 34 `elementor`;
- 5 `mixed`;
- 4 `gutenberg`;
- 3 `empty`.

Plan status:

- 347 `projectable`;
- 269 `review_required`;
- 4 `native_noop`;
- 3 `not_applicable`.

Warnings observados:

- `KNOWLEDGE_DOCUMENT_REVIEW_REQUIRED`: 233;
- `BLOCK_PROJECTION_UNSUPPORTED_KIND:image`: 40;
- `BLOCK_PROJECTION_TABLE_SPAN_REVIEW`: 32;
- `BLOCK_PROJECTION_UNSUPPORTED_KIND:quote`: 8.

Evidência: `evidence/g245-block-projection-t091-20260917T172515Z.json`.
SHA-256 do JSON recebido: `1fa9fc1439634cb9ecca6a1caa0aac63e9f37366bc9c5656d2ea1b18eb3c6b93`.

## T091 — PASS AMBIENTAL

- duas passagens 623/623;
- errors 0;
- throwables 0;
- `block_projection_hash_mismatches=0`;
- `canonical_hash_mismatches=0`;
- safety violations 0;
- fingerprint editorial before/after idêntico;
- `gate_result.t091_block_projection_pass=true`;
- plugin Gutenberg dependency false.

## T092 — Block Projection v1.1 — PASS LOCAL

Contrato: `block-projection-contract-v1.1.md`.

Mudança baseada no corpus:

- `quote` → `core/quote`;
- `image` **não** foi liberado: KD 2.1 preserva alt text, mas não referência canônica de mídia suficiente para gerar `core/image` sem invenção;
- tabelas com `rowspan/colspan` continuam `review_required`;
- schema de Block Projection evolui para `1.1.0`.

Validação: **25/25 assertions PASS + PHP lint PASS**.

## T093 — Full-corpus v1.1 + diagnóstico KD

Runner atualizado em `includes/class-block-projection-plan-smoke.php`.

Além dos checks T091, T093 exporta somente métricas agregadas de:

- `knowledge_document_readiness`;
- `knowledge_document_reasons` para review/not_ready;
- `source_plan_matrix` por source kind × plan status;
- warnings e block names.

Safety permanece read-only: sem `serialize_blocks()`, sem persistência, sem IDs/conteúdo editorial exportados e sem dependência do plugin Gutenberg.

## Gates históricos reutilizáveis

Journal, stale-source, dry-run, batches, lock, readiness e rollback permanecem conceitos válidos. Nomes/classes Elementor-specific existentes são dívida nominal de transição e não autorizam writer Elementor.

## Próximos subgates

- [ ] executar T093 em homologação e versionar a evidência.
- [ ] T094: definir contrato de serialização Core Blocks a partir do diagnóstico T093; `serialize_blocks()` apenas in-memory.
- [ ] T095: round-trip `projection → serialize → parse → projeção semântica`, ainda sem persistência.
- [ ] T096: generalizar dry-run/journal/stale/lock/batches para Block Migration.
- [ ] T097: canário Block Migration em 1 artigo de homologação + rollback real, somente após Authorization Pack específico.
- [ ] T098: batches de migração homologados.
- [ ] T099: inventário de dependência residual Elementor e gate de retirada futura.
- [ ] G-250 Lifecycle/RC.

## Regras constitucionais

1. WordPress Core Blocks são o destino canônico futuro.
2. Plugin Gutenberg não é dependência de produção.
3. Elementor permanece até dependência zero; nunca é removido automaticamente.
4. Nenhum writer `_elementor_data` será implementado como destino.
5. Qualquer write em `post_content` exige gates e autorização explícitos.
6. Imagem sem proveniência de mídia não pode ser convertida silenciosamente.
7. UX-002 não pode regredir.
8. Trabalho incompleto permanece fora da `main`.
