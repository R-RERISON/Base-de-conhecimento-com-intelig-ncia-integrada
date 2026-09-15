# Package `0.3.0-dev.11` — correção PRG da Classificação

## Motivo

O Browser Acceptance real do `0.3.0-dev.10` executou o fluxo completo e terminou com:

- browser: 21 PASS / 1 FAIL;
- server: 7 PASS / 0 FAIL;
- cleanup zero resíduos;
- `overall=FAIL`.

O único FAIL foi `G110-B10`: Classificação persistiu corretamente, mas o redirect não preservou a tab `classification`.

## Alteração funcional

Arquivo permanente alterado:

`includes/class-classification-admin.php`

Mudança:

`Classification_Admin::redirect()` passa a incluir:

`'tab' => 'classification'`

O save, store, taxonomias, nonce, capability, normalização e tratamento fail-safe permanecem inalterados.

## Build

Versão:

`0.3.0-dev.11`

Browser Acceptance continua habilitado exclusivamente para o rerun final do G-110.

## Validação local do package

- PHP lint: PASS 14/14;
- JavaScript syntax: PASS 2/2;
- integridade ZIP: PASS;
- raiz de plugin WordPress: PASS.

SHA-256:

`85766f0298afe5bb10c779f0901e7b3e2b010792b66034e0e86f1f4023f7d3c8`

## Critério do rerun

O `dev.11` deve executar o Browser Acceptance completo. Não basta validar somente B10.

Para promover G-110:

- `browser_fail=0`;
- `server_fail=0`;
- `overall=PASS`;
- `residual_posts=0`;
- `residual_terms=0`;
- `residual_review_events=0`.

Somente depois abrir G-130 para remoção de toda instrumentação temporária e geração de RC limpo/reproduzível a partir do repositório.
