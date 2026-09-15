# SPEC-003 — Package `0.3.0-dev.5` / G-070 rerun

## Motivo

O `0.3.0-dev.4` executou o writer real e obteve `15 PASS / 7 FAIL`. O padrão das falhas, combinado com os redirects observados (`saved` e `forbidden`), indicou incoerência de cache de comentários entre o request filho do loopback e o processo pai que executa as asserções.

## Alteração do build

O writer permanente (`class-review-admin.php`) e o store canônico (`class-review-store.php`) permanecem inalterados.

Foi adicionado apenas suporte temporário de teste:

- `class-review-http-cache-coherence.php`;
- registro condicionado por `BDC_KB_REVIEW_HTTP_DIAGNOSTICS_BUILD`;
- após resposta HTTP destinada a `bdc_kb_save_review`, o processo do runner chama `wp_cache_set_comments_last_changed()` antes de suas releituras.

## Invariantes preservados

- POST only;
- nonce vinculado ao post;
- `edit_post(post_id)`;
- reviewer capability em `Review_Store`;
- allowlist `target_state`/`note`;
- eventos append-only via Comments API;
- ausência de current-state paralelo em meta;
- PRG;
- `NO_CHANGE`, `FAIL_SAFE`, `PARTIAL_FAILURE_CRITICAL`;
- nenhum write em conteúdo editorial real;
- nenhum terceiro bloco vertical de Review.

## Critério de aceite do rerun

Executar o mesmo botão do runner no ambiente real e exigir:

- `22 PASS / 0 FAIL`;
- `overall=PASS`;
- `residual_posts=0`;
- `residual_terms=0`;
- `residual_review_events=0`.

Se qualquer item falhar, G-070 permanece bloqueado e G-110 não abre.

## Lifecycle

`class-review-http-cache-coherence.php` e `class-review-http-diagnostics.php` são artefatos temporários de homologação e devem ser removidos antes do RC em G-130.
