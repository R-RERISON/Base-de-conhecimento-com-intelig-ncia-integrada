# G-220 — Análise do smoke ambiental

## Resultado

**PASS.**

Evidência: `evidence/g220-smoke-20260915T221710Z.json`.

## Gates de segurança

- `editorial_fingerprint_equal=true`;
- `changed_posts_during_run=0`;
- corpus `622 -> 622`;
- `extractor_errors=0`;
- `throwables=0`.

## Ambiente

- WordPress `6.9.4`;
- PHP `8.5.10`;
- Elementor `4.1.0`;
- DOMDocument disponível;
- multisite: não.

## Resultado funcional

- 622 posts processados;
- 21.969 fragments;
- source kinds efetivos: 536 legacy_html, 41 plain_text, 34 Elementor, 5 mixed, 4 Gutenberg, 2 empty;
- strategies: 546 legacy_html, 39 Elementor, 31 plain_text, 9 Gutenberg, 2 empty.

A diferença para a classificação estatística R-200 é intencional: o extractor usa a estratégia efetiva. Elementor inválido não permanece artificialmente classificado como Elementor; cai para fonte segura quando possível.

## Readiness Elementor

- 39 native (6,27%);
- 505 projectable (81,19%);
- 78 review_required (12,54%);
- 0 blocked.

`native + projectable = 544/622 = 87,46%`.

Essa classificação não autoriza migração. Ela orienta o futuro Projection Plan de G-245.

## Warnings observados

- `HTML_PARSE_RECOVERED`: 62;
- `ELEMENTOR_JSON_INVALID`: 41;
- shortcodes não expandidos: principalmente `table` (45), além de `wpt`, `caption`, `video`, `dbc_table`, `faq_wd` e `bdc_resumo_executivo`.

Nenhum warning se converteu em erro fatal ou throwable.

## Performance

- runtime: 1.807 ms;
- peak memory: 31.457.280 bytes (~30 MiB).

Não há evidência que justifique storage/cache durável no G-220.

## Decisão

G-220 está fechado. O próximo gate é G-230, mantendo:

- extractor read-only;
- Knowledge Document in-memory;
- migration Elementor em plano separado;
- produção governada por preflight/canário/rollback.
