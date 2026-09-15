# Continuidade — SPEC-003 Review & Governança

## Estado atual

- SPEC-001: concluída.
- SPEC-002: concluída, baseline `0.2.0-rc.1`.
- UX-001: baseline v1 congelada; UI as Code v0.2 é a referência executável.
- SPEC-003: **R-001 PASS / R-010 PASS / G-001 PASS / G-030 PASS / DS-010 PASS / G-070 PASS**.
- etapa ativa: **G-110 — Knowledge Workspace / Browser Acceptance**.
- build ativo: **`0.3.0-dev.7` — W-001/W-002/W-003 com PASS ambiental inicial; Browser Acceptance final pendente**.

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

## G-110 — evidências ambientais

### `0.3.0-dev.6`

Documento: `evidencia-g110-dev6-smoke.md`.

PASS ambiental inicial de:

- **W-001 — Knowledge Workspace shell**;
- **W-002 — Review & Governança no Workspace**.

A captura real comprovou Context Header, tabs horizontais, Visão geral e Review integrado ao domínio do artigo, sem terceiro bloco vertical.

### `0.3.0-dev.7`

Documento: `evidencia-g110-dev7-history-smoke.md`.

A captura real do Histórico comprova:

- tab `Histórico` presente no Workspace;
- projection read-only dos eventos canônicos;
- ordenação do evento mais recente para o mais antigo;
- transição `from -> to`;
- actor;
- timestamp;
- badge do estado final;
- nota quando presente;
- sequência coerente com a máquina de estados: `Não revisado -> Em revisão -> Aprovado -> Em revisão`.

Decisão:

- **W-003 — Histórico read-only: PASS ambiental inicial**;
- desktop amplo: **PASS visual inicial**, sem overflow horizontal visível na captura.

## Build ativo — `0.3.0-dev.7`

Documento: `package-dev7-history-keyboard.md`.

SHA-256 do ZIP instalável:

`1a8e85acdc63af7c5bc568bb9bf518019cf2644c403c9252d8d1761b76958dc9`

Validação local:

- PHP lint: **PASS 13/13**;
- JavaScript syntax check: **PASS**;
- estrutura instalável WordPress: PASS.

### Teclado implementado

`assets/js/workspace.js` implementa:

- `ArrowRight`: próximo tab link;
- `ArrowLeft`: tab link anterior;
- `Home`: primeiro tab link;
- `End`: último tab link.

A ativação continua nativa por link/Enter. O servidor permanece a autoridade da tab ativa.

## Falta para fechar G-110

O escopo funcional do Workspace está implementado e comprovado ambientalmente. Não adicionar novos domínios antes do gate.

Ainda é obrigatório validar no browser real:

1. foco nas tabs com `ArrowLeft` / `ArrowRight` / `Home` / `End`;
2. foco visível e ativação com Enter;
3. 1024px / 782px / ~492px;
4. zero overflow horizontal indevido;
5. Summary e Classificação sem regressão e permanecendo no contexto após save;
6. `NO_CHANGE`, note required e permission denied pela UI/feedback;
7. consistência entre Review e Histórico após transições;
8. cleanup zero resíduos do Browser Acceptance final.

Após essa evidência, G-110 pode ser promovido a PASS e G-130 é aberto.

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
- não recuperar stores KB2Ops vazios;
- não ampliar o Workspace antes de G-110 PASS.

## Gates

- R-001: **PASS**.
- R-010: **PASS**.
- G-001: **PASS**.
- G-030: **PASS**.
- DS-010: **PASS**.
- G-070: **PASS — 22/22, cleanup zero resíduos**.
- G-110: **ACTIVE — W-001/W-002/W-003 PASS ambiental inicial; Browser Acceptance final pendente**.
- G-130: **BLOQUEADO até G-110 PASS**.
