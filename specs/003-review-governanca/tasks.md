# Tarefas — SPEC-003 Review & Governança

## S001 — Descoberta / Current State

- [x] T001 Congelar baseline `0.2.0-rc.1` + UX v1.
- [x] T002 Inventariar artefatos históricos de review/governança no código GRE/KB2Ops disponível.
- [x] T003 Mapear metas/taxonomias/tabelas candidatas.
- [x] T004 Mapear writers e consumers históricos.
- [x] T005 Mapear roles/capabilities e handlers relacionados.
- [x] T006 Construir profiler read-only temporário.
- [x] T007 Medir cobertura/distribuição de valores por store candidato.
- [x] T008 Medir presença de actor/timestamp auditável.
- [x] T009 Comparar com `post_status` e identificar sobreposição/conflito.
- [x] T010 Registrar política de legado: migrável / advisory / descartado.
- [x] T011 Gerar evidência JSON e registrar resultado do profiler.

**Gate R-001: PASS.**

## S002 — Domain Contract

- [x] T020 Definir owner canônico do estado de governança.
- [x] T021 Definir conjunto mínimo de estados.
- [x] T022 Definir estado inicial/ausência de decisão.
- [x] T023 Definir transições válidas.
- [x] T024 Definir capabilities/atores por transição.
- [x] T025 Decidir reviewer/responsável da primeira slice.
- [x] T026 Decidir primitiva do estado atual.
- [x] T027 Decidir primitiva de histórico/auditoria.
- [x] T028 Definir contrato de atomicidade/compensação.
- [x] T029 Fechar política de migração/coexistência legada.

**Gate R-010: PASS.**

## S003 — Runtime mínimo

- [x] T030 Registrar contrato/owner sem side effects.
- [x] T031 Implementar leitura do estado.
- [x] T032 Implementar transição determinística.
- [x] T033 Implementar auditoria mínima aprovada.
- [x] T034 Implementar read-after-write e consistência do evento canônico.
- [x] T035 Implementar compensation/fail-safe ou estado crítico conforme contrato.
- [x] T036 Unitários determinísticos — PASS 19/19.
- [x] T037 PHP lint PASS e package `0.3.0-dev.1`.
- [x] T038 Smoke ambiental `0.3.0-dev.1` — PASS.
- [x] T039 Integração Comments API `0.3.0-dev.2` — PASS 17/17, cleanup zero resíduos.

**Gate G-001: PASS.**  
**Gate G-030: PASS determinístico + ambiental.**

## S003.5 — Design System Runtime Foundation

- [x] T039A Formalizar `design-system-runtime-plan.md`.
- [x] T039B Traduzir tokens UX-001 para CSS Custom Properties.
- [x] T039C Aplicar surfaces/hierarquia à Knowledge List e contexto do artigo.
- [x] T039D Refinar visual de Summary/Classificação sem alterar writers.
- [x] T039E Smoke visual desktop real `0.3.0-dev.3` — PASS.
- [x] T039F Reflow/foco <=782px e ~492px — coberto pelo Browser Acceptance `0.3.0-dev.10`.

**Gate DS-010: PASS — Runtime Foundation.**

## S004 — HTTP e segurança

- [x] T040 Handler POST + nonce vinculado.
- [x] T041 Capability por objeto `edit_post(post_id)`.
- [x] T042 Allowlist `target_state`/`note`.
- [x] T043 PRG com statuses explícitos.
- [x] T044 Runner HTTP temporário.
- [x] T045 Camada negativa real GET/nonce/mass-assignment/IDOR/payload.
- [x] T045A Preservar evidência `0.3.0-dev.4` — 15 PASS / 7 FAIL.
- [x] T045B Diagnosticar incoerência de cache H14-H20.
- [x] T045C Hardening test-only de cache sem alterar writer/store permanentes.
- [x] T046 Rerun `0.3.0-dev.5` — H14-H21 PASS.
- [x] T047 Cleanup zero resíduos no dev.4.
- [x] T048 Cleanup zero resíduos no dev.5.
- [x] T049 Evidência final G-070 — **22 PASS / 0 FAIL / overall=PASS**.

Evidências finais:

- `evidencias/bdc-kb-review-http-security-20260915-165537.json`;
- `evidencia-g070-dev5-pass.md`.

**Gate G-070: PASS determinístico + ambiental.**

## S005 — UX / Browser Acceptance

Plano: `g110-workspace-browser-acceptance-plan.md`.

- [x] T050 Implementar shell Knowledge Workspace: Context Header + tabs + Main Work Area — `0.3.0-dev.6`.
- [x] T050A Preservar writers atuais de Summary e Classificação sem mudança de persistência.
- [x] T050B Integrar Review como tab própria usando `Review_Admin`/`Review_Store`/`Review_Contract`.
- [x] T050B1 Smoke ambiental inicial do `0.3.0-dev.6` — PASS por captura + confirmação do operador.
- [x] T050C Implementar Histórico como projection read-only de `Review_Store::history()` — `0.3.0-dev.7`.
- [x] T050C1 Smoke ambiental do Histórico no `0.3.0-dev.7` — PASS.
- [x] T051 Não exibir score/AI Ready/progresso ou métricas não contratadas.
- [ ] T052 Fechar regressão Summary/Classificação + permanência no contexto no build final do G-110. Summary PASS no dev.10; Classificação persiste no store, mas PRG perdeu `tab=classification`.
- [x] T053A Implementar navegação de foco `ArrowLeft`/`ArrowRight`/`Home`/`End` entre tabs.
- [x] T053B Browser real: teclado, foco visível e semântica de link/Enter — PASS.
- [x] T054 1440px/1024px/782px/~492px e zero overflow horizontal — PASS no `0.3.0-dev.10`.
- [x] T055A Executar Browser Acceptance `0.3.0-dev.10` com fixtures controladas e cleanup obrigatório — **21 browser PASS / 1 FAIL; 7/7 server PASS; cleanup zero**.
- [x] T056 Validar `unreviewed -> in_review -> needs_changes -> approved` pela UI real — PASS no dev.10.
- [x] T057 Validar `NO_CHANGE`, permission denied, note required e histórico consistente — PASS no dev.10.
- [x] T058A `0.3.0-dev.8`: cleanup zero resíduos após exceção do harness.
- [x] T058B Diagnosticar FAIL do dev.8: `form.submit is not a function`.
- [x] T058C Implementar shim test-only no `0.3.0-dev.9`.
- [x] T058D Rerun `0.3.0-dev.9`: FAIL preservado, mesma exceção; cleanup zero.
- [x] T058E Remover shim ineficaz e corrigir o chamador para `HTMLFormElement.prototype.submit.call(form)` no `0.3.0-dev.10`.
- [x] T058F Rerun `0.3.0-dev.10`: harness completo executou; único FAIL `G110-B10` por ausência de `tab=classification` no PRG.
- [x] T058G Diagnosticar bug permanente localizado em `Classification_Admin::redirect()`.
- [x] T058H Corrigir PRG de Classificação adicionando `tab=classification` — `0.3.0-dev.11`.
- [ ] T058I Rerun completo `0.3.0-dev.11`: exigir `browser_fail=0`, `server_fail=0`, `overall=PASS` e cleanup zero resíduos.

Evidências Browser Acceptance:

- `evidencias/bdc-kb-g110-browser-acceptance-20260915-192838.json` — dev.8 FAIL preservado;
- `evidencias/bdc-kb-g110-browser-acceptance-20260915-193517.json` — dev.9 FAIL preservado;
- `evidencias/bdc-kb-g110-browser-acceptance-20260915-194552.json` — dev.10 **21/1 browser, 7/7 server, cleanup zero**;
- `evidencia-g110-dev10-classification-prg.md`.

Packages:

- `0.3.0-dev.6` — W-001/W-002;
- `0.3.0-dev.7` — W-003 Histórico + teclado;
- `0.3.0-dev.8` — Browser Acceptance, FAIL do harness;
- `0.3.0-dev.9` — shim test-only, FAIL reproduzido;
- `0.3.0-dev.10` — Browser Acceptance completo, único FAIL de PRG da Classificação;
- `0.3.0-dev.11` — correção mínima de permanência na tab Classificação, rerun pendente.

**Gate G-110: ACTIVE — NÃO APROVADO; aguardando rerun real completo do `0.3.0-dev.11`.**

## S006 — Lifecycle / fechamento

- [ ] T060 Remover profiler/runners/hooks temporários G-070/G-110.
- [ ] T061 Gerar package clean RC.
- [ ] T062 deactivate/activate sem regressão.
- [ ] T063 Confirmar zero resíduos de teste.
- [ ] T064 Atualizar matriz de evidência e continuidade.
- [ ] T065 Congelar baseline da SPEC-003.

**Gate G-130: NOT_RUN.**

## Regra

G-110 continua aberto. O `dev.10` comprovou todo o fluxo funcional, segurança visual, responsividade e persistência canônica, mas revelou um único bug real de PRG: Classificação salvava e retornava sem `tab=classification`. O `dev.11` altera somente esse redirect e deve repetir o gate completo. Nenhum novo domínio é autorizado antes do PASS.
