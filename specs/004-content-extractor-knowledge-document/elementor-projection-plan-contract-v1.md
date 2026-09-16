# Elementor Projection Plan Contract v1 — SPEC-004

**Contract version:** `1.0.0`  
**Status:** FROZEN para T081 / read-only  
**Data:** 2026-09-16

## 1. Objetivo

Definir um plano determinístico e auditável de normalização editorial futura para Elementor sem produzir qualquer efeito de escrita.

`Projection Plan != Elementor writer`

O plano responde **o que seria necessário fazer**, quais dependências existem e se a transformação pode ser automatizada. Ele não executa a transformação.

## 2. Entradas autoritativas

O plano é derivado somente de:

- Knowledge Document KD `2.1.0`;
- `source_hash` canônico do KD;
- `source_kind`;
- `extraction.elementor_compatibility`;
- warnings determinísticos do extractor;
- inspeção read-only de shortcodes no `post_content` e `_elementor_data` bruto;
- G-245 Compatibility Matrix vigente.

Nenhum LLM, Foundry, embedding, render dinâmico ou `do_shortcode()` participa do plano.

## 3. Schema mínimo

```text
schema_version
post_id
source_kind
source_hash_before
knowledge_document_schema_version
elementor_compatibility.status
elementor_compatibility.reasons[]
plan_status
projection_strategy
operations[]
dependencies.shortcodes[]
warnings[]
requires_review
writer_allowed
projection_hash
```

## 4. Status do plano

- `native_noop` — Elementor nativo válido; plano não propõe migração automática.
- `projectable` — transformação determinística pode ser planejada, ainda sem writer.
- `review_required` — existe dependência/estrutura que impede automação segura.
- `blocked` — fonte/compatibilidade impede plano seguro.
- `not_applicable` — não há conteúdo materializável que justifique migração.

## 5. Estratégias v1

### Elementor nativo

`preserve_native`

- nenhuma regravação;
- nenhuma reconstrução do documento;
- se `source_kind=mixed`, adicionar `MIXED_SOURCE_NATIVE_REVIEW` e exigir revisão antes de qualquer writer futuro.

### Legacy HTML

`legacy_html_to_container_html`

Plano abstrato:

1. criar container raiz moderno;
2. preservar conteúdo semântico em componente controlado;
3. manter links/listas/tabelas/código e shortcodes como dependências explícitas;
4. nenhuma melhoria visual ou reescrita textual automática.

### Plain text

`plain_text_to_text_editor`

Plano abstrato:

1. container raiz;
2. text-editor com texto preservado;
3. nenhuma reescrita.

### Gutenberg

`gutenberg_static_mapping`

- somente quando a compatibilidade atual for `projectable`;
- dynamic/unsupported block => `review_required`.

### Review required

`manual_adapter_required`

- não gera operação de escrita executável;
- mantém reasons/warnings e dependências.

### Blocked

`blocked_no_projection`

- nenhuma operação proposta.

### Empty

`empty_noop`

- `not_applicable`;
- nenhuma operação proposta.

## 6. Shortcodes

Shortcodes são dependências opacas.

O plano pode registrar somente:

- tag;
- `registered=true|false`;
- origem `post_content|elementor_data`;
- provider apenas quando já comprovado pela matriz/preflight.

É proibido:

- executar shortcode;
- renderizar conteúdo do shortcode;
- substituir shortcode legado por outro por heurística;
- presumir provider desconhecido.

### Regras especiais da matriz v1

- `faq_wd` => `LEGACY_SHORTCODE_ORPHAN:faq_wd` + `requires_review=true`;
- `wpt` => `LEGACY_SHORTCODE_UNKNOWN:wpt` + `requires_review=true`.

## 7. `requires_review`

Deve ser `true` quando qualquer condição ocorrer:

- `elementor_compatibility.status=review_required|blocked`;
- shortcode usado sem handler registrado;
- `faq_wd` ou `wpt` presente;
- dynamic/unsupported Gutenberg;
- Elementor widget unsupported;
- Elementor JSON inválido;
- `source_kind=mixed` com Elementor nativo;
- warning estrutural relevante para migração.

`requires_review=false` nunca equivale a autorização de escrita.

## 8. `projection_hash`

SHA-256 sobre JSON canônico do plano excluindo apenas o próprio campo `projection_hash`.

O payload do hash inclui:

- schema/version;
- post_id;
- source_kind;
- source_hash_before;
- compatibility;
- plan_status;
- strategy;
- operations;
- dependencies;
- warnings;
- requires_review;
- writer_allowed=false.

Mesmo source + mesma matriz + mesmo algoritmo => mesmo hash.

## 9. Segurança

Todo plano v1 deve declarar:

```text
writer_allowed=false
persists_plan=false
executes_shortcodes=false
calls_external_network=false
writes_post_content=false
writes_elementor_data=false
```

O plano é calculado em memória e exportado apenas por runner administrativo temporário quando necessário.

## 10. Critério de aceite T081

T081 somente passa quando:

1. full-corpus produzir um plano para todos os 622 posts;
2. duas passagens gerarem zero `projection_hash` mismatch;
3. zero errors/throwables;
4. corpus/fingerprint editorial permanecerem idênticos;
5. nenhum plano tiver `writer_allowed=true`;
6. todos os `faq_wd`/`wpt` forem `requires_review=true`;
7. distribuição por status/strategy estiver versionada como evidência;
8. nenhum conteúdo editorial bruto for exportado no relatório.
