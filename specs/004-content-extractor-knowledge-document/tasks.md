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
- T093 Block Projection v1.1 full-corpus + diagnóstico KD: **PASS AMBIENTAL**.
- T094 Editorial Fidelity Contract/Inventory: **IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE**, 11/11 assertions + lint.
- G-250: NOT_RUN.

## Baseline visual obrigatória

UX-002 `0.4.0-ux002.3` permanece contrato visual obrigatório. G-245 não deve alterar arquivos visuais canônicos sem UX-SPEC/aceite.

## Arquitetura editorial vigente

- `WP_Post.post_content` + WordPress Core Blocks = destino editorial futuro;
- plugin Gutenberg = não dependência;
- somente APIs estáveis do WordPress Core homologado;
- Elementor = source adapter legado temporário;
- nenhum novo writer em `_elementor_data`;
- remoção do Elementor somente após dependência zero comprovada.

## Baseline ambiental atual — T093

Corpus: **623 posts**.

Source kinds:

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

Knowledge Document readiness:

- candidate_ready 387;
- review_required 233;
- not_applicable 3.

Principais famílias de reasons do KD, em ocorrências agregadas:

- `HIERARCHY_AMBIGUOUS`: 964;
- `SHORTCODE_NOT_EXPANDED`: 55;
- `HTML_LOCAL_HEADING_FLATTENED`: 19;
- `HIERARCHY_NUMBERING_CONFLICT`: 6;
- `HTML_NESTED_LIST_IN_TABLE_FLATTENED`: 2.

Warnings de Block Projection:

- `KNOWLEDGE_DOCUMENT_REVIEW_REQUIRED`: 233;
- `BLOCK_PROJECTION_UNSUPPORTED_KIND:image`: 40;
- `BLOCK_PROJECTION_TABLE_SPAN_REVIEW`: 32.

`quote` já foi absorvido por `core/quote`; T093 projetou 13 quotes.

T093 safety:

- duas passagens 623/623;
- errors/throwables 0;
- hash/canonical mismatches 0;
- safety violations 0;
- fingerprint editorial before/after idêntico;
- `gate_result.t093_block_projection_pass=true`.

Evidência resumida: `evidence/g245-block-projection-t093-summary-20260917T174835Z.json`.  
SHA-256 do JSON bruto recebido: `db04ba6ac564c9471e00491987ae2a8d8125a2c9b7462aca9dc55e6259e8da43`.

## Descoberta pré-serializer

O KD 2.1 é um modelo semântico, não um modelo editorial lossless.

O adapter legado reduz vários elementos a texto visível. Links, mídia e rich inline não são preservados no KD com fidelidade suficiente para um writer. Imagem mantém alt + ID sintético, mas não `src`/attachment reference canônica.

Portanto, serializer direto `KD -> Core Blocks` está **BLOQUEADO** por risco de regressão editorial.

## T094 — Editorial Fidelity

Contrato: `editorial-fidelity-contract-v1.md`.

Runner: `includes/class-editorial-fidelity-inventory-smoke.php`.

Inventário agregado, read-only, sem conteúdo/URLs/post IDs:

- links/hrefs;
- imagens/src e resolução para attachment por contagem;
- rich inline tags;
- styled spans;
- figures/figcaptions/BR;
- rowspan/colspan;
- shortcodes;
- dependência Elementor;
- widgets Elementor relevantes;
- matriz source × fidelity class.

Classes diagnósticas:

- native_core_blocks;
- not_applicable;
- elementor_source_adapter_required;
- shortcode_resolution_required;
- rich_html_source_required;
- complex_table_source_required;
- kd_structure_sufficient_candidate.

Validação local: **11/11 assertions PASS + PHP lint PASS**.

Pacote de homologação: `0.4.0-g245-editorial-fidelity-t094.1`  
SHA-256: `e25494a8c7be9bc2103e421ab7698d4a2f1114aea85fa2efdb0811a5a48f2caf`.

## Próximos subgates

- [ ] executar T094 e versionar evidência.
- [ ] T095: definir `Migration Fidelity Source v1` a partir da distribuição real T094.
- [ ] T096: Core Block Serializer in-memory para cohort seguro, usando fonte editorial rica + KD como guardrail.
- [ ] T097: round-trip `migration source -> serialize_blocks -> parse_blocks -> semantic/fidelity compare`, sem persistência.
- [ ] T098: generalizar dry-run/journal/stale/lock/batches para Block Migration.
- [ ] T099: canário de 1 artigo + rollback real com Authorization Pack específico.
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
7. Imagem sem proveniência de mídia não pode ser convertida silenciosamente.
8. UX-002 não pode regredir.
9. Trabalho incompleto permanece fora da `main`.
