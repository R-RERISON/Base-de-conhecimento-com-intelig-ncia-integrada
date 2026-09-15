# Evidência G-110 — `0.3.0-dev.9` — colisão `form.submit` reproduzida

## Resultado real

Evidência: `evidencias/bdc-kb-g110-browser-acceptance-20260915-193517.json`.

- browser: **7 PASS / 1 FAIL**;
- server: **3 PASS / 4 FAIL**;
- `overall=FAIL`;
- cleanup: **0 posts / 0 termos / 0 eventos residuais**.

A exceção permaneceu:

`form.submit is not a function`

## Diagnóstico

O `class-workspace-browser-submit-shim.php` do `dev.9` tentou renomear controles `name="submit"` após o carregamento dos iframes. No browser real isso não restaurou de forma confiável o método `submit` da instância `HTMLFormElement` usada pelo runner.

A correção correta pertence ao chamador do harness: invocar explicitamente o método nativo do protótipo:

`loaded.win.HTMLFormElement.prototype.submit.call(form)`

Isso ignora qualquer named property do formulário chamada `submit`.

## Impacto

Nenhum POST real de Summary/Classificação/Review foi concluído antes da exceção; portanto S04-S07 falharam em cascata. S01-S03 e o cleanup passaram. Não há evidência de regressão no runtime permanente.

## Decisão

- G-110 permanece **NÃO APROVADO**;
- o FAIL do `dev.9` é preservado;
- remover o shim ineficaz;
- corrigir somente `assets/js/browser-acceptance.js`;
- rerun no `0.3.0-dev.10`.
