# T097 — Core Block Static Editorial Parity Contract v1

**Status:** IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE  
**SPEC:** 004 — G-245 Rebaseline  
**Target:** WordPress Core Blocks  

## 1. Objetivo

Provar, antes de qualquer write, que a canonicalização lossless validada no T096 continua editorialmente íntegra quando reinterpretada pelo parser real do WordPress Core e que qualquer drift da fonte bloqueia a migração.

T097 é read-only. Não chama `render_block()`, `do_blocks()`, `do_shortcode()`, `wp_update_post()` ou qualquer writer.

## 2. Escopo

Para cada post:

1. construir `Migration Fidelity Source`;
2. serializar in-memory via `Core_Block_Lossless_Serializer`;
3. `parse_blocks()` sobre o resultado quando aplicável;
4. comparar cada unidade editorial com o bloco parseado correspondente por block name + SHA-256 do `innerHTML`;
5. reconstruir a fonte e executar stale-source guard genérico;
6. repetir em segunda passagem e comparar manifest hash agregado.

## 3. Paridade

Mappings aceitos:

- `post_content_rich_html` -> `core/freeform`;
- `post_content_plain_text` -> `core/freeform`;
- `elementor_text_editor_html` -> `core/freeform`;
- `elementor_shortcode` -> `core/shortcode`;
- Gutenberg existente -> `native_noop` byte-equivalent.

`mixed` permanece `review_required` e não é decidido automaticamente.

## 4. Block Registry

O runtime homologado deve ter registrados:

- `core/freeform`;
- `core/shortcode`.

Ausência de qualquer um bloqueia o gate.

## 5. Stale-source

O guard compara:

- `fidelity_hash`;
- `source_kind`;
- `post_content_sha256`;
- `elementor_data_sha256`.

Qualquer divergência produz `stale` e bloqueia futura migração.

## 6. Segurança

Obrigatório:

- read-only;
- zero persistência;
- zero render de blocks;
- zero execução de shortcodes;
- zero network;
- zero export de raw content/URLs/post IDs;
- writer/migration false;
- sem dependência do plugin Gutenberg.

## 7. Gate ambiental

PASS somente se:

- corpus completo em duas passagens;
- block registry PASS;
- errors/throwables/safety violations = 0;
- parity mismatches = 0;
- stale sources = 0;
- manifest hash mismatches = 0;
- corpus e fingerprint editorial unchanged;
- `gate_result.t097_static_editorial_parity_pass=true`.

## 8. Fora de escopo

- paridade visual humana;
- render de shortcode/dynamic block;
- write em `post_content`;
- remoção do Elementor;
- decisão automática sobre source `mixed`.

Paridade visual/editorial humana deve ocorrer no canário controlado com rollback já preparado.
