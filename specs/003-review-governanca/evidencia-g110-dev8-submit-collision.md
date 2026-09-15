# Evidência G-110 — `0.3.0-dev.8` — colisão `form.submit`

## Resultado bruto

Evidência: `evidencias/bdc-kb-g110-browser-acceptance-20260915-192838.json`.

Ambiente real:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- plugin `0.3.0-dev.8`;
- multisite: não.

Resumo:

- browser: `7 PASS / 1 FAIL`;
- server: `3 PASS / 4 FAIL`;
- `overall=FAIL`;
- cleanup: `residual_posts=0`, `residual_terms=0`, `residual_review_events=0`.

O resultado é preservado como FAIL real e não é reinterpretado como PASS.

## O que foi comprovado antes da exceção

Passaram no browser real:

- cinco tabs autorizadas;
- Context Header + Main Work Area;
- ausência de features proibidas;
- `ArrowLeft` / `ArrowRight` / `Home` / `End`;
- foco visível;
- semântica de links/Enter;
- render do formulário Summary + labels associados.

Também passaram no servidor, antes de qualquer mutação de domínio:

- `post_status` permaneceu `draft`;
- `post_content` permaneceu intacto;
- `_elementor_data` permaneceu intacto.

## Causa da interrupção

O runner abortou ao tentar submeter o primeiro formulário com:

`form.submit is not a function`

O formulário WordPress contém um controle nomeado `submit`. Em HTML, controles nomeados podem sombrear propriedades do objeto `HTMLFormElement`; nesse contexto, `form.submit` deixou de referenciar o método nativo e passou a resolver para o controle do formulário.

Consequência: o Browser Acceptance parou antes de efetuar o POST de Summary. Por isso as asserções server-side S04-S07 falharam em cascata por ausência das mutações esperadas, e não por evidência de regressão dos stores permanentes.

## Correção `0.3.0-dev.9`

A correção é deliberadamente test-only:

- novo `class-workspace-browser-submit-shim.php`;
- carregado somente no build de diagnostics G-110;
- antes do runner, intercepta somente os iframes criados pelo Browser Acceptance;
- dentro desses iframes, renomeia controles `name="submit"` para `name="bdc_g110_submit"`;
- nenhum handler depende desse nome;
- nenhum arquivo de Store/Contract/Writer permanente foi alterado;
- o runner funcional permanece o mesmo.

O shim deve ser removido no G-130 junto com os demais artefatos temporários.

## Decisão de gate

**G-110 permanece ACTIVE / NÃO APROVADO.**

O `0.3.0-dev.9` deve repetir o Browser Acceptance completo. Somente `browser_fail=0`, `server_fail=0`, `overall=PASS` e cleanup zero resíduos autorizam promover G-110 para PASS.
