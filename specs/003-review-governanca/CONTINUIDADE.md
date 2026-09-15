# Continuidade — SPEC-003 Review & Governança

## Estado atual

- SPEC-001: concluída.
- SPEC-002: concluída, baseline `0.2.0-rc.1`.
- UX-001: baseline v1 congelada; UI as Code v0.2 é a referência executável.
- addendum de consumo: `ux/001-product-experience-knowledge-workspace/heritage-addendum-public-summary-v1.md`.
- SPEC-003: **R-001 PASS / R-010 PASS / G-001 PASS / G-030 PASS**.
- etapa ativa: **DS-010 — Design System Runtime Foundation**.

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

## Runtime mínimo e integração real

Arquivos permanentes:

- `class-review-contract.php`;
- `class-review-store.php`.

Evidências:

- unitários determinísticos: **PASS 19/19**;
- smoke `0.3.0-dev.1`: **PASS**;
- integração Comments API `0.3.0-dev.2`: **PASS 17/17**;
- cleanup do runner: `residual_posts=0`, `residual_terms=0`, `residual_review_events=0`;
- preservação de editorial/Summary/Classificação: PASS;
- corrupção do último evento: erro explícito de integridade comprovado.

**G-001: PASS.**  
**G-030: PASS determinístico + ambiental.**

## Design System Runtime Foundation

Documento: `design-system-runtime-plan.md`.

A fundação visual não será adiada para o fim da SPEC-003. Antes de abrir o handler/UI de Review, o runtime existente recebe o Design System v1 de forma isolada e sem alterar contratos funcionais.

Build: `0.3.0-dev.3`  
SHA-256: `081a9b0411e3e60020aa9b875c1a09d33c32ece2000011aa830186617eec542f`

Implementado no build:

- tokens canônicos como CSS Custom Properties;
- navy/blue/surfaces/border/radius/spacing/focus do UX-001;
- hierarquia visual para cabeçalho, contexto do artigo e tabela;
- Summary e Classificação em superfícies coerentes;
- inputs/selects/legacy reference refinados;
- responsive <=782px e <=520px;
- foco visível;
- nenhuma alteração em Summary Store, Classification Store ou Review Store;
- runner `class-review-diagnostics.php` removido do package.

O markup funcional permanece conservador nesta etapa. Tabs completas/Workspace com Review real entram somente depois do G-070.

## Próximo passo exato

1. substituir `0.3.0-dev.2` por `0.3.0-dev.3`;
2. abrir a listagem da Base de Conhecimento e um artigo;
3. confirmar que a linguagem visual mudou e continua coerente dentro do wp-admin;
4. salvar um Summary sem alterar seu conteúdo sem necessidade e confirmar feedback normal;
5. abrir Classificação e confirmar controles/vocabulários íntegros;
6. reduzir a janela para aproximadamente 782px e validar uso/foco;
7. retornar screenshot da listagem e do artigo;
8. com smoke PASS, fechar DS-010 e iniciar S004/G-070.

## UX / valor preservado

A tela histórica de artigo com **Resumo Executivo lateral** foi registrada como patrimônio de produto. No futuro Resolvedor, esse painel será uma projection read-only composta por owners canônicos, não um novo writer.

## Proibições mantidas

- não alterar `post_status` por decisão de governança;
- não escrever `post_content` ou `_elementor_data` de posts reais;
- não criar score;
- não criar `AI Ready`;
- não duplicar estado em meta + histórico;
- não criar tabela própria sem necessidade comprovada;
- não recuperar stores KB2Ops vazios por nostalgia arquitetural;
- não expor Review como ação antes de G-070.

## Gates

- R-001: **PASS**.
- R-010: **PASS**.
- G-001: **PASS**.
- G-030: **PASS**.
- DS-010: **AGUARDANDO SMOKE VISUAL `0.3.0-dev.3`**.
- G-070/G-110/G-130: pendentes na sequência normal.
