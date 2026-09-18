# T100E-E6 — Workspace Regression Matrix Contract v1

**Status:** IMPLEMENTADO / PASS LOCAL / HOMOLOGAÇÃO PENDENTE  
**Build:** `0.4.0-g245-e6.1`  
**Modo:** read-only

## Objetivo

Consolidar em uma única evidência a regressão funcional da Post Management Workspace sobre o corpus real, sem alterar artigos, metadata, journals ou locks.

## Superfície

O runner é oculto e acessível apenas por rota administrativa direta:

`/wp-admin/admin.php?page=bdc-kb-t100e-e6`

Não cria item de menu no produto.

## Cobertura ambiental

Duas passagens completas sobre o corpus agregam:

- source kind;
- dry-run status;
- operational status;
- lock status;
- journal presence/latest state;
- authorization readiness;
- source kind × operational status;
- errors/throwables;
- safety violations.

O relatório não exporta:
- conteúdo editorial;
- URLs;
- lista de post IDs;
- payloads Elementor;
- rollback capsules.

## Casos mínimos de fonte

A matriz exige presença ambiental de:

- Gutenberg/Core Blocks;
- legacy_html;
- plain_text;
- Elementor;
- mixed.

## Casos contratuais puros

Sem write e sem criar estado artificial no banco:

- ready + no journal + free lock → ready_for_authorization;
- held lock → blocked;
- rolled_back terminal journal → reassessment permitido;
- applied journal sobre fonte ainda legacy → blocked;
- mixed → human_review_required;
- review_required → human_review_required;
- native Gutenberg noop → no_action_required;
- stale guard fresh → fresh;
- source drift → stale.

## Integridade

O runner calcula fingerprint antes/depois contendo somente hashes de:

- post_content;
- _elementor_data;
- Block Migration journal metadata;
- Block Migration lock metadata.

PASS exige fingerprint before == after.

## Determinismo

A matriz é executada duas vezes.

PASS exige:
- zero errors;
- zero throwables;
- zero safety violations;
- matrix hash pass 1 == pass 2;
- editorial fingerprint before == after;
- cobertura mínima completa;
- casos puros 9/9 PASS.

## Segurança

O runner não pode:

- persistir estado;
- adquirir/release lock;
- escrever post_content;
- escrever _elementor_data;
- escrever journal;
- executar shortcode;
- renderizar block dinâmico;
- chamar rede externa.

## Próximo gate

Após PASS ambiental:

**T100E-E7 — Production Readiness Exit**, seguido por **G-250 Lifecycle / RC**.
