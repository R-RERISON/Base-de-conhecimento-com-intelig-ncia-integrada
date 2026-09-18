# T090 — Block Projection Contract v1

**Status:** FROZEN — PASS LOCAL / READ-ONLY  
**SPEC:** 004 — G-245 Rebaseline  
**Target:** WordPress Core Blocks  
**ADR:** `adr/ADR-004-001-wordpress-core-blocks-canonical-editorial-target.md`

## 1. Objetivo

Definir uma projeção determinística e read-only do Knowledge Document 2.1.0 para uma representação canônica de WordPress Core Blocks, antes de qualquer serialização ou persistência em `post_content`.

T090 não é writer, não chama `serialize_blocks()`, não executa dynamic blocks e não depende do plugin Gutenberg.

## 2. Entrada

Knowledge Document válido contendo, no mínimo:

- `post_id`;
- `source_hash` SHA-256;
- `source_kind`;
- `ai_readiness.status`;
- `blocks[]`.

## 3. Saída

Block Projection Plan v1:

- `schema_version`;
- `post_id`;
- `source_kind`;
- `source_hash_before`;
- `target=wordpress_core_blocks`;
- `plan_status`;
- `projection_strategy`;
- `blocks[]` normalizados;
- `warnings[]`;
- `requires_review`;
- `block_projection_hash`;
- invariantes de safety com writer/migration false.

`serialized_post_content` deve permanecer `null` em T090.

## 4. Estados

- `native_noop`: conteúdo já é Gutenberg/Core Blocks e não há blocker/review;
- `projectable`: projeção segura com allowlist v1;
- `review_required`: existe estrutura sem mapeamento seguro ou warning que exige humano;
- `blocked`: Knowledge Document `not_ready` ou violação estrutural;
- `not_applicable`: fonte vazia sem conteúdo semântico.

## 5. Allowlist v1

### `heading` → `core/heading`

Preservar nível 1–6 via `attrs.level` e texto.

### `paragraph` → `core/paragraph`

Preservar texto em ordem.

### `code` → `core/code`

Preservar texto literalmente; nenhum código é executado.

### `list` → `core/list` + `core/list-item`

Preservar:

- ordered/unordered quando identificável;
- ordem dos itens;
- nesting determinístico via `inner_blocks`.

Tipo de lista desconhecido gera `BLOCK_PROJECTION_LIST_TYPE_REVIEW:*`.

### `table` → `core/table`

Preservar caption, linhas e células em representação intermediária. `rowspan` ou `colspan` diferente de 1 gera `BLOCK_PROJECTION_TABLE_SPAN_REVIEW`, pois a serialização/paridade ainda não foi comprovada.

## 6. Tipos não suportados

Qualquer `kind` fora da allowlist v1 produz:

`BLOCK_PROJECTION_UNSUPPORTED_KIND:<kind>`

O plano continua auditável, mas fica `review_required`. T090 não inventa conteúdo, não converte silenciosamente e não usa IA para completar estrutura.

## 7. Determinismo

`block_projection_hash` é SHA-256 sobre canonical JSON do plano sem o próprio hash.

Duas projeções da mesma entrada devem produzir:

- mesma ordem;
- mesmos blocos;
- mesmos warnings;
- mesmo hash.

## 8. Segurança

Obrigatoriamente:

- `writer_allowed=false`;
- `migration_execution_allowed=false`;
- `persists_state=false`;
- `writes_post_content=false`;
- `writes_elementor_data=false`;
- `calls_external_network=false`;
- `executes_shortcodes=false`;
- `renders_dynamic_blocks=false`;
- `depends_on_gutenberg_plugin=false`.

## 9. Elementor

`source_kind=elementor` ou `mixed` pode gerar Block Projection a partir do Knowledge Document, porém Elementor permanece apenas fonte legada. T090 nunca produz `_elementor_data`.

## 10. Gutenberg/Core Blocks existentes

Quando `source_kind=gutenberg`, `ai_readiness` está pronto e todos os blocos são mapeáveis, o resultado é `native_noop` / `preserve_core_blocks`.

O gate futuro deverá comparar representação canônica/serialização para garantir que “native noop” realmente não provoque drift.

## 11. Fora de escopo

- serializar markup de blocks;
- persistir `post_content`;
- registrar blocos `bdc/*`;
- converter imagens/mídia sem proveniência segura;
- renderizar shortcodes;
- renderizar dynamic blocks;
- usar plugin Gutenberg;
- writer ou canário mutável.

## 12. Aceite local

`tests/unit/spec004-block-projection-plan.php` cobre:

- documento válido/inválido;
- mapping de heading/paragraph/list/table/code;
- nested list;
- hash determinístico;
- `native_noop` para Gutenberg;
- unsupported kind → review;
- table spans → review;
- KD not_ready → blocked;
- empty → not_applicable;
- zero writer/plugin dependency.

**Resultado local:** 23/23 assertions PASS + PHP lint PASS.

## 13. Próximo gate

T091 deve criar um **Block Projection full-corpus smoke read-only**, comparando duas passagens e produzindo distribuição por source kind/status/warnings, sem serialização/persistência.
