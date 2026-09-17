# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Repositório / branch

- Repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`.
- Branch ativa: `spec004-g245-production-readiness`.
- PR: #4 — DRAFT / NÃO MERGEAR.
- SPEC ativa: SPEC-004.

Antes de qualquer alteração, reler `AGENTS.md`, `.specify/PROJECT_MANIFEST.md`, `.specify/memory/constitution.md`, esta SPEC, ADRs vigentes e `docs/DEFINITION-OF-DONE.md`.

## Estado atual

- SPEC-001/002/003: concluídas.
- UX-002 `0.4.0-ux002.3`: PASS/CLOSED e contrato visual obrigatório.
- G-240: PASS/CLOSED/promovido para `main`.
- KD 2.1.0: PASS técnico full-corpus + PASS humano 8/8.
- G-245: **REBASELINED / IN PROGRESS**.
- T083B Durable Journal Storage: PASS AMBIENTAL.
- ADR-004-001: **ACEITA**.
- T090 Block Projection v1.0: PASS LOCAL / READ-ONLY.
- T091 Block Projection full-corpus: **PASS AMBIENTAL**.
- T092 Block Projection v1.1: **PASS LOCAL / READ-ONLY**, 25/25 assertions + lint.
- T093 full-corpus v1.1 + diagnóstico KD: **IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE**.
- Nenhum writer/migration está autorizado.

## Decisão arquitetural vigente

`WP_Post.post_content` + WordPress Core Blocks são o destino editorial canônico futuro.

- plugin Gutenberg NÃO é dependência de produção;
- usar somente APIs estáveis presentes no WordPress Core homologado;
- Elementor é fonte legada temporária/read-only até dependência zero;
- `_elementor_data` deve ser preservado durante a transição;
- nenhum novo writer deve usar `_elementor_data` como destino;
- T087C writer Elementor foi CANCELADO/SUPERSEDED antes de implementação.

Constituição nesta branch: **v1.3.0**.

## Baseline ambiental T091

Evidência: `evidence/g245-block-projection-t091-20260917T172515Z.json`.

Ambiente:

- WordPress 6.9.4;
- PHP 8.5.10;
- Elementor 4.1.0;
- Block Projection schema 1.0.0;
- plugin Gutenberg dependency false.

Corpus observado: **623 posts**. O baseline T081 tinha 622; houve +1 post antes do T091. Durante o gate, corpus/fingerprint permaneceram estáveis.

Resultado:

- duas passagens 623/623;
- errors 0;
- throwables 0;
- block projection hash mismatches 0;
- canonical hash mismatches 0;
- safety violations 0;
- fingerprint editorial igual;
- `gate_result.t091_block_projection_pass=true`.

Distribuição:

- projectable 347;
- review_required 269;
- native_noop 4;
- not_applicable 3.

Warnings:

- KD review required 233;
- image unsupported 40;
- table span review 32;
- quote unsupported 8.

SHA-256 do JSON recebido: `1fa9fc1439634cb9ecca6a1caa0aac63e9f37366bc9c5656d2ea1b18eb3c6b93`.

## T092 — Block Projection v1.1

Contrato: `block-projection-contract-v1.1.md`.
Build de desenvolvimento: `0.4.0-g245-block-projection.2`.

Mudança deliberada:

- `quote` → `core/quote`;
- schema → `1.1.0`;
- imagem continua review porque o KD 2.1 não preserva referência canônica de mídia suficiente;
- table spans continuam review para evitar flattening silenciosa.

Validação local: **25/25 PASS + PHP lint PASS**.

## T093 — próximo gate ambiental

Runner: `plugin/base-conhecimento-inteligencia-integrada/includes/class-block-projection-plan-smoke.php`.

Além do determinismo/safety de T091, T093 exporta somente dados agregados:

- `knowledge_document_readiness`;
- `knowledge_document_reasons` para review/not_ready;
- `source_plan_matrix`;
- warnings;
- projected block names.

Sem conteúdo editorial, sem post IDs, sem `serialize_blocks()`, sem persistência.

## Próximo passo exato

1. instalar o pacote T093 em homologação;
2. abrir `Base de Conhecimento > Block Projection G-245`;
3. executar `Executar T093 e baixar JSON`;
4. devolver o JSON;
5. versionar evidência;
6. definir T094 com base nos motivos reais dos KD reviews e nos gaps restantes.

Critério esperado:

- duas passagens cobrindo todo o corpus atual;
- errors/throwables 0;
- hash mismatches 0;
- safety violations 0;
- corpus/fingerprint estáveis;
- `gate_result.t093_block_projection_pass=true`.

## Próxima sequência planejada

- T094: contrato de serialização Core Blocks in-memory;
- T095: semantic round-trip `projection → serialize → parse` sem persistência;
- T096: generalizar dry-run/journal/stale/lock/batches para Block Migration;
- T097: canário de 1 artigo + rollback real com Authorization Pack;
- T098: batches homologados;
- T099: dependência residual Elementor e gate de retirada.

## Guardrails absolutos

- não mexer na UX-002 sem UX-SPEC;
- não instalar/declarar plugin Gutenberg como requisito;
- não usar APIs Gutenberg experimentais/plugin-only;
- não remover Elementor agora;
- não escrever em `_elementor_data`;
- não escrever em `post_content` antes dos novos gates de Blocks;
- não converter imagem sem proveniência de mídia;
- não interpretar autorização genérica como GO para canário mutável;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
