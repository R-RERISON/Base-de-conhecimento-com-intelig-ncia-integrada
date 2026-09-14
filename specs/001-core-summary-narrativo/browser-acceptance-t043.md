# T043 — Browser Acceptance temporário com fixture

> Estado: **PASS — execução real no `0.1.0-dev.5`.**

## Objetivo

Comprovar G-110 no WordPress real sem usar conteúdo real e sem deixar storage de teste.

## Ativação

O ambiente de homologação não depende de `wp-config.php`. O build de homologação carregou internamente as ferramentas temporárias para `manage_options`.

## Fluxo dev.5 executado

1. abrir **Base de Conhecimento**;
2. clicar **Preparar cenário temporário G-110**;
3. criar `post` draft marcado por `_bdc_kb_browser_fixture=spec001-browser-v1`;
4. preencher automaticamente os três campos de teste;
5. clicar **Salvar Summary** e aguardar o redirect;
6. reler os valores persistidos;
7. validar Tab/Shift+Tab;
8. reduzir a janela a `<=782px`;
9. registrar explicitamente PASS/FAIL para teclado/foco e usabilidade estreita;
10. gerar JSON e hard-delete da fixture.

## Resultado real dev.5

- `manual_pass=2`;
- `manual_fail=0`;
- `auto_fail=0`;
- `overall=PASS`;
- viewport mínimo observado `671x660`;
- fixture deletada;
- `residual_fixtures=0`;
- nenhum conteúdo real modificado.

Checks automáticos comprovaram shell do wp-admin, ausência de sidebar secundária própria, listagem/seleção, contexto read-only, labels, feedback textual, persistência/releitura, preservação editorial, PRG e viewport estreito.

Evidência raw: `evidencias/bdc-kb-browser-acceptance-20260914-193151.json`.

## Remoção

A classe temporária, hooks e markers foram removidos do package `0.1.0-rc.1` em T044A. O gate G-110 permanece PASS; o lifecycle do RC é tratado separadamente em G-130.
