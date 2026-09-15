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
- [ ] T039F Reflow/foco <=782px e ~492px — transferido para G-110 final.

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
- [x] T050B1 Smoke ambiental inicial do `0.3.0-dev.6` — PASS por captura + confirmação do operador; evidência `evidencia-g110-dev6-smoke.md`.
- [x] T050C Implementar Histórico como projection read-only de `Review_Store::history()` — `0.3.0-dev.7`.
- [x] T050C1 Smoke ambiental do Histórico no `0.3.0-dev.7` — PASS; eventos, ator, timestamp, estado final e nota confirmados em captura real. Evidência `evidencia-g110-dev7-history-smoke.md`.
- [x] T051 Não exibir score/AI Ready/progresso ou métricas não contratadas.
- [ ] T052 Confirmar ambientalmente regressão Summary/Classificação, vocabulários e permanência no contexto no build final do G-110.
- [x] T053A Implementar navegação de foco `ArrowLeft`/`ArrowRight`/`Home`/`End` entre tabs — `0.3.0-dev.7`.
- [ ] T053B Validar teclado, foco, labels e feedback no browser real.
- [ ] T054 Testar 1440px/1024px/782px/~492px e zero overflow horizontal. Desktop amplo possui PASS visual inicial por captura do dev.7; viewports restantes pendentes.
- [ ] T055 Executar Browser Acceptance final com fixtures controladas e cleanup obrigatório.
- [ ] T056 Validar `unreviewed -> in_review -> needs_changes -> approved` pela UI real.
- [ ] T057 Validar `NO_CHANGE`, permission denied, note required e histórico consistente.
- [ ] T058 Confirmar cleanup zero resíduos do Browser Acceptance.

Packages:

- `0.3.0-dev.6` — W-001/W-002, smoke ambiental inicial PASS;
- `0.3.0-dev.7` — W-003 Histórico + teclado; Histórico com PASS ambiental inicial.

**Gate G-110: ACTIVE — W-001/W-002/W-003 PASS ambiental inicial; teclado, responsividade estreita, regressão final e Browser Acceptance ainda pendentes.**

## S006 — Lifecycle / fechamento

- [ ] T060 Remover profiler/runners/hooks temporários, incluindo runners G-070.
- [ ] T061 Gerar package clean RC.
- [ ] T062 deactivate/activate sem regressão.
- [ ] T063 Confirmar zero resíduos de teste.
- [ ] T064 Atualizar matriz de evidência e continuidade.
- [ ] T065 Congelar baseline da SPEC-003.

**Gate G-130: NOT_RUN.**

## Regra

G-110 está aberto, mas não está aprovado. O `0.3.0-dev.7` já comprovou no ambiente real o shell, Review e Histórico read-only. O próximo passo é exclusivamente Browser Acceptance final: teclado/foco, viewports estreitos, regressão Summary/Classificação, cenários negativos e cleanup. Nenhum novo domínio deve ser adicionado antes do fechamento do gate.