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
- T094 Editorial Fidelity: **PASS AMBIENTAL**.
- T095 Migration Fidelity Source v1: **PASS LOCAL / READ-ONLY**.
- T096 Lossless Core Block Serialization: **PASS LOCAL / HOMOLOGAÇÃO PENDENTE**.
- G-250: NOT_RUN.

## Arquitetura editorial vigente

Canônica futura: `WP_Post.post_content` + WordPress Core Blocks.

- plugin Gutenberg não é dependência;
- Elementor é source adapter legado/read-only durante transição;
- `_elementor_data` é preservado enquanto houver dependência;
- nenhum novo writer usa `_elementor_data` como destino.

## Baseline ambiental atual

Ambiente comprovado: WordPress 6.9.4 / PHP 8.5.10 / Elementor 4.1.0.

Corpus: **623 posts**.

- legacy_html 536;
- plain_text 41;
- elementor 34;
- mixed 5;
- gutenberg 4;
- empty 3.

T093 comprovou duas passagens 623/623 com errors/throwables/hash mismatches/safety violations = 0 e fingerprint editorial idêntico.

## T094 — Editorial Fidelity — PASS AMBIENTAL

Evidência: `evidence/g245-editorial-fidelity-t094-20260917T180802Z.json`.

SHA-256 bruto: `87e86ea84fdd7def0651a8218971d449aa21b85f82c3bcd521429ebee58f7923`.

Distribuição:

- rich_html_source_required 467;
- elementor_source_adapter_required 79;
- shortcode_resolution_required 37;
- kd_structure_sufficient_candidate 33;
- native_core_blocks 4;
- not_applicable 3.

O inventário confirmou que o KD sozinho não pode alimentar uma migração lossless:

- 6.874 links em 501 posts;
- 4.595 imagens em 346 posts;
- 25.764 ocorrências de inline formatting em 564 posts;
- 1.394 styled spans;
- 1.319 line breaks;
- 513 tabelas, 463 rowspan e 13 colspan;
- 53 posts com shortcodes;
- 80 posts com meta Elementor;
- 39 widgets Elementor text-editor + 1 shortcode;
- editor HTML Elementor contém 1.011 links, 587 imagens e 3.357 inline-formatting.

`attachment_urls_resolved=0` e `attachment_urls_unresolved=4595`: nenhum Media Library ID pode ser presumido automaticamente.

T094 safety: errors/throwables 0, corpus unchanged, fingerprint igual, sem exportar conteúdo/URLs/post IDs, sem network/render e `t094_editorial_fidelity_pass=true`.

## Decisão de migração lossless

A arquitetura agora possui duas projeções complementares:

`fonte editorial -> Content Extractor -> Knowledge Document`  
para busca, IA, hierarquia e guardrail semântico.

`fonte editorial -> Migration Fidelity Source -> Lossless Core Block Serializer`  
para migração sem perda.

### Primeira canonicalização

- Gutenberg nativo → preservar exatamente (`native_noop`);
- legacy HTML/plain text → `core/freeform` com payload exato;
- Elementor text-editor → `core/freeform` com `settings.editor` exato;
- Elementor shortcode → `core/shortcode` com texto exato;
- mixed/unsupported → review_required.

O objetivo desta etapa é tirar o conteúdo de estruturas proprietárias sem tentar reconstruir semanticamente todo HTML rico em uma única migração.

Refinamento de `core/freeform` para blocos mais semânticos será etapa posterior e independente.

## T095 — Migration Fidelity Source v1

Contrato: `migration-fidelity-source-contract-v1.md`.

Runtime: `includes/class-migration-fidelity-source.php`.

- raw payload somente em memória;
- hashes por unidade/canal;
- `fidelity_hash` determinístico;
- source kind efetivo decide a fonte ativa;
- meta Elementor residual não vence automaticamente `post_content`;
- mixed fail-closed;
- writer/network/render = false.

## T096 — Lossless Core Block Serialization

Contrato: `core-block-lossless-serialization-contract-v1.md`.

Runtime:

- `includes/class-core-block-lossless-serializer.php`;
- `includes/class-core-block-lossless-roundtrip-smoke.php`.

Validação local combinada T095/T096: **34/34 assertions PASS + PHP lint PASS**.

T096 usa `serialize_blocks()` e `parse_blocks()` somente em memória e exige raw payload byte-equivalent por SHA-256, reserialização determinística e zero mutação editorial.

Pacote de homologação:

- build `0.4.0-g245-lossless-t096.1`;
- SHA-256 `5a2fc4ac31bfbe2b68cfe5f06d07057310760f54c9f5ecfc9fbc55b3b07ad961`;
- 41 PHP files lint PASS pré/pós ZIP;
- UX-002 byte parity PASS;
- T094 smoke OFF;
- T096 smoke ON;
- writer/migration OFF.

## Próximo passo

Executar T096 em homologação e devolver o JSON.

Gate esperado:

`gate_result.t096_lossless_roundtrip_pass=true`.

Se PASS, T097 passa a tratar **paridade renderizada/editorial**, não mais fidelidade de armazenamento.

## Guardrails

- UX-002 não pode regredir;
- plugin Gutenberg não pode virar dependência silenciosa;
- Elementor não pode ser removido antes de dependência zero;
- KD não pode ser usado como fonte editorial lossless;
- payload bruto de migração não pode sair em evidência diagnóstica;
- nenhuma migração automática em activation/update;
- nenhum writer está autorizado;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
