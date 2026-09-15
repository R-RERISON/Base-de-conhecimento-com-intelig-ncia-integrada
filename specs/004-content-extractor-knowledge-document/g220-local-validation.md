# G-220 — Validação local do Content Extractor

**Build:** `0.4.0-dev.1`  
**Data:** 2026-09-15  
**Escopo:** implementação determinística/read-only antes do smoke ambiental em WordPress.

## PHP lint

Arquivos novos validados:

- `class-content-normalizer.php`;
- `class-shortcode-inspector.php`;
- `class-legacy-html-adapter.php`;
- `class-content-source.php`;
- `class-elementor-adapter.php`;
- `class-gutenberg-adapter.php`;
- `class-content-extractor.php`;
- `tests/unit/spec004-content-extractor.php`.

Resultado: **PASS — sem erro de sintaxe**.

## Unit tests SPEC-004

Resultado local:

```text
PASS legacy_html_structural
PASS technical_brackets_not_shortcode
PASS registered_shortcode_not_executed
PASS elementor_valid_text_editor
PASS elementor_invalid_falls_back
PASS mixed_elementor_gutenberg
PASS gutenberg_known_blocks
PASS gutenberg_dynamic_not_rendered
PASS plain_text
PASS empty
PASS soft_limit_warning
PASS hard_limit_no_silent_truncate
PASS repeatability
PASS unsupported_post_type

RESULT passed=14 failed=0
```

O runner possui stubs de `update_post_meta()`, `wp_update_post()` e `wp_insert_post()` que incrementam contador de write. Após **cada teste**, o contador deve permanecer zero.

## Descoberta de portabilidade

O PHP CLI usado no teste local não possui `ext-dom`/`DOMDocument`.

Em vez de adicionar nova dependência obrigatória ao package, o Legacy adapter foi adaptado para:

1. usar `DOMDocument` quando disponível;
2. cair para parser estrutural determinístico quando indisponível;
3. emitir `HTML_DOM_UNAVAILABLE`;
4. continuar sem renderizar tema, Elementor, widget ou shortcode.

Isso reduz risco de diferença entre homologação e produção.

## Comportamentos comprovados localmente

- legacy HTML preserva boundaries/facts principais;
- texto técnico entre colchetes não vira shortcode por heurística ingênua;
- shortcode reconhecido não executa callback;
- Elementor `text-editor` válido é extraído estruturalmente;
- Elementor JSON inválido faz fail-soft para `post_content`;
- mixed Elementor + Gutenberg preserva proveniência dupla;
- Gutenberg conhecido é extraído sem renderização;
- dynamic block não é renderizado;
- plain text é normalizado deterministicamente;
- conteúdo vazio não recebe texto sintético;
- soft/hard limits são observáveis e hard limit não trunca silenciosamente;
- mesma entrada gera saída intermediária byte-equivalente no runner;
- post type fora do contrato falha fechado;
- nenhum path de teste chama writer editorial.

## Limite desta evidência

Esta evidência é **local/unitária**. Ela não substitui:

- smoke no WordPress de homologação;
- G-230 Knowledge Document/hash;
- G-240 Real Content Acceptance;
- G-245 Production Readiness/canário;
- lifecycle/package G-250.
