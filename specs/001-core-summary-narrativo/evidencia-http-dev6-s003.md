# Evidência S003 — Segurança HTTP `0.1.0-dev.6`

## Resultado

**PASS — G-070 HTTP fechado no ambiente WordPress real.**

Arquivo raw: `evidencias/bdc-kb-http-security-20260914-194454.json`.

## Ambiente

- WordPress: `6.9.4`
- PHP: `8.5.10`
- Plugin: `0.1.0-dev.6`
- Browser: Microsoft Edge 153 / Windows 10

## Resultado agregado

- `pass=11`
- `fail=0`
- `overall=PASS`
- fixture `post` removida: `true`
- fixture `page` removida: `true`
- `residual_fixtures=0`
- `real_content_modified=false`

## Checks comprovados

- `G070-H01`: GET tentando salvar -> HTTP 405;
- `G070-H02`: nonce ausente -> `invalid_nonce`;
- `G070-H03`: nonce inválido -> `invalid_nonce`;
- `G070-H04`: payload ausente -> `invalid_payload`;
- `G070-H05`: campo extra/mass assignment -> `validation_error`;
- `G070-H06`: `page` fora do escopo -> `invalid_post`;
- `G070-H07`: ID inexistente -> `invalid_post`;
- `G070-H08`: XSS via POST válido -> `saved`, com sanitização verificada server-side;
- `G070-S01`: requests rejeitados não alteraram objective/escalation;
- `G070-S02`: payload XSS não persistiu `<script>`;
- `G001-HTTP`: título, `post_content` e `_elementor_data` permaneceram intactos.

Capability/IDOR já haviam sido comprovados no runner técnico real por `map_meta_cap`; a sessão HTTP desta execução era administrativa e não criou usuário de teste permanente.

## Decisão

Combinando o runner técnico real, o browser acceptance e este runner HTTP, **G-070 = PASS para o escopo da SPEC-001**.
