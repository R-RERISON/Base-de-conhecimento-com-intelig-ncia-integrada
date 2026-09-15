# Continuidade — SPEC-003 Review & Governança

## Estado atual

- SPEC-001: concluída.
- SPEC-002: concluída, baseline `0.2.0-rc.1`.
- UX-001: baseline v1 congelada; UI as Code v0.2 é a referência executável.
- SPEC-003: **R-001 PASS / R-010 PASS / G-001 PASS / G-030 PASS / DS-010 PASS / G-070 PASS**.
- etapa ativa: **G-110 — Knowledge Workspace / Browser Acceptance**.
- build ativo: **`0.3.0-dev.7` — Histórico read-only + teclado implementados, aguardando homologação ambiental final**.

## G-070 fechado

Evidência final real:

`evidencias/bdc-kb-review-http-security-20260915-165537.json`

Resultado:

- **22 PASS / 0 FAIL**;
- `overall=PASS`;
- `residual_posts=0`;
- `residual_terms=0`;
- `residual_review_events=0`.

**G-070: PASS determinístico + ambiental.**

## Contrato de Review preservado

- owner: Review & Governança;
- estado inicial implícito: `unreviewed`;
- estados: `unreviewed`, `in_review`, `needs_changes`, `approved`, `excluded`;
- fonte canônica: eventos append-only via Comments API;
- `comment_type=bdc_kb_review_event`;
- estado atual = último evento válido;
- actor = `user_id`;
- data = `comment_date_gmt`;
- sem meta paralela de current state;
- sem tabela customizada;
- sem writer próprio para Histórico;
- `AI Ready` fora do domínio.

## G-110 — evidência ambiental do `0.3.0-dev.6`

Documento: `evidencia-g110-dev6-smoke.md`.

O operador instalou o build e confirmou funcionamento conforme orientado. A captura real comprova:

- Context Header do Knowledge Workspace;
- navegação horizontal por tabs;
- `Visão geral`, `Summary`, `Classificação` e `Review & Governança`;
- overview como superfície própria, sem empilhamento vertical dos domínios;
- card de Review exibindo estado canônico.

Decisão:

- **W-001 Workspace shell: PASS ambiental inicial**;
- **W-002 Review no Workspace: PASS ambiental inicial**.

Esse smoke não fecha G-110.

## Build ativo — `0.3.0-dev.7`

Documento: `package-dev7-history-keyboard.md`.

SHA-256 do ZIP instalável:

`1a8e85acdc63af7c5bc568bb9bf518019cf2644c403c9252d8d1761b76958dc9`

Validação local:

- PHP lint: **PASS 13/13**;
- JavaScript syntax check: **PASS**;
- estrutura instalável WordPress: PASS.

### W-003 — Histórico

A tab `Histórico` foi adicionada ao Workspace.

A implementação:

- usa somente `Review_Store::history()`;
- é read-only;
- não cria metadata, tabela ou writer;
- exibe transição `from -> to`, ator, data, estado final e nota;
- possui empty state quando não há eventos;
- limita a projeção aos 50 eventos mais recentes nesta primeira slice.

`Review_Store`, `Review_Contract`, `Summary_Store` e `Classification_Store` permanecem inalterados.

### Teclado

Novo asset `assets/js/workspace.js`:

- `ArrowRight`: próximo tab link;
- `ArrowLeft`: tab link anterior;
- `Home`: primeiro tab link;
- `End`: último tab link.

A ativação continua nativa por link/Enter. O servidor permanece a autoridade da tab ativa.

### Visual

Novo asset `assets/css/history.css` estende a foundation existente sem substituir `admin.css`/`workspace.css`.

A overview passa a comportar quatro domínios em desktop, dois em largura intermediária e uma coluna em viewport estreito.

## Próximo passo exato

1. instalar/substituir pelo `0.3.0-dev.7`;
2. abrir o mesmo artigo de homologação no Workspace;
3. confirmar a nova tab **Histórico**;
4. antes de qualquer evento, confirmar empty state quando aplicável;
5. executar uma transição real em Review e confirmar que o Histórico reflete exatamente o evento;
6. testar foco nas tabs com setas, Home e End;
7. validar desktop e reduzir largura até aproximadamente 782px e 492px, observando overflow horizontal;
8. confirmar Summary e Classificação sem regressão;
9. retornar capturas/resultado.

Após essa evidência entra o Browser Acceptance final do G-110; só depois abre G-130.

## Artefatos temporários ainda presentes

Remover apenas no G-130, após G-110 PASS:

- `class-review-http-diagnostics.php`;
- `class-review-http-cache-coherence.php`;
- flag `BDC_KB_REVIEW_HTTP_DIAGNOSTICS_BUILD`.

## Proibições mantidas

- não alterar `post_status` por governança;
- não escrever `post_content` ou `_elementor_data` de posts reais;
- não criar score;
- não criar `AI Ready`;
- não duplicar estado em meta + histórico;
- não criar tabela própria sem necessidade comprovada;
- não criar writer próprio para Histórico;
- não recuperar stores KB2Ops vazios.

## Gates

- R-001: **PASS**.
- R-010: **PASS**.
- G-001: **PASS**.
- G-030: **PASS**.
- DS-010: **PASS**.
- G-070: **PASS — 22/22, cleanup zero resíduos**.
- G-110: **ACTIVE — W-001/W-002 PASS ambiental inicial; dev.7 aguardando homologação W-003/teclado/responsividade**.
- G-130: **BLOQUEADO até G-110 PASS**.
