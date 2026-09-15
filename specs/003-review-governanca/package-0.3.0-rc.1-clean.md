# Package limpo — `0.3.0-rc.1`

## Origem

RC gerado após o **G-110 PASS** no ambiente real com `0.3.0-dev.11`.

Evidência de entrada:

`evidencias/bdc-kb-g110-browser-acceptance-20260915-200043.json`

Resultado de entrada:

- browser: **22 PASS / 0 FAIL**;
- server: **7 PASS / 0 FAIL**;
- `overall=PASS`;
- cleanup: zero posts, termos e eventos residuais.

## Lifecycle cleanup

Antes do package RC foram removidos do runtime:

- `includes/class-review-http-cache-coherence.php`;
- `includes/class-review-http-diagnostics.php`;
- `includes/class-workspace-browser-diagnostics.php`;
- `assets/js/browser-acceptance.js`;
- `BDC_KB_REVIEW_HTTP_DIAGNOSTICS_BUILD`;
- `BDC_KB_WORKSPACE_BROWSER_DIAGNOSTICS_BUILD`;
- hooks condicionais dos runners G-070/G-110.

Permanecem somente os componentes do produto: Summary, Classificação, Review & Governança, Histórico read-only, Workspace e seus assets permanentes.

## Versão

- header WordPress: `0.3.0-rc.1`;
- `BDC_KB_VERSION`: `0.3.0-rc.1`.

## Reprodutibilidade

O diretório de build foi reconstruído a partir do `main` limpo após o cleanup, e não derivado por simples remoção de arquivos de um ZIP de desenvolvimento.

Foram comparados os blobs locais contra os blobs GitHub dos **15 arquivos permanentes** do plugin. Resultado: **15/15 correspondências exatas** e zero arquivo excedente no build.

## Validação local

- PHP lint: **PASS 11/11**;
- `assets/js/workspace.js`: syntax PASS;
- busca por classes/flags/assets de diagnóstico temporários: zero referências;
- `unzip -t`: PASS;
- pasta raiz instalável: `base-conhecimento-inteligencia-integrada/`;
- nenhum arquivo de diagnóstico no ZIP.

## Artefato

Arquivo:

`base-conhecimento-inteligencia-integrada-0.3.0-rc.1.zip`

SHA-256:

`7f681a3f62d792d30ccb016ae64b03e83d5cc46c4b2b1e2c2d96d41e3dfd0db5`

Tamanho: `31.036 bytes`.

## Estado do gate

- G-110: **PASS determinístico + ambiental**;
- G-130: **ACTIVE**;
- cleanup e geração do RC: concluídos;
- falta somente lifecycle ambiental do RC: instalar/substituir, desativar, ativar e executar smoke final sem regressão;
- baseline da SPEC-003 ainda não deve ser congelada antes desse smoke.
