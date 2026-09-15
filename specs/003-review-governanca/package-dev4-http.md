# SPEC-003 — Package `0.3.0-dev.4` / Gate G-070

## Objetivo

Validar o writer HTTP permanente de Review & Governança através do `admin-post.php` real antes de expor a UI funcional no Knowledge Workspace.

## Package

- versão: `0.3.0-dev.4`;
- SHA-256: `31564fb8bd0da0e0e501c0edeee434f75ce01a1a95410422065a219fa9b033cb`;
- PHP lint: PASS 12/12;
- runner anterior `Review_Diagnostics`: ausente;
- writer permanente: `class-review-admin.php`;
- runner temporário: `class-review-http-diagnostics.php`.

## Contrato HTTP permanente

Action:

`bdc_kb_save_review`

Regras:

1. POST only;
2. post válido do tipo `post`;
3. capability por objeto `edit_post(post_id)`;
4. nonce vinculado ao `post_id`;
5. payload `review` array;
6. allowlist exata `target_state`, `note`;
7. `Review_Store` continua owner da validação de transição/capability de reviewer/persistência;
8. PRG sempre após processamento normal;
9. `FAIL_SAFE` e `PARTIAL_FAILURE_CRITICAL` preservam status explícito.

## Runner G-070

Usa somente fixtures temporárias:

- 2 posts;
- 1 page;
- 4 termos canônicos;
- eventos `bdc_kb_review_event` apenas nas fixtures.

Exercita:

- GET -> 405;
- nonce ausente/inválido;
- nonce vinculado ao post;
- payload ausente/escalar;
- mass assignment;
- estado inválido/XSS;
- post type fora do escopo;
- ID inexistente;
- capability `edit_post` por objeto;
- capability `edit_others_posts` para decisão de reviewer;
- POST válido + PRG + reread;
- NO_CHANGE sem write;
- nota obrigatória;
- limite de bytes;
- transições válidas para `needs_changes` e `approved`;
- regressão de editorial/Summary/Classificação/legado;
- cleanup zero resíduos.

## Instrumentação de capability

O ambiente corporativo já demonstrou que roles reduzidas podem ser desviadas por política externa antes do `admin-post.php`. Para testar o ramo de autorização do nosso handler sem depender dessa política, o runner usa negação assinada por nonce, restrita às fixtures e somente neste build de homologação.

Essa instrumentação é temporária e deve ser removida antes do RC.

## Critério de aceite

Esperado:

- `22 PASS / 0 FAIL`;
- `overall=PASS`;
- `residual_posts=0`;
- `residual_terms=0`;
- `residual_review_events=0`.

## Limite

Este build NÃO adiciona UI de Review ao Workspace. G-110 permanece bloqueado até G-070 PASS.
