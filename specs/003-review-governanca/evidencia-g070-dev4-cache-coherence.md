# SPEC-003 — Evidência G-070 `0.3.0-dev.4` e diagnóstico de coerência de cache

## Resultado real

Ambiente:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- plugin `0.3.0-dev.4`;
- multisite: não.

Arquivo bruto preservado em:

`evidencias/bdc-kb-review-http-security-20260915-155801.json`

Resultado:

- PASS: 15;
- FAIL: 7;
- overall: FAIL;
- cleanup: `residual_posts=0`, `residual_terms=0`, `residual_review_events=0`.

## Leitura técnica

Os testes H01-H13 passaram, cobrindo fixture, método HTTP, nonce, nonce por post, payload, mass assignment, estado inválido, post type, ID inexistente e negação de `edit_post`.

A sequência H14-H20 falhou em bloco. Entretanto, o próprio request HTTP válido observou `302 + saved`, e a negação de reviewer observou `302 + forbidden`. Isso demonstra que o request filho alcançou o writer e retornou os statuses esperados, enquanto as asserções executadas no processo pai continuaram incompatíveis com a mutação recém-realizada.

A causa provável e consistente com o comportamento observado é cache de `WP_Comment_Query` no processo pai do runner. O writer é chamado por `wp_remote_*` em outro request PHP; esse request atualiza a Comments API e o respectivo `last_changed`, mas o processo pai pode continuar com o marcador/cache de comentários já carregado. Assim, `Review_Store::read()` e `event_count()` no runner podem reler a visão anterior mesmo após `302/saved`.

## Decisão

Não alterar o writer permanente nem o `Review_Store` para compensar um problema do harness.

Foi criado suporte temporário de coerência de cache:

`class-review-http-cache-coherence.php`

Ele é carregado somente enquanto `BDC_KB_REVIEW_HTTP_DIAGNOSTICS_BUILD=true` e, após um loopback destinado a `Review_Admin::ACTION`, chama `wp_cache_set_comments_last_changed()` no processo do runner antes das asserções seguintes.

Esse helper:

- não altera payload, nonce, capability ou PRG;
- não escreve Review;
- não altera posts reais;
- não modifica o contrato canônico;
- deve ser removido junto com o runner antes do RC.

## Gate

**G-070 permanece NÃO APROVADO.**

O `0.3.0-dev.4` é evidência válida de FAIL do harness no ambiente real. O `0.3.0-dev.5` deve repetir exatamente o mesmo conjunto de 22 testes. Somente `22 PASS / 0 FAIL / overall=PASS` libera G-110.
