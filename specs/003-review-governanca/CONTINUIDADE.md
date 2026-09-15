# Continuidade — SPEC-003 Review & Governança

## Estado atual

- SPEC-001: concluída.
- SPEC-002: concluída, baseline `0.2.0-rc.1`.
- UX-001: baseline v1 congelada; UI as Code v0.2 aprovado como referência executável.
- SPEC-003: **S001 em execução**.
- T001–T006: concluídas.
- Implementação de runtime permanece **bloqueada**.

## Baseline histórica fechada

Documento: `baseline-historico-s001.md`.

Stores históricos sob profiling:

- `_kb2ops_review_state`;
- `_kb2ops_review_notes`;
- `_kb2ops_reviewed_at`;
- `_kb2ops_reviewed_by`;
- `_kb2ops_include_ai`;
- `_kb2ops_review_history`.

Estados históricos `unreviewed`, `in_review`, `approved`, `excluded` continuam apenas candidatos.

## Profiler autorizado

- build: `0.3.0-profile.1`;
- ferramenta versionada: `tools/homologation/spec003/class-review-profiler.php`;
- package SHA-256: `bb5a27fd455fc9cf6b2d98e4bb4e8855c2e07daecaa2a3c98a414377acaccf27`;
- modo: read-only;
- capability: `manage_options`;
- não exporta notas humanas ou IDs de usuários;
- não lê conteúdo editorial;
- não persiste relatório.

## Próximo passo exato

1. instalar/substituir temporariamente pelo `0.3.0-profile.1`;
2. abrir **Base de Conhecimento** com usuário administrador;
3. executar **Profiling Review/Governança — gerar JSON**;
4. retornar o arquivo `bdc-kb-review-profile-*.json`;
5. analisar T007–T010;
6. registrar evidência e fechar R-001;
7. somente então decidir o Domain Contract R-010.

## O que NÃO fazer agora

- não criar meta canônica de review;
- não promover `approved/in_review/...` para contrato novo;
- não criar reviewer/responsável;
- não criar histórico persistente novo;
- não criar score;
- não criar `AI Ready`;
- não migrar legado;
- não iniciar S003 enquanto R-001 e R-010 não estiverem PASS.

## Gate atual

- R-001: NOT_RUN — aguardando JSON real.
- R-010: BLOCKED por R-001.
- G-001/G-030/G-070/G-110/G-130: BLOCKED.

## Regra

A primeira entrega da SPEC-003 deve aumentar conhecimento sobre o estado real do ambiente, não aumentar quantidade de código permanente.
