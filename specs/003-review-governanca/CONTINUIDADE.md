# Continuidade — SPEC-003 Review & Governança

## Estado atual

- SPEC-001: concluída.
- SPEC-002: concluída, baseline `0.2.0-rc.1`.
- UX-001: baseline v1 congelada; UI as Code v0.2 é a referência executável.
- SPEC-003: **R-001 PASS / R-010 PASS / G-001 PASS / G-030 PASS / DS-010 PASS / G-070 PASS**.
- etapa ativa: **G-110 — Knowledge Workspace / Browser Acceptance**.
- build ativo: **`0.3.0-dev.10` — rerun do Browser Acceptance com submit nativo explícito no harness**.

## Estado funcional já comprovado

- W-001 Workspace shell: PASS ambiental inicial (`dev.6`);
- W-002 Review & Governança no Workspace: PASS ambiental inicial (`dev.6`);
- W-003 Histórico read-only: PASS ambiental inicial (`dev.7`);
- teclado/foco/links: PASS parcial real nos `dev.8` e `dev.9` antes da exceção do harness;
- G-070: **PASS 22/22 com cleanup zero**.

## Browser Acceptance `0.3.0-dev.9` — FAIL preservado

Evidência:

`evidencias/bdc-kb-g110-browser-acceptance-20260915-193517.json`

Resultado:

- browser: **7 PASS / 1 FAIL**;
- server: **3 PASS / 4 FAIL**;
- `overall=FAIL`;
- cleanup: `residual_posts=0`, `residual_terms=0`, `residual_review_events=0`.

A exceção foi novamente:

`form.submit is not a function`

O shim do `dev.9` tentou renomear controles `name="submit"` após load dos iframes. No browser real isso não restaurou de forma confiável o método `submit` da instância `HTMLFormElement` utilizada pelo runner.

A falha ocorreu antes do primeiro POST real; por isso Summary/Classificação/Review permaneceram sem mutação e S04-S07 falharam em cascata. S01-S03 passaram e o cleanup foi integral.

Documento: `evidencia-g110-dev9-submit-collision-reproduzida.md`.

## Build ativo — `0.3.0-dev.10`

Correção somente no harness:

- `assets/js/browser-acceptance.js` usa `loaded.win.HTMLFormElement.prototype.submit.call(form)`;
- isso ignora named properties do formulário chamadas `submit`;
- `class-workspace-browser-submit-shim.php` foi removido;
- `Review_Store`, `Review_Contract`, `Review_Admin`, `Summary_Store`, `Classification_Store`, `Classification_Admin` e writers permanentes não foram alterados.

Package: `package-dev10-browser-rerun.md`.

ZIP SHA-256:

`eee8dbed4e4cc1c8baaa5b07bd9522f063612ff8586bc93ba18352f35c41b8eb`

Validação local:

- PHP lint: **PASS 14/14**;
- `workspace.js`: syntax PASS;
- `browser-acceptance.js`: syntax PASS;
- ZIP WordPress: PASS.

## Próximo passo exato

1. substituir `0.3.0-dev.9` por `0.3.0-dev.10`;
2. abrir **Base de Conhecimento** como administrador;
3. clicar **Executar Browser Acceptance G-110 e gerar JSON**;
4. aguardar sem fechar a aba;
5. retornar o novo `bdc-kb-g110-browser-acceptance-*.json`;
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
- G-110: **ACTIVE / NÃO APROVADO — dev.8 e dev.9 FAIL preservados; dev.10 aguardando rerun real**.
- G-130: **BLOQUEADO até G-110 PASS**.
