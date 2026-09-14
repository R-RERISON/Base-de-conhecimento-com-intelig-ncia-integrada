# T043 — Browser Acceptance guiado e temporário

> Estado: **PREPARADO — execução manual ainda NOT_RUN**.

## Objetivo

Registrar G-110 com evidência humana estruturada, sem criar storage permanente, options, transients, tabelas ou logs de teste no WordPress.

## Ativação

O painel só existe enquanto:

```php
define( 'BDC_KB_ENABLE_DIAGNOSTICS', true );
```

Ele exige `manage_options`, POST e nonce próprio.

## Fluxo

1. executar primeiro o diagnóstico onclick automatizado e obter o JSON técnico;
2. validar manualmente a tela real no wp-admin;
3. testar listagem, editor, save, redirect, reload, teclado e viewport estreito;
4. preencher navegador/versão e viewport usados;
5. marcar somente os checks efetivamente observados;
6. clicar **Gerar JSON do browser acceptance**;
7. enviar o JSON junto com o JSON técnico para revisão;
8. remover/desativar a flag de diagnóstico após a coleta.

## Checks G-110

- shell do wp-admin sem segunda sidebar;
- listagem legível/paginada;
- título/contexto read-only e labels associados;
- POST-Redirect-GET sem reenvio ao refresh;
- feedback textual de sucesso/erro;
- navegação por teclado e foco utilizáveis;
- viewport administrativo estreito sem perda funcional;
- valores persistidos reaparecem após redirect/reload.

## Natureza da evidência

O JSON usa `source=operator_assertion` e é explicitamente **manual_browser_observation**. Ele não se apresenta como automação E2E.

Nenhuma resposta é persistida no WordPress. O resultado é devolvido apenas como arquivo JSON.

## Regra de promoção

G-110 permanece `NOT_RUN` até recebermos o JSON real do ambiente alvo e revisarmos o contexto de execução. Um JSON com qualquer check `FAIL` não promove o gate.

## Remoção

`class-browser-acceptance.php` é temporário e deve ser removido junto com `class-diagnostics-runner.php` antes de T044/G-130/package.
