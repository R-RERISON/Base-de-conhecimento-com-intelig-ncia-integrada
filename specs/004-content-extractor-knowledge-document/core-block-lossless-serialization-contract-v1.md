# T096 — Lossless Core Block Serialization v1

**Status:** FROZEN — PASS LOCAL / HOMOLOGAÇÃO PENDENTE  
**SPEC:** 004 — G-245 Rebaseline  
**Target:** WordPress Core Blocks  
**Dependency:** T095 Migration Fidelity Source v1

## 1. Objetivo

Provar que o material editorial lossless pode ser convertido **somente em memória** para uma estrutura válida de WordPress Core Blocks e sobreviver a `serialize_blocks()` → `parse_blocks()` → `serialize_blocks()` sem perda do payload bruto.

T096 não é writer.

## 2. Estratégia de normalização em duas etapas

### Etapa A — canonicalização lossless

Objetivo: retirar a dependência estrutural do page builder/legado sem perder conteúdo.

- Gutenberg existente → `native_noop`;
- legacy HTML/plain text → `core/freeform`;
- Elementor `text-editor` → `core/freeform`;
- Elementor `shortcode` → `core/shortcode`;
- mixed/unsupported → `review_required`.

`core/freeform` é um Core Block estável e permite preservar HTML rico como payload sem reconstruí-lo prematuramente.

### Etapa B — refinamento semântico futuro

Após migração lossless e paridade comprovada, blocos `core/freeform` poderão ser progressivamente convertidos em `core/paragraph`, `core/heading`, `core/list`, `core/table`, `core/image` etc., orientados pelo Knowledge Document e por novos gates.

A Etapa B não faz parte de T096.

## 3. Entrada

Migration Fidelity Source v1 com `fidelity_hash` válido.

## 4. Saída

Serializer v1 retorna:

- `status`;
- `blocks[]` em memória;
- `serialized_post_content` em memória;
- `serialized_sha256`;
- `serialization_hash`;
- warnings;
- `writer_allowed=false`;
- `migration_execution_allowed=false`;
- safety read-only.

O runner ambiental nunca exporta o conteúdo serializado.

## 5. Estados

- `serialized_in_memory`;
- `native_noop`;
- `review_required`;
- `blocked`;
- `not_applicable`.

## 6. Mapeamento lossless v1

| Migration unit | Core Block |
|---|---|
| `post_content_rich_html` | `core/freeform` |
| `post_content_plain_text` | `core/freeform` |
| `elementor_text_editor_html` | `core/freeform` |
| `elementor_shortcode` | `core/shortcode` |
| `native_core_blocks` | no-op; preservar conteúdo atual |

Qualquer outra unidade deve falhar fechado em `review_required`.

## 7. Round-trip obrigatório

Para cada item `serialized_in_memory`:

1. serializar com `serialize_blocks()` do WordPress Core;
2. executar `parse_blocks()` sobre o resultado;
3. comparar SHA-256 de `innerHTML` de cada bloco material com o payload bruto da unidade correspondente;
4. reserializar o parsed tree;
5. exigir hash idêntico ao primeiro conteúdo serializado.

Para `native_noop`, o SHA-256 do conteúdo retornado deve ser idêntico ao `post_content` original.

## 8. Determinismo

Duas passagens consecutivas devem produzir:

- mesmo `fidelity_hash`;
- mesmo `serialization_hash`;
- zero mismatch de raw payload;
- zero mismatch parse/serialize.

## 9. Segurança

Obrigatoriamente:

- serialização apenas em memória;
- `persists_state=false`;
- `writes_post_content=false`;
- `writes_elementor_data=false`;
- `writer_allowed=false`;
- `migration_execution_allowed=false`;
- `renders_blocks=false`;
- `executes_shortcodes=false`;
- `calls_external_network=false`;
- `depends_on_gutenberg_plugin=false`.

## 10. Gate ambiental T096

O full-corpus smoke deve provar em duas passagens:

- cobertura de todo o corpus;
- zero errors/throwables;
- zero safety violations;
- zero raw roundtrip mismatch;
- zero parse/serialize mismatch;
- zero fidelity hash mismatch;
- zero serialization hash mismatch;
- fingerprint editorial before/after idêntico;
- corpus unchanged;
- nenhuma exportação de conteúdo, URLs ou post IDs;
- `gate_result.t096_lossless_roundtrip_pass=true`.

## 11. O que T096 não prova

T096 não prova paridade visual/renderizada. `core/freeform` preserva o payload, mas a equivalência de frontend e de experiência editorial será comprovada posteriormente por canário controlado e aceite humano.

## 12. Aceite local

T095/T096 combinados: **34/34 assertions PASS + PHP lint PASS**.
