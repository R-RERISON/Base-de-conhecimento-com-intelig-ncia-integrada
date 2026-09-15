# Package `0.3.0-dev.10` — rerun Browser Acceptance G-110

## Escopo

Build exclusivamente de homologação. O runtime funcional do `0.3.0-dev.7` permanece congelado.

Mudança do `dev.10`:

- `browser-acceptance.js` deixa de chamar `form.submit()` diretamente;
- passa a chamar `loaded.win.HTMLFormElement.prototype.submit.call(form)` nos formulários dentro dos iframes;
- o shim temporário do `dev.9` foi removido por ter se mostrado ineficaz no browser real;
- nenhum Store, Contract ou Writer permanente foi alterado.

## Evidência que motivou o rerun

`0.3.0-dev.9` reproduziu:

- 7 browser PASS / 1 FAIL;
- 3 server PASS / 4 FAIL;
- `overall=FAIL`;
- exceção `form.submit is not a function`;
- cleanup zero resíduos.

Documento: `evidencia-g110-dev9-submit-collision-reproduzida.md`.

## Validação local

- PHP lint: **PASS 14/14**;
- `workspace.js`: syntax PASS;
- `browser-acceptance.js`: syntax PASS;
- estrutura ZIP WordPress: PASS;
- teste de integridade do ZIP: PASS.

## ZIP

`base-conhecimento-inteligencia-integrada-0.3.0-dev.10.zip`

SHA-256:

`eee8dbed4e4cc1c8baaa5b07bd9522f063612ff8586bc93ba18352f35c41b8eb`

## Critério de aceite

G-110 só pode ser promovido a PASS se o JSON real do `dev.10` retornar simultaneamente:

- `browser_fail=0`;
- `server_fail=0`;
- `overall=PASS`;
- `residual_posts=0`;
- `residual_terms=0`;
- `residual_review_events=0`.
