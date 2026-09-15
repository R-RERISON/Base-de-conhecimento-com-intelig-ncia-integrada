# Continuidade — SPEC-003 Review & Governança

## Estado atual

- SPEC-001: concluída.
- SPEC-002: concluída, baseline `0.2.0-rc.1`.
- UX-001: baseline v1 congelada; UI as Code v0.2 é a referência executável.
- addendum de consumo: `ux/001-product-experience-knowledge-workspace/heritage-addendum-public-summary-v1.md`.
- SPEC-003: **R-001 PASS / R-010 PASS / G-001 PASS / G-030 local PASS**.

## Evidência do ambiente real

Profiler `0.3.0-profile.1` executado em WordPress 6.9.4 / PHP 8.5.10:

- corpus: 622 posts;
- seis stores históricos de review analisados;
- meta rows encontradas: 0;
- posts com qualquer dado histórico de review: 0;
- writes do profiler: 0;
- conteúdo editorial lido: não;
- notas/IDs de usuários exportados: não.

Documento: `evidencia-profiling-s001.md`.

Conclusão: não existe passivo real de migração de Review/Governança no ambiente analisado.

## Domain Contract aprovado

Documento: `domain-contract.md`.

Decisões principais:

- owner: Review & Governança;
- estado inicial implícito: `unreviewed`;
- estados: `unreviewed`, `in_review`, `needs_changes`, `approved`, `excluded`;
- fonte canônica: eventos append-only via WordPress Comments API;
- `comment_type`: `bdc_kb_review_event`;
- estado atual = último evento válido;
- actor = `user_id` do evento;
- data = `comment_date_gmt`;
- sem meta paralela de current state;
- sem `_reviewed_by`/`_reviewed_at` duplicados;
- sem tabela customizada;
- sem migração/dual-read/dual-write legado;
- `AI Ready` e `_kb2ops_include_ai` permanecem fora do domínio.

## Runtime mínimo

Arquivos permanentes:

- `class-review-contract.php`;
- `class-review-store.php`.

Evidência local:

- unitários: **PASS 19/19**;
- G-030 determinístico local: PASS.

## Smoke ambiental `0.3.0-dev.1`

O operador confirmou o smoke completo com a resposta `funcionou, pode seguir` após validar ativação, Base de Conhecimento, listagem/artigo, Summary/Classificação e ausência de profiler/UI Review.

Documento: `evidencia-smoke-dev1-s003.md`.

**G-001: PASS.**

## Build ativo para integração ambiental

Package: `0.3.0-dev.2`  
SHA-256: `915e2806f63c677fd2afe1bd60e7d0965f1c8667abb43a14c58ca53c7478e9c1`

Tooling temporário:

- `class-review-diagnostics.php`;
- capability `manage_options`;
- POST + nonce;
- cria somente 1 post, 1 page e 4 termos de fixture;
- exercita eventos `bdc_kb_review_event` reais;
- verifica preservação de editorial, Summary, Classificação e markers legados da fixture;
- injeta um evento malformado somente na fixture para comprovar erro explícito de integridade;
- cleanup no `finally`;
- não toca posts reais.

Documento: `package-dev2-integration.md`.

## Próximo passo exato

1. substituir `0.3.0-dev.1` por `0.3.0-dev.2`;
2. abrir **Base de Conhecimento** como administrador;
3. clicar **Executar diagnóstico Review/Governança e gerar JSON**;
4. retornar `bdc-kb-review-diagnostics-*.json`;
5. exigir `17 PASS / 0 FAIL / overall=PASS`;
6. exigir `residual_posts=0`, `residual_terms=0`, `residual_review_events=0`;
7. somente depois iniciar S004 / writer HTTP permanente e Gate G-070.

## UX / valor preservado

A tela histórica de artigo com **Resumo Executivo lateral** foi registrada como patrimônio de produto. No futuro Resolvedor, esse painel será uma projection read-only composta por owners canônicos, não um novo writer.

## Proibições mantidas

- não alterar `post_status` por decisão de governança;
- não escrever `post_content` ou `_elementor_data` de posts reais;
- não criar score;
- não criar `AI Ready`;
- não duplicar estado em meta + histórico;
- não criar tabela própria sem necessidade comprovada;
- não recuperar stores KB2Ops vazios por nostalgia arquitetural.

## Gates

- R-001: **PASS**.
- R-010: **PASS**.
- G-001: **PASS**.
- G-030: **PASS local / integração ambiental T039 pendente**.
- G-070/G-110/G-130: bloqueados pela sequência normal.
