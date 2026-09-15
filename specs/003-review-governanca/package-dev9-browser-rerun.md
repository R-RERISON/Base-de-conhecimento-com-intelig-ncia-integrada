# Package `0.3.0-dev.9` — rerun Browser Acceptance G-110

## Objetivo

Repetir o Gate G-110 após o FAIL real do `0.3.0-dev.8`, corrigindo exclusivamente a colisão do harness `form.submit is not a function`.

## Escopo da mudança

Runtime funcional congelado no comportamento do `0.3.0-dev.7/8`.

Alteração nova:

- `includes/class-workspace-browser-submit-shim.php`;
- bootstrap passa a carregar/registrar o shim somente no build de diagnostics G-110;
- versão `0.3.0-dev.9`.

Nenhum Store, Contract ou writer permanente foi alterado.

## Motivo técnico

Os formulários do wp-admin possuem controle nomeado `submit`. No DOM do iframe, esse controle sombreou o método nativo `HTMLFormElement.submit`, fazendo `form.submit()` resolver para um elemento e abortar o runner.

O shim age apenas nos iframes do Browser Acceptance e renomeia esses controles para `bdc_g110_submit` antes do teste. Os handlers não dependem do nome do botão de submit.

## Evidência de entrada

`0.3.0-dev.8`:

- browser: `7 PASS / 1 FAIL`;
- server: `3 PASS / 4 FAIL`;
- `overall=FAIL`;
- cleanup zero resíduos.

Evidência: `evidencias/bdc-kb-g110-browser-acceptance-20260915-192838.json`.

Diagnóstico: `evidencia-g110-dev8-submit-collision.md`.

## Validação local

- PHP lint: `15/15 PASS`;
- `assets/js/workspace.js`: syntax PASS;
- `assets/js/browser-acceptance.js`: syntax PASS;
- ZIP com raiz WordPress correta: PASS.

## Artefato

ZIP: `base-conhecimento-inteligencia-integrada-0.3.0-dev.9.zip`

SHA-256:

`2d0c306a03c8a76ec1816ea984d9cd9242823278515778ca7123c9e31bef9e73`

## Critério de aceite do rerun

O G-110 somente poderá ser aprovado se o JSON real do `dev.9` apresentar simultaneamente:

- `browser_fail=0`;
- `server_fail=0`;
- `overall=PASS`;
- `residual_posts=0`;
- `residual_terms=0`;
- `residual_review_events=0`.

Se qualquer item falhar, preservar a evidência e diagnosticar sem alterar Stores/Contracts/Writers permanentes para mascarar o harness.

## Lifecycle

O shim e todo o Browser Acceptance são temporários. Após G-110 PASS, G-130 deverá remover:

- runner G-070;
- cache coherence G-070;
- runner G-110;
- submit shim G-110;
- JS de Browser Acceptance;
- flags de diagnostics.
