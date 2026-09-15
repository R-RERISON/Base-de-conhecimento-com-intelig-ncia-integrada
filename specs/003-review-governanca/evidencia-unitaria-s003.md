# SPEC-003 — Evidência unitária S003 / Review Store

## Escopo

Validação determinística local do Domain Contract R-010 antes de expor handler HTTP ou UI.

Arquivos permanentes:

- `includes/class-review-contract.php`;
- `includes/class-review-store.php`.

Teste versionado:

- `tests/unit/spec003-review-store.php`.

SHA-256 do harness local executado:

`9299a479ece1672891ff13bb42b85f99793d80b9a1113b6e74aba0aa286de598`

## Resultado

**PASS 19 / FAIL 0**.

Cenários:

1. contrato exato de estados;
2. leitura `unreviewed` sem side effect;
3. post type fora do escopo;
4. submissão para `in_review` sem capability de reviewer;
5. aprovação exige reviewer;
6. `edit_post` obrigatório;
7. `needs_changes` exige nota;
8. `excluded` exige nota;
9. sanitização de nota;
10. limite de 2000 bytes;
11. no-op gera zero write;
12. target inválido gera zero write;
13. transição inválida gera zero write;
14. cadeia válida + histórico;
15. falha de inserção -> `FAIL_SAFE`;
16. divergência de releitura -> compensação -> `FAIL_SAFE`;
17. falha da compensação -> `PARTIAL_FAILURE_CRITICAL`;
18. último evento malformado -> integrity error;
19. paginação do histórico.

A linha de log `REVIEW_PARTIAL_FAILURE_CRITICAL` apareceu somente no cenário de fault injection esperado.

## PHP lint

Runtime do package `0.3.0-dev.1`: **PASS 10/10 arquivos PHP**.

## Delta contra baseline

Comparação do package `0.3.0-dev.1` com `0.2.0-rc.1`:

- bootstrap/version/require alterado;
- novo `class-review-contract.php`;
- novo `class-review-store.php`;
- demais arquivos do plugin idênticos.

## Gate

- G-030 determinístico local: **PASS**.
- G-001 estático/bootstrap: **PASS local**, pendente smoke real de instalação/ativação antes de promoção ambiental.
