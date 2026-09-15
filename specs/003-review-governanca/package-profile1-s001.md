# SPEC-003 — Package de profiling `0.3.0-profile.1`

## Identificação

- build: `0.3.0-profile.1`;
- SHA-256 do ZIP: `bb5a27fd455fc9cf6b2d98e4bb4e8855c2e07daecaa2a3c98a414377acaccf27`;
- base funcional: `0.2.0-rc.1`;
- finalidade exclusiva: Gate R-001 / current-state evidence.

## Delta contra `0.2.0-rc.1`

Somente três diferenças de package:

1. bootstrap: versão `0.3.0-profile.1`, flag `BDC_KB_REVIEW_PROFILE_BUILD=true` e require do profiler;
2. `class-plugin.php`: registro de `Review_Profiler`;
3. novo `class-review-profiler.php` temporário.

Nenhum arquivo permanente de Summary/Classificação foi alterado.

## Validação local

- PHP lint: **PASS 9/9**;
- busca estática por mutações no profiler (`INSERT/UPDATE/DELETE/REPLACE/ALTER/CREATE/DROP`, Post Meta writers, Post writers e Options writers): **nenhuma ocorrência**;
- SQL executado pelo profiler: apenas `SELECT`;
- relatório: download JSON; sem persistência interna.

## Privacidade / minimização

- não lê `post_title`, `post_content` ou `_elementor_data`;
- não exporta `_kb2ops_review_notes`;
- não exporta IDs de usuários;
- estado histórico é normalizado apenas para profiling;
- histórico exporta shape, tipos e agregados, não notas/editorial.

## Evidência esperada

`bdc-kb-review-profile-YYYYMMDD-HHMMSS.json`

A execução real ainda é necessária antes de R-001 PASS.
