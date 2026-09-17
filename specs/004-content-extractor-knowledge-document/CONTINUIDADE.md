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
- T090 Block Projection Contract/Plan: **PASS LOCAL / READ-ONLY**, 23/23 assertions + lint.
- T091 full-corpus Block Projection smoke: **NEXT**.
- Nenhum writer/migration está autorizado.

## Decisão arquitetural vigente

Em 2026-09-17 foi aceita a ADR:

`adr/ADR-004-001-wordpress-core-blocks-canonical-editorial-target.md`

Decisão:

- `WP_Post.post_content` + WordPress Core Blocks são o destino editorial canônico futuro;
- o plugin Gutenberg NÃO é dependência de produção;
- usar somente APIs estáveis presentes no WordPress Core homologado;
- Elementor é fonte legada temporária/read-only até dependência zero;
- `_elementor_data` deve ser preservado durante a transição;
- nenhum novo writer deve usar `_elementor_data` como destino;
- T087C writer Elementor foi CANCELADO/SUPERSEDED antes de implementação.

A Constituição foi elevada para **v1.3.0** com essa decisão.

## Por que o pivot foi barato

O primeiro writer mutável ainda não existia e nenhum canário editorial havia sido executado. O que foi construído em G-245 é majoritariamente defensivo e reutilizável.

T081 diagnosticou 622 posts:

- 536 legacy_html;
- 41 plain_text;
- 34 elementor;
- 5 mixed;
- 4 gutenberg;
- 2 empty.

Elementor puro é minoritário. O problema real é normalizar legado heterogêneo.

## Componentes preservados

- Content Extractor;
- Knowledge Document 2.1;
- Legacy_HTML_Adapter;
- Gutenberg_Adapter;
- Elementor_Adapter como source adapter legado;
- Canonical JSON/hashes;
- journal + durable store;
- stale-source guard;
- dry-run;
- batch planning;
- lock exclusivo;
- canary/rollback methodology;
- runbook, a ser generalizado de Elementor para Blocks.

## T083B comprovado

Evidência:
`evidence/g245-journal-storage-smoke-20260917T155150Z.json`.

Resultado:

- `gate.t083b_storage_pass=true`;
- round-trip exato;
- integrity OK;
- cleanup OK;
- contagem restaurada;
- `post_content` unchanged;
- `_elementor_data` unchanged;
- writer/migration false.

## T090 — implementação atual

Build: `0.4.0-g245-block-projection.1`.

Arquivos:

- `specs/004-content-extractor-knowledge-document/block-projection-contract-v1.md`;
- `plugin/base-conhecimento-inteligencia-integrada/includes/class-block-projection-plan.php`;
- `tests/unit/spec004-block-projection-plan.php`.

Allowlist v1:

- heading → core/heading;
- paragraph → core/paragraph;
- code → core/code;
- list → core/list + core/list-item;
- table simples → core/table.

Comportamento:

- Gutenberg/Core Blocks pronto → `native_noop`;
- legado seguro → `projectable`;
- kind sem mapping/table spans/KD review → `review_required`;
- KD not_ready → `blocked`;
- empty → `not_applicable`;
- hash determinístico;
- sem serialize/persist/write.

Validação: **23/23 PASS + PHP lint PASS**.

## Estado do bootstrap

`0.4.0-g245-block-projection.1`:

- carrega `Block_Projection_Plan`;
- antigo Elementor Projection smoke fica `false` e é tratado como histórico;
- journal smoke fica `false` após PASS ambiental;
- futuro Block Projection smoke existe como flag `false` até T091;
- `BDC_KB_ELEMENTOR_WRITER_ENABLED=false`.

## Próximo passo exato — T091

Implementar runner full-corpus read-only de Block Projection.

Critérios:

1. duas passagens 622/622;
2. zero errors/throwables;
3. zero `block_projection_hash` mismatch;
4. zero canonical representation mismatch;
5. distribuição por source/status;
6. warnings agregados sem conteúdo editorial no JSON;
7. fingerprint editorial before/after idêntico;
8. changed posts = 0;
9. writer/migration false;
10. runner desabilitado por padrão após uso.

A evidência T091 deve orientar T092. Não ampliar allowlist por hipótese.

## Guardrails absolutos

- não mexer na UX-002 sem UX-SPEC;
- não instalar/declarar plugin Gutenberg como requisito;
- não usar APIs Gutenberg experimentais/plugin-only;
- não remover Elementor agora;
- não escrever em `_elementor_data`;
- não escrever em `post_content` antes dos novos gates de Blocks;
- não interpretar autorização genérica como GO para canário mutável;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
