# SPEC-003 — Profiler Review & Governança

Ferramenta temporária, read-only, usada exclusivamente no Gate R-001.

## Arquivo

- `class-review-profiler.php`
- build de homologação: `0.3.0-profile.1`
- package SHA-256: `bb5a27fd455fc9cf6b2d98e4bb4e8855c2e07daecaa2a3c98a414377acaccf27`

## Stores lidos

- `_kb2ops_review_state`
- `_kb2ops_review_notes`
- `_kb2ops_reviewed_at`
- `_kb2ops_reviewed_by`
- `_kb2ops_include_ai`
- `_kb2ops_review_history`

## Segurança

- execução somente por POST;
- nonce específico;
- `manage_options`;
- apenas SELECT/read APIs;
- não cria ou atualiza posts, metas, taxonomias, options, usuários, transients ou tabelas;
- não lê `post_title`, `post_content` ou `_elementor_data`;
- não exporta conteúdo de `_kb2ops_review_notes`;
- não exporta IDs de usuários;
- JSON é baixado para o operador e não é persistido pelo plugin.

## Evidência esperada

`bdc-kb-review-profile-YYYYMMDD-HHMMSS.json`

O relatório mede cobertura, distribuição de estados, relação com `post_status`, validade agregada de actor/timestamp, shape do histórico e coerência entre os stores.

## Regra

O profiler mede o legado; não decide automaticamente estado canônico, primitive ou migração. Após R-001 ele deve sair do package e permanecer apenas como ferramenta versionada de homologação.
