# Evidência G-110 — 0.3.0-dev.10 / PRG da Classificação

## Resultado real

Arquivo bruto:

`evidencias/bdc-kb-g110-browser-acceptance-20260915-194552.json`

Ambiente:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- plugin `0.3.0-dev.10`;
- multisite: não.

Resumo:

- browser: **21 PASS / 1 FAIL**;
- server: **7 PASS / 0 FAIL**;
- `overall=FAIL`;
- cleanup: zero posts, zero termos e zero eventos residuais.

## O único FAIL

`G110-B10 — Classificação salva pelo formulário real e permanece no contexto/tab.`

A URL real após o save foi:

`admin.php?page=bdc-knowledge-summary&bdc_classification_status=saved&post_id=46754`

O status `saved` comprova que o writer respondeu com sucesso. A asserção server-side `G110-S05` também passou, confirmando que a Classificação foi persistida corretamente no store canônico.

O problema é exclusivamente de PRG/UX: faltou `tab=classification` no redirect.

## Diagnóstico no código

`Classification_Admin::redirect()` montava os argumentos:

- `page`;
- `bdc_classification_status`;
- `post_id`.

Não havia `tab=classification`.

Consequência: o POST salva corretamente, mas o retorno cai na tab padrão do Workspace, violando o critério de permanência no mesmo domínio após save.

## Correção autorizada

Correção mínima em `class-classification-admin.php`:

`'tab' => 'classification'`

Nenhuma alteração em:

- `Classification_Store`;
- `Classification_Contract`;
- `Summary_Store`;
- `Review_Store`;
- `Review_Contract`;
- schema;
- persistência;
- segurança do writer.

## Decisão de gate

**G-110 permanece NÃO APROVADO.**

O `dev.10` é um FAIL real, embora localizado. O `0.3.0-dev.11` deve repetir o Browser Acceptance completo. O gate só pode ser promovido se vier:

- `browser_fail=0`;
- `server_fail=0`;
- `overall=PASS`;
- `residual_posts=0`;
- `residual_terms=0`;
- `residual_review_events=0`.
