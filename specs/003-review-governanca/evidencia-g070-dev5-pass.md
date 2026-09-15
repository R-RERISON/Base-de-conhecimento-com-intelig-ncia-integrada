# Evidência G-070 — `0.3.0-dev.5`

## Resultado

Execução real do runner HTTP de Review & Governança em:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- plugin `0.3.0-dev.5`;
- multisite: não.

Arquivo bruto:

`evidencias/bdc-kb-review-http-security-20260915-165537.json`

Resultado final:

- **22 PASS / 0 FAIL**;
- `overall=PASS`;
- `duration_ms=8269`;
- `residual_posts=0`;
- `residual_terms=0`;
- `residual_review_events=0`.

## Segurança comprovada

PASS para:

- GET rejeitado com 405;
- nonce ausente/inválido;
- nonce vinculado ao post;
- payload ausente/escalar;
- mass assignment;
- estado inválido/XSS;
- post type fora do domínio;
- ID inexistente;
- negação por `edit_post(post_id)`;
- negação por `edit_others_posts` para decisão de reviewer.

## Writer e domínio comprovados

PASS para:

- POST válido para `in_review`;
- read-after-write pela store canônica;
- PRG `saved`;
- `NO_CHANGE` sem novo evento;
- nota obrigatória em `needs_changes`;
- limite máximo de bytes da nota;
- transição `in_review -> needs_changes`;
- transição `needs_changes -> approved`;
- persistência de estado, actor e evento canônico.

## Regressão e isolamento

PASS para preservação de:

- conteúdo editorial;
- `_elementor_data`;
- Summary da SPEC-001;
- Classificação da SPEC-002;
- markers legados usados pela fixture.

O runner declarou `fixture_only=true` e `modifies_real_content=false`.

## Cleanup

Após a execução:

- posts temporários: `0`;
- termos temporários: `0`;
- eventos Review temporários: `0`.

## Diagnóstico do dev.4 encerrado

O `0.3.0-dev.4` havia retornado `15 PASS / 7 FAIL` por incoerência de cache de Comments API entre o request loopback filho e o processo pai do harness.

O `0.3.0-dev.5` alterou somente o harness temporário através de `class-review-http-cache-coherence.php`. O writer permanente `class-review-admin.php` e a store permanente `class-review-store.php` não foram relaxados ou modificados para obter o PASS.

A repetição real demonstrou que as falhas H14-H20 eram do mecanismo de verificação do harness, não do contrato permanente de Review.

## Limitações preservadas

- o loopback do runner usa `sslverify=false` apenas para não transformar CA interna em falso negativo de aplicação; TLS não é validado por este gate;
- `FAIL_SAFE` e `PARTIAL_FAILURE_CRITICAL` continuam cobertos pelos testes determinísticos da store;
- UI, teclado, reflow e Browser Acceptance pertencem ao G-110.

## Decisão

**G-070 — PASS determinístico + ambiental.**

Está autorizado iniciar **G-110 — Knowledge Workspace / Browser Acceptance**.

Os runners e hooks temporários permanecem identificados para remoção obrigatória no G-130 antes do package RC limpo.