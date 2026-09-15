# Continuidade — SPEC-003 Review & Governança

## Estado atual

- SPEC-001: concluída.
- SPEC-002: concluída, baseline `0.2.0-rc.1`.
- UX-001: baseline v1 congelada; UI as Code v0.2 é a referência executável.
- SPEC-003: **R-001 PASS / R-010 PASS / G-001 PASS / G-030 PASS / DS-010 PASS / G-070 PASS**.
- etapa ativa: **G-110 — Knowledge Workspace / Browser Acceptance**.
- build ativo: **`0.3.0-dev.9` — rerun do Browser Acceptance com correção test-only do harness**.

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

## G-110 — evidências ambientais já aprovadas

### `0.3.0-dev.6`

- **W-001 — Knowledge Workspace shell: PASS ambiental inicial**;
- **W-002 — Review & Governança no Workspace: PASS ambiental inicial**.

### `0.3.0-dev.7`

- **W-003 — Histórico read-only: PASS ambiental inicial**;
- desktop amplo: PASS visual inicial;
- Histórico comprovou eventos, ator, timestamp, estado final e nota em ambiente real.

## Browser Acceptance `0.3.0-dev.8` — FAIL preservado

Evidência bruta:

`evidencias/bdc-kb-g110-browser-acceptance-20260915-192838.json`

Documento de diagnóstico:

`evidencia-g110-dev8-submit-collision.md`

Ambiente:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- plugin `0.3.0-dev.8`;
- multisite: não.

Resultado:

- browser: **7 PASS / 1 FAIL**;
- server: **3 PASS / 4 FAIL**;
- `overall=FAIL`;
- cleanup: `residual_posts=0`, `residual_terms=0`, `residual_review_events=0`.

Antes da interrupção, passaram no browser real:

- cinco tabs autorizadas;
- Context Header + Main Work Area;
- ausência de `AI Ready`/health score;
- ArrowLeft/ArrowRight/Home/End;
- foco visível;
- links/Enter;
- Summary renderizado com labels associados.

Também passaram as preservações server-side de `post_status`, `post_content` e `_elementor_data`.

### Diagnóstico

A exceção foi:

`form.submit is not a function`

O runner chamou `form.submit()` em um formulário que contém controle nomeado `submit`. No DOM esse controle sombreou o método nativo `HTMLFormElement::submit`, então o Browser Acceptance abortou antes do primeiro POST real.

Por isso S04-S07 falharam em cascata: Summary, Classificação e Review não haviam sido mutados ainda. O relatório não contém evidência de regressão dos Stores/Contracts/Writers permanentes.

O FAIL do dev.8 permanece como evidência real e não é reinterpretado como PASS.

## Build ativo — `0.3.0-dev.9`

Correção deliberadamente restrita ao harness:

- novo `class-workspace-browser-submit-shim.php`;
- executa somente quando o runner G-110 está enfileirado;
- intercepta apenas os iframes do Browser Acceptance;
- renomeia controles `name="submit"` para `name="bdc_g110_submit"` antes das submissões;
- nenhum handler depende desse nome;
- `browser-acceptance.js` permanece funcionalmente igual;
- `Review_Store`, `Review_Contract`, `Review_Admin`, `Summary_Store`, `Classification_Store` e writers permanentes permanecem inalterados.

O shim é temporário e deve ser removido no G-130.

ZIP instalável `0.3.0-dev.9`:

SHA-256: `2d0c306a03c8a76ec1816ea984d9cd9242823278515778ca7123c9e31bef9e73`.

Validação local:

- PHP lint: **PASS 15/15**;
- `workspace.js`: syntax PASS;
- `browser-acceptance.js`: syntax PASS;
- estrutura instalável WordPress: PASS.

## Próximo passo exato

1. substituir `0.3.0-dev.8` por `0.3.0-dev.9`;
2. abrir **Base de Conhecimento** como administrador;
3. clicar **Executar Browser Acceptance G-110 e gerar JSON**;
4. aguardar o download sem fechar a aba;
5. retornar `bdc-kb-g110-browser-acceptance-*.json`;
6. exigir `browser_fail=0`;
7. exigir `server_fail=0`;
8. exigir `overall=PASS`;
9. exigir `residual_posts=0`, `residual_terms=0`, `residual_review_events=0`;
10. somente então promover G-110 para PASS e abrir G-130.

## Artefatos temporários ainda presentes

Remover no G-130 somente após G-110 PASS:

- `class-review-http-diagnostics.php`;
- `class-review-http-cache-coherence.php`;
- `class-workspace-browser-diagnostics.php`;
- `class-workspace-browser-submit-shim.php`;
- `assets/js/browser-acceptance.js`;
- flags `BDC_KB_REVIEW_HTTP_DIAGNOSTICS_BUILD` e `BDC_KB_WORKSPACE_BROWSER_DIAGNOSTICS_BUILD`.

## Proibições mantidas

- não alterar `post_status` por governança;
- não escrever `post_content` ou `_elementor_data` de posts reais;
- não criar score;
- não criar `AI Ready`;
- não duplicar estado em meta + histórico;
- não criar tabela própria sem necessidade comprovada;
- não criar writer próprio para Histórico;
- não recuperar stores KB2Ops vazios;
- não alterar runtime permanente para mascarar falha do harness;
- não ampliar o Workspace antes de G-110 PASS.

## Gates

- R-001: **PASS**.
- R-010: **PASS**.
- G-001: **PASS**.
- G-030: **PASS**.
- DS-010: **PASS**.
- G-070: **PASS — 22/22, cleanup zero resíduos**.
- G-110: **ACTIVE / NÃO APROVADO — dev.8 FAIL preservado; dev.9 aguardando rerun real**.
- G-130: **BLOQUEADO até G-110 PASS**.
