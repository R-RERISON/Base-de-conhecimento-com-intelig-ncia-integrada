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
- T091: PASS AMBIENTAL.
- T092: PASS LOCAL.
- T093: PASS AMBIENTAL.
- T094 Editorial Fidelity: **PASS AMBIENTAL**.
- T095 Migration Fidelity Source v1: **PASS LOCAL / READ-ONLY**.
- T096 Lossless Core Block Serialization + round-trip: **PASS LOCAL / HOMOLOGAÇÃO PENDENTE**, 34/34 local.
- Nenhum writer/migration está autorizado.

## Decisão arquitetural vigente

`WP_Post.post_content` + WordPress Core Blocks são o destino editorial canônico futuro.

- plugin Gutenberg NÃO é dependência de produção;
- somente APIs estáveis do WordPress Core homologado;
- Elementor é fonte legada temporária/read-only;
- `_elementor_data` deve ser preservado durante a transição;
- nenhum writer futuro usa `_elementor_data` como destino.

Constituição da branch: v1.3.0.

## Evidência T094

Arquivo:
`evidence/g245-editorial-fidelity-t094-20260917T180802Z.json`.

SHA-256 bruto recebido:
`87e86ea84fdd7def0651a8218971d449aa21b85f82c3bcd521429ebee58f7923`.

Ambiente:

- WordPress 6.9.4;
- PHP 8.5.10;
- Block Projection 1.1.0;
- DOMDocument true;
- Gutenberg plugin dependency false.

Corpus: 623/623, errors 0, throwables 0.

Fidelity classes:

- rich_html_source_required 467;
- elementor_source_adapter_required 79;
- shortcode_resolution_required 37;
- kd_structure_sufficient_candidate 33;
- native_core_blocks 4;
- not_applicable 3.

Características observadas:

- links: 6.874 em 501 posts;
- images: 4.595 em 346 posts;
- inline formatting: 25.764 em 564 posts;
- styled spans: 1.394;
- line breaks: 1.319;
- tables: 513;
- rowspan cells: 463;
- colspan cells: 13;
- posts com shortcodes: 53;
- posts com Elementor meta: 80;
- Elementor text-editor widgets: 39;
- Elementor shortcode widgets: 1;
- editor HTML Elementor: 1.011 links, 587 imagens, 3.357 rich inline occurrences;
- attachment URL resolved: 0;
- attachment URL unresolved: 4.595.

Safety: read-only, sem exportar conteúdo/URLs/post IDs, sem network/render, fingerprint editorial igual e `t094_editorial_fidelity_pass=true`.

## Arquitetura de duas projeções

### Conhecimento

`fonte -> Content Extractor -> Knowledge Document`

Uso: busca, IA, hierarquia, qualidade e guardrail semântico.

### Migração lossless

`fonte -> Migration Fidelity Source -> Core Block Lossless Serializer`

Uso: preservar o material editorial necessário à migração.

O KD não pode ser tratado como uma cópia editorial lossless.

## T095 — Migration Fidelity Source v1

Contrato:
`migration-fidelity-source-contract-v1.md`.

Runtime:
`plugin/base-conhecimento-inteligencia-integrada/includes/class-migration-fidelity-source.php`.

Estratégias:

- Gutenberg → `native_core_blocks`;
- legacy HTML → unidade `post_content_rich_html` exata;
- plain text → unidade `post_content_plain_text` exata;
- Elementor → unidades ordenadas `elementor_text_editor_html` e `elementor_shortcode`;
- mixed → `review_required`, preservando os dois canais para futura decisão humana.

Cada unidade possui SHA-256/bytes e o documento possui `fidelity_hash` determinístico. Raw payload fica somente em memória.

## T096 — Lossless Core Block Serialization

Contrato:
`core-block-lossless-serialization-contract-v1.md`.

Runtime:

- `includes/class-core-block-lossless-serializer.php`;
- `includes/class-core-block-lossless-roundtrip-smoke.php`.

Mapeamento v1:

- legacy HTML/plain text → `core/freeform`;
- Elementor text-editor → `core/freeform`;
- Elementor shortcode → `core/shortcode`;
- Gutenberg existente → `native_noop`;
- mixed/unsupported → fail-closed.

Motivação: primeiro fazer uma **canonicalização lossless** para Core Blocks, sem tentar reconstruir semanticamente milhares de links/imagens/spans/tabelas de uma vez. Refinamento de `core/freeform` para blocos semânticos é etapa posterior.

Validação local combinada T095/T096: **34/34 assertions PASS + PHP lint PASS**.

O smoke T096 executa duas passagens e valida com as APIs reais do Core:

`Migration Fidelity Source -> serialize_blocks() -> parse_blocks() -> serialize_blocks()`.

Critérios:

- todo o corpus processado;
- zero errors/throwables;
- zero safety violations;
- zero raw payload round-trip mismatch;
- zero parse/serialize mismatch;
- zero fidelity hash mismatch;
- zero serialization hash mismatch;
- Gutenberg `native_noop` byte-preserved;
- corpus/fingerprint editorial unchanged;
- nenhum conteúdo/URL/post ID exportado;
- nenhum block/shortcode renderizado;
- nenhuma persistência.

## Pacote T096

- `0.4.0-g245-lossless-t096.1`;
- SHA-256 `5a2fc4ac31bfbe2b68cfe5f06d07057310760f54c9f5ecfc9fbc55b3b07ad961`;
- 41 PHP files lint PASS pré/pós ZIP;
- UX-002 byte parity PASS;
- T094 smoke OFF;
- T096 smoke ON;
- writer/migration OFF.

## Próximo passo exato

1. instalar o pacote T096 em homologação;
2. abrir `Base de Conhecimento > Lossless Blocks G-245`;
3. executar `Executar T096 e baixar JSON`;
4. devolver o JSON;
5. versionar a evidência;
6. se PASS, abrir T097 para paridade renderizada/editorial em cohort controlado;
7. somente depois generalizar dry-run/journal/stale/lock/batch para Block Migration e preparar canário.

Gate esperado:

`gate_result.t096_lossless_roundtrip_pass=true`.

## Guardrails absolutos

- não mexer na UX-002 sem UX-SPEC;
- não instalar/declarar plugin Gutenberg como requisito;
- não remover Elementor agora;
- não escrever `_elementor_data`;
- não escrever `post_content`;
- não usar KD como fonte editorial lossless;
- não exportar raw payload em runners;
- não decidir mixed source automaticamente;
- não executar shortcodes para migrar;
- não baixar/relinkar mídia;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
