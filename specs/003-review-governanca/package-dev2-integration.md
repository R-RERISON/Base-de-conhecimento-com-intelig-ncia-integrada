# SPEC-003 — Package de integração `0.3.0-dev.2`

## Identificação

- build: `0.3.0-dev.2`;
- SHA-256 do ZIP: `915e2806f63c677fd2afe1bd60e7d0965f1c8667abb43a14c58ca53c7478e9c1`;
- base permanente: runtime `Review_Contract` + `Review_Store` já validado localmente;
- finalidade exclusiva: confirmar integração real com WordPress Comments API antes do Gate HTTP G-070.

## Tooling temporário

Arquivo:

`includes/class-review-diagnostics.php`

O runner:

- exige `manage_options`;
- usa POST + nonce;
- cria somente fixtures temporárias;
- não usa posts reais;
- cria 1 post, 1 page e 4 termos temporários;
- grava eventos `bdc_kb_review_event` somente no post fixture;
- valida leitura, transições, NO_CHANGE, nota obrigatória, limite de nota, post type, histórico e corrupção explícita;
- verifica que editorial, Summary, Classificação e markers legados da fixture não foram alterados pelo Review Store;
- remove post/page/termos/eventos no `finally`;
- só retorna `overall=PASS` com zero resíduos.

## Validação local do package

- PHP lint: **PASS 11/11**;
- arquivos no ZIP: **12**;
- Review runtime permanente não foi semanticamente alterado nesta build;
- o único runtime adicional é tooling de homologação explicitamente temporário.

## Resultado esperado

Arquivo:

`bdc-kb-review-diagnostics-YYYYMMDD-HHMMSS.json`

Critério esperado:

- `pass=17`;
- `fail=0`;
- `overall=PASS`;
- `residual_posts=0`;
- `residual_terms=0`;
- `residual_review_events=0`.

## Limitação intencional

Fault injection de `FAIL_SAFE` e `PARTIAL_FAILURE_CRITICAL` permanece nos unitários determinísticos. Segurança HTTP do writer final pertence ao G-070 e não é antecipada por este runner.
