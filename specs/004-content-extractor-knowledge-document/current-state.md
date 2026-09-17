# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline e gates

- SPEC-001/002/003: concluídas.
- UX-001/UX-002: concluídas; UX-002 `0.4.0-ux002.3` é contrato visual obrigatório.
- G-240: PASS / CLOSED / promovido para `main`.
- KD 2.1.0: PASS técnico full-corpus + PASS humano 8/8.
- G-245: **REBASELINED / IN PROGRESS** em `spec004-g245-production-readiness`; PR #4 DRAFT / NÃO MERGEAR.
- ADR-004-001: **ACEITA**.
- T090 Block Projection: **PASS LOCAL / READ-ONLY**.
- T091 Block Projection full-corpus smoke: **IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE**.
- G-250: NOT_RUN.

## Mudança arquitetural de 2026-09-17

A arquitetura editorial futura foi alterada antes do primeiro writer mutável.

**Antes:** Elementor como destino editorial futuro.  
**Agora:** `WP_Post.post_content` + WordPress Core Blocks.

Razões:

- WordPress-first;
- Elementor Free é dependência externa desnecessária como fundação canônica;
- corpus real mostra somente 34/622 posts Elementor puro;
- legado predominante é HTML, portanto convergir todos os sources para Blocks é mais coerente;
- Blocks fornecem estrutura nativa para IA, busca e automação governada;
- nenhum writer Elementor ou canário mutável havia sido executado, tornando o pivot barato.

ADR: `adr/ADR-004-001-wordpress-core-blocks-canonical-editorial-target.md`.

## Fonte editorial

- Canônica futura: `WP_Post.post_content` + Core Blocks.
- Plugin Gutenberg: não é dependência; somente APIs estáveis do WordPress Core.
- Elementor: source adapter legado, read-only durante a transição.
- `_elementor_data`: preservar enquanto houver dependência; nunca usar como novo destino.

## Evidência do corpus

T081 sobre 622 posts:

- 536 legacy_html;
- 41 plain_text;
- 34 elementor;
- 5 mixed;
- 4 gutenberg;
- 2 empty.

T081 continua evidência válida de diagnóstico/determinismo, mas o Elementor Projection Plan não é mais destino arquitetural.

## Investimentos preservados

Permanecem válidos:

- Content Extractor;
- Knowledge Document 2.1;
- Legacy HTML Adapter;
- Gutenberg Adapter;
- Elementor Adapter como reader legado;
- Canonical JSON/hashes;
- journal durable;
- stale-source guard;
- dry-run;
- batch plan;
- exclusive lock;
- canary/rollback methodology;
- production runbook, a ser generalizado.

T083B Journal Durable Storage possui PASS ambiental com `gate.t083b_storage_pass=true` e zero mutação de `post_content`/`_elementor_data`.

## Itens SUPERSEDED

- Elementor como destino editorial futuro;
- T087C writer Elementor;
- writer em `_elementor_data`;
- Elementor Gateway como gate do destino final;
- Elementor Projection Plan como plano de migração final.

Esses artefatos permanecem versionados como memória institucional/diagnóstico.

## T090 — Block Projection Plan v1

Build de desenvolvimento: `0.4.0-g245-block-projection.1`.

Implementado:

- `block-projection-contract-v1.md`;
- `class-block-projection-plan.php`;
- `tests/unit/spec004-block-projection-plan.php`.

Allowlist v1:

- `heading` → `core/heading`;
- `paragraph` → `core/paragraph`;
- `code` → `core/code`;
- `list` → `core/list`/`core/list-item`;
- `table` simples → `core/table`.

Fail-closed/review:

- kind não suportado → review_required;
- table span → review_required;
- KD not_ready → blocked;
- empty → not_applicable;
- Gutenberg/Core Blocks prontos → native_noop.

Safety:

- sem `serialize_blocks()`;
- sem persistência;
- `serialized_post_content=null`;
- writer/migration false;
- sem network/shortcode/dynamic block render;
- sem dependência do plugin Gutenberg.

Validação local: **23/23 assertions PASS + PHP lint PASS**.

## T091 — Full-corpus Block Projection

Runner: `class-block-projection-plan-smoke.php`.

Pacote de homologação:

- build `0.4.0-g245-block-projection-smoke.1`;
- SHA-256 `d70b518bc20825a5d64984aef555f67a270ec065587c00bfefa3caad3e67028c`;
- 37 PHP files lint PASS antes/depois do ZIP;
- raiz única PASS;
- UX-002 byte parity PASS;
- Elementor Projection smoke disabled;
- Journal smoke disabled;
- Block Projection smoke enabled somente no pacote;
- writer Elementor false.

O runner executa duas passagens e exporta apenas métricas, contagens e hashes agregados. Não exporta conteúdo editorial nem post IDs, não serializa Blocks e não persiste `post_content`/`_elementor_data`.

## Próximo passo técnico

Executar T091 na homologação e devolver o JSON.

Gate esperado:

`gate_result.t091_block_projection_pass=true`.

A distribuição de warnings/status/block names definirá T092. Não ampliar a allowlist antes da evidência real.

## Guardrails

- UX-002 não pode regredir;
- plugin Gutenberg não pode virar dependência silenciosa;
- Elementor não pode ser removido antes de dependência zero;
- nenhuma migração automática em activation/update;
- nenhum writer está autorizado;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
