# Continuidade — SPEC-003 Review & Governança

## Estado atual

- SPEC-001: concluída.
- SPEC-002: concluída, baseline `0.2.0-rc.1`.
- UX-001: baseline v1 congelada; UI as Code v0.2 é a referência executável.
- SPEC-003: **R-001 PASS / R-010 PASS / G-001 PASS / G-030 PASS / DS-010 PASS / G-070 PASS**.
- etapa ativa: **G-110 — Knowledge Workspace / Browser Acceptance**.
- build ativo: **`0.3.0-dev.11` — rerun final após correção do PRG de Classificação**.

## Estado funcional comprovado

- W-001 Workspace shell: PASS ambiental inicial (`dev.6`);
- W-002 Review & Governança no Workspace: PASS ambiental inicial (`dev.6`);
- W-003 Histórico read-only: PASS ambiental inicial (`dev.7`);
- teclado/foco/links: PASS em browser real;
- reflow 1440/1024/782/492: PASS no `dev.10`;
- Summary: render/save/permanência na tab PASS no `dev.10`;
- Review: `unreviewed -> in_review -> needs_changes -> approved` PASS no `dev.10`;
- `NO_CHANGE`, note required, capability visual e Histórico consistente: PASS no `dev.10`;
- server assertions: 7/7 PASS no `dev.10`;
- cleanup: zero resíduos no `dev.10`;
- G-070: PASS 22/22 com cleanup zero.

## Browser Acceptance `0.3.0-dev.10` — FAIL localizado

Evidência bruta:

`evidencias/bdc-kb-g110-browser-acceptance-20260915-194552.json`

Diagnóstico:

`evidencia-g110-dev10-classification-prg.md`

Resultado:

- browser: **21 PASS / 1 FAIL**;
- server: **7 PASS / 0 FAIL**;
- `overall=FAIL`;
- cleanup: `residual_posts=0`, `residual_terms=0`, `residual_review_events=0`.

Único FAIL:

`G110-B10 — Classificação salva pelo formulário real e permanece no contexto/tab.`

A URL após o save continha:

- `bdc_classification_status=saved`;
- `post_id` correto;
- **não continha `tab=classification`**.

Ao mesmo tempo, `G110-S05` passou, portanto a Classificação foi persistida corretamente no store canônico.

Conclusão: não é falha de persistência nem de segurança. É um bug real e localizado de PRG/UX.

## Diagnóstico de código

`Classification_Admin::redirect()` montava:

- `page`;
- `bdc_classification_status`;
- `post_id`.

Faltava:

`tab=classification`

Isso quebrava o requisito de permanência no mesmo domínio após salvar.

## Build ativo — `0.3.0-dev.11`

Correção mínima e permanente:

- `Classification_Admin::redirect()` agora inclui `'tab' => 'classification'`;
- nenhuma alteração em `Classification_Store`;
- nenhuma alteração em `Classification_Contract`;
- nenhuma alteração em `Summary_Store`;
- nenhuma alteração em `Review_Store`/`Review_Contract`;
- nenhuma alteração de schema/persistência;
- Browser Acceptance continua habilitado para o rerun final.

O `dev.11` deve repetir o runner completo, não somente B10, para impedir falso positivo por correção localizada.

## Próximo passo exato

1. instalar/substituir pelo `0.3.0-dev.11`;
2. abrir **Base de Conhecimento** como administrador;
3. executar **Browser Acceptance G-110 e gerar JSON**;
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
- não ampliar o Workspace antes de G-110 PASS.

## Gates

- R-001: PASS.
- R-010: PASS.
- G-001: PASS.
- G-030: PASS.
- DS-010: PASS.
- G-070: PASS — 22/22, cleanup zero resíduos.
- G-110: **ACTIVE / NÃO APROVADO — dev.10 21/1 browser e 7/7 server; dev.11 aguardando rerun real**.
- G-130: BLOQUEADO até G-110 PASS.
