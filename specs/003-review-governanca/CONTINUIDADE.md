# Continuidade — SPEC-003 Review & Governança

## Estado atual

- SPEC-001: concluída.
- SPEC-002: concluída, baseline `0.2.0-rc.1`.
- UX-001: baseline v1 congelada; UI as Code v0.2 é a referência executável.
- addendum de consumo: `ux/001-product-experience-knowledge-workspace/heritage-addendum-public-summary-v1.md`.
- SPEC-003: **R-001 PASS / R-010 PASS / G-001 PASS / G-030 PASS / DS-010 PASS / G-070 PASS**.
- etapa ativa: **G-110 — Knowledge Workspace / Browser Acceptance**.
- próximo build sugerido: **`0.3.0-dev.6`**.

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

## Runtime mínimo e integração

Evidências acumuladas:

- unitários determinísticos: **PASS 19/19**;
- smoke `0.3.0-dev.1`: **PASS**;
- integração Comments API `0.3.0-dev.2`: **PASS 17/17**;
- preservação de editorial/Summary/Classificação: PASS;
- corrupção do último evento: erro explícito de integridade comprovado.

**G-001: PASS.**  
**G-030: PASS determinístico + ambiental.**

## Design System Runtime Foundation

Documento: `evidencia-design-system-runtime-dev3.md`.

Build validado: `0.3.0-dev.3`.

Comprovado no ambiente real:

- tokens/surfaces/hierarquia do Design System presentes no runtime;
- Knowledge List mais legível;
- contexto do artigo, Summary e Classificação visualmente coerentes;
- nenhuma regressão funcional reportada.

A conclusão arquitetural permanece: `Summary -> Classificação` não pode crescer com um terceiro bloco vertical. G-110 deve convergir a tela para o Knowledge Workspace/tabs.

**DS-010: PASS — Runtime Foundation.**

## G-070 — Writer HTTP e Segurança

Writer permanente: `class-review-admin.php`.

Contrato preservado:

- POST only;
- nonce vinculado ao post;
- `edit_post(post_id)`;
- allowlist `target_state`/`note`;
- reviewer capability validada pelo `Review_Store`;
- PRG;
- `NO_CHANGE`, `FAIL_SAFE`, `PARTIAL_FAILURE_CRITICAL` preservados.

### Histórico `0.3.0-dev.4`

Evidência:

`evidencias/bdc-kb-review-http-security-20260915-155801.json`

Resultado histórico preservado:

- `15 PASS / 7 FAIL`;
- `overall=FAIL`;
- cleanup zero resíduos.

As falhas H14-H20 foram diagnosticadas como incoerência de cache de Comments API no processo pai do harness após loopback HTTP.

Documento: `evidencia-g070-dev4-cache-coherence.md`.

### Correção test-only `0.3.0-dev.5`

Foi introduzido `class-review-http-cache-coherence.php`, carregado apenas no build de diagnóstico. A alteração avança o marcador de cache de comments após o loopback do writer Review.

Não foram relaxados nem alterados para mascarar o teste:

- `class-review-admin.php`;
- `class-review-store.php`;
- `class-review-contract.php`.

### Evidência final real `0.3.0-dev.5`

Arquivo:

`evidencias/bdc-kb-review-http-security-20260915-165537.json`

Ambiente:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- plugin `0.3.0-dev.5`;
- multisite: não.

Resultado:

- **22 PASS / 0 FAIL**;
- `overall=PASS`;
- `residual_posts=0`;
- `residual_terms=0`;
- `residual_review_events=0`;
- duração observada: `8269 ms`.

Comprovado:

- camada negativa de segurança;
- nonce post-bound;
- object capability;
- reviewer capability;
- POST válido + PRG + read-after-write;
- NO_CHANGE sem write;
- nota obrigatória/limite;
- `needs_changes` e `approved` reais;
- preservação de editorial/Summary/Classificação/legado da fixture;
- cleanup integral.

Documento: `evidencia-g070-dev5-pass.md`.

**G-070: PASS determinístico + ambiental.**

## G-110 — etapa ativa

Plano: `g110-workspace-browser-acceptance-plan.md`.

A autoridade de experiência continua sendo a UX-001 congelada:

- Context Header;
- tabs horizontais;
- Main Work Area;
- Context Panel somente quando real/acionável;
- uma coluna em `<=782px`;
- permanência no contexto do mesmo artigo após save.

### Tabs autorizadas

- Visão geral;
- Summary;
- Classificação;
- Review & Governança;
- Histórico read-only, derivado exclusivamente dos eventos `bdc_kb_review_event`.

### Primeira implementação

Próximo build sugerido: `0.3.0-dev.6`.

Prioridade:

1. W-001 — refatorar a tela atual para o shell do Knowledge Workspace sem mudar os writers existentes;
2. W-002 — integrar Review como tab própria usando os contratos permanentes já aprovados;
3. depois W-003 — Histórico read-only;
4. só então Browser Acceptance completo.

### Browser Acceptance obrigatório

Validar no mínimo:

- Knowledge List -> Workspace;
- troca de tabs;
- Summary sem regressão;
- Classificação sem regressão;
- Review `unreviewed -> in_review -> needs_changes -> approved`;
- NO_CHANGE;
- capabilities/forbidden;
- Histórico consistente;
- teclado/foco;
- viewports 1440/1024/782/~492;
- zero overflow horizontal;
- cleanup zero resíduos.

## Artefatos temporários ainda presentes

Devem ser removidos somente no G-130, após G-110 PASS:

- `class-review-http-diagnostics.php`;
- `class-review-http-cache-coherence.php`;
- flag `BDC_KB_REVIEW_HTTP_DIAGNOSTICS_BUILD`.

## UX / patrimônio preservado

A tela histórica de artigo com **Resumo Executivo lateral** permanece registrada como patrimônio de produto. No futuro Resolvedor, esse painel será projection read-only composta pelos owners canônicos, nunca um novo writer.

## Proibições mantidas

- não alterar `post_status` por decisão de governança;
- não escrever `post_content` ou `_elementor_data` de posts reais;
- não criar score;
- não criar `AI Ready`;
- não duplicar estado em meta + histórico;
- não criar tabela própria sem necessidade comprovada;
- não recuperar stores KB2Ops vazios por nostalgia arquitetural;
- não adicionar Review como terceiro bloco vertical;
- não alterar writers de Summary/Classificação durante a refatoração visual sem necessidade contratual;
- não criar writer próprio para Histórico.

## Gates

- R-001: **PASS**.
- R-010: **PASS**.
- G-001: **PASS**.
- G-030: **PASS**.
- DS-010: **PASS**.
- G-070: **PASS — `0.3.0-dev.5`, 22/22, cleanup zero resíduos**.
- G-110: **ACTIVE / IMPLEMENTAÇÃO AUTORIZADA**.
- G-130: **BLOQUEADO até G-110 PASS**.
