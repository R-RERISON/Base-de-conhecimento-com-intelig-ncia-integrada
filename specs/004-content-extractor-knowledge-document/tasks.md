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
- T094 Editorial Fidelity Inventory: **PASS AMBIENTAL**.
- T095 Migration Fidelity Source v1: **PASS LOCAL / READ-ONLY**.
- T096 Lossless Core Block Serialization + round-trip: **PASS LOCAL / HOMOLOGAÇÃO PENDENTE**, 34/34 assertions + lint.
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

## Baseline ambiental — T093

Corpus: **623 posts**.

Source kinds: legacy_html 536; plain_text 41; elementor 34; mixed 5; gutenberg 4; empty 3.

Plan status: projectable 347; review_required 269; native_noop 4; not_applicable 3.

KD readiness: candidate_ready 387; review_required 233; not_applicable 3.

Principais families de reasons, em ocorrências agregadas:

- `HIERARCHY_AMBIGUOUS`: 964;
- `SHORTCODE_NOT_EXPANDED`: 55;
- `HTML_LOCAL_HEADING_FLATTENED`: 19;
- `HIERARCHY_NUMBERING_CONFLICT`: 6;
- `HTML_NESTED_LIST_IN_TABLE_FLATTENED`: 2.

T093 safety: duas passagens 623/623; errors/throwables/hash mismatches/safety violations 0; fingerprint editorial idêntico; `gate_result.t093_block_projection_pass=true`.

## T094 — Editorial Fidelity — PASS AMBIENTAL

Evidência: `evidence/g245-editorial-fidelity-t094-20260917T180802Z.json`.  
SHA-256 do JSON bruto recebido: `87e86ea84fdd7def0651a8218971d449aa21b85f82c3bcd521429ebee58f7923`.

Resultado sobre 623 posts, errors/throwables 0:

- `rich_html_source_required`: 467;
- `elementor_source_adapter_required`: 79, classificação conservadora por presença de meta;
- `shortcode_resolution_required`: 37;
- `kd_structure_sufficient_candidate`: 33;
- `native_core_blocks`: 4;
- `not_applicable`: 3.

Material editorial observado:

- 501 posts com links / 6.874 links;
- 346 posts com imagens / 4.595 imagens;
- 564 posts com inline formatting / 25.764 ocorrências;
- 1.394 styled spans;
- 1.319 line breaks;
- 513 tabelas, 463 células com rowspan e 13 com colspan;
- 53 posts com shortcodes;
- 80 posts com meta Elementor;
- 39 widgets Elementor `text-editor` e 1 `shortcode`;
- 1.011 links, 587 imagens e 3.357 rich-inline dentro de editor HTML Elementor.

Todas as 4.595 URLs de imagem do `post_content` ficaram `attachment_urls_unresolved`, portanto Media Library ID não pode ser presumido na migração.

Safety: read-only, fingerprint before/after igual, sem conteúdo/URLs/post IDs exportados, sem network/shortcode render e `t094_editorial_fidelity_pass=true`.

## Decisão T095/T096 — canonicalização lossless em duas etapas

O KD 2.1 permanece modelo semântico para busca/IA/guardrail; não é fonte editorial lossless.

### Etapa A — migração lossless

- Gutenberg existente → `native_noop`;
- legacy HTML/plain text → preservar `post_content` exato e serializar em `core/freeform`;
- Elementor `text-editor` → preservar `settings.editor` exato em `core/freeform`;
- Elementor `shortcode` → preservar `settings.shortcode` exato em `core/shortcode`;
- mixed/unsupported → `review_required` fail-closed.

### Etapa B — refinamento semântico posterior

Somente após paridade e migração segura, `core/freeform` poderá ser convertido progressivamente em paragraph/heading/list/table/image etc., usando KD como orientação. Isso não faz parte de T095/T096.

## T095 — Migration Fidelity Source v1 — PASS LOCAL

Contrato: `migration-fidelity-source-contract-v1.md`.

Runtime: `includes/class-migration-fidelity-source.php`.

Características:

- raw payload apenas em memória;
- SHA-256 por unidade e por canal-fonte;
- `fidelity_hash` determinístico;
- usa `source_kind` efetivo do Content Extractor para não confundir meta Elementor residual com fonte ativa;
- mixed exige seleção humana;
- nenhum write/network/render.

## T096 — Lossless Core Block Serialization — HOMOLOGAÇÃO

Contrato: `core-block-lossless-serialization-contract-v1.md`.

Runtime:

- `includes/class-core-block-lossless-serializer.php`;
- `includes/class-core-block-lossless-roundtrip-smoke.php`.

Validação local T095/T096 combinada: **34/34 assertions PASS + PHP lint PASS**.

O gate ambiental executa duas passagens e exige:

- todo corpus processado;
- zero errors/throwables/safety violations;
- zero raw payload round-trip mismatch;
- zero `parse_blocks -> serialize_blocks` mismatch;
- zero fidelity/serialization hash mismatch;
- Gutenberg existente byte-preserved em `native_noop`;
- corpus/fingerprint editorial unchanged;
- sem exportar conteúdo, URLs ou IDs;
- `gate_result.t096_lossless_roundtrip_pass=true`.

Pacote de homologação: `0.4.0-g245-lossless-t096.1`  
SHA-256: `5a2fc4ac31bfbe2b68cfe5f06d07057310760f54c9f5ecfc9fbc55b3b07ad961`.

Pacote: 41 PHP files lint PASS pré/pós ZIP; UX-002 byte parity PASS; T096 smoke ON; writers OFF.

## Próximos subgates

- [ ] executar T096 e versionar evidência.
- [ ] T097: definir/validar paridade renderizada e experiência editorial em cohort controlado, sem write global.
- [ ] T098: generalizar stale-source/journal/dry-run/lock/batch para Block Migration.
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
7. Payload editorial lossless não pode ser exportado por runners.
8. Mixed source não pode ser decidido automaticamente.
9. UX-002 não pode regredir.
10. Trabalho incompleto permanece fora da `main`.
