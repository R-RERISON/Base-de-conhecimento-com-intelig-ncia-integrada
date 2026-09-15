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

Evidência: `evidencia-profiling-s001.md`. O ambiente possui 622 posts e zero rows nos seis stores históricos de Review/Governança; não há passivo de migração.

## S002 — Domain Contract

- [x] T020 Definir owner canônico do estado de governança.
- [x] T021 Definir conjunto mínimo de estados.
- [x] T022 Definir estado inicial/ausência de decisão.
- [x] T023 Definir transições válidas.
- [x] T024 Definir capabilities/atores por transição.
- [x] T025 Decidir se reviewer/responsável é necessário na primeira slice.
- [x] T026 Decidir primitiva do estado atual.
- [x] T027 Decidir primitiva de histórico/auditoria.
- [x] T028 Definir contrato de atomicidade/compensação.
- [x] T029 Fechar política de migração/coexistência legada.

**Gate R-010: PASS.**

Contrato: `domain-contract.md`. Estado atual é derivado do último evento append-only `bdc_kb_review_event`; não existe meta paralela de current state.

## S003 — Runtime mínimo

- [x] T030 Registrar contrato/owner sem side effects.
- [x] T031 Implementar leitura do estado.
- [x] T032 Implementar transição determinística.
- [x] T033 Implementar auditoria mínima aprovada.
- [x] T034 Implementar read-after-write e consistência do evento canônico.
- [x] T035 Implementar compensation/fail-safe ou estado crítico conforme contrato.
- [x] T036 Unitários determinísticos — PASS 19/19.
- [x] T037 PHP lint PASS 10/10 e package `0.3.0-dev.1` gerado.
- [x] T038 Smoke ambiental `0.3.0-dev.1` — PASS.
- [x] T039 Integração real do `Review_Store` na WordPress Comments API via `0.3.0-dev.2` — PASS 17/17, cleanup zero resíduos.

**Gate G-001: PASS.**  
**Gate G-030: PASS determinístico + ambiental.**

## S003.5 — Design System Runtime Foundation

- [x] T039A Formalizar `design-system-runtime-plan.md`.
- [x] T039B Traduzir tokens UX-001 para CSS Custom Properties do plugin.
- [x] T039C Aplicar surfaces/hierarquia à Knowledge List e contexto do artigo sem mudar navegação.
- [x] T039D Refinar visual de Summary/Classificação sem alterar writers.
- [x] T039E Smoke visual desktop real do `0.3.0-dev.3` + regressão funcional — PASS.
- [ ] T039F Reflow/foco <=782px e ~492px — transferido para G-110 Browser Acceptance após entrada do Workspace final.

Evidência: `evidencia-design-system-runtime-dev3.md`.

**Gate DS-010: PASS — Runtime Foundation.**

## S004 — HTTP e segurança

- [x] T040 Handler POST + nonce vinculado implementado em `class-review-admin.php`.
- [x] T041 Capability por objeto `edit_post(post_id)` preservada no handler/store.
- [x] T042 Allowlist exata de payload `target_state`/`note` implementada.
- [x] T043 PRG implementado com statuses explícitos.
- [x] T044 Runner HTTP temporário criado em `class-review-http-diagnostics.php`.
- [x] T045 Executar GET/nonce/mass-assignment/IDOR/payload inválido no ambiente real.
- [x] T045A Registrar evidência bruta do `0.3.0-dev.4` — `15 PASS / 7 FAIL`, cleanup zero resíduos.
- [x] T045B Diagnosticar falha H14-H20 como incoerência de cache de Comments API entre loopback filho e processo pai do harness.
- [x] T045C Implementar hardening test-only em `class-review-http-cache-coherence.php`, sem alterar writer/store permanentes.
- [x] T046 Reexecutar POST válido + reread + regressão SPEC-001/002 no `0.3.0-dev.5` — H14-H21 PASS.
- [x] T047 Confirmar cleanup zero resíduos no `0.3.0-dev.4`.
- [x] T048 Confirmar cleanup zero resíduos novamente no `0.3.0-dev.5`.
- [x] T049 Registrar evidência final G-070 — `22 PASS / 0 FAIL / overall=PASS`.

Evidências:

- `evidencias/bdc-kb-review-http-security-20260915-155801.json` — FAIL histórico preservado;
- `evidencia-g070-dev4-cache-coherence.md`;
- `evidencias/bdc-kb-review-http-security-20260915-165537.json` — PASS final;
- `evidencia-g070-dev5-pass.md`.

**Gate G-070: PASS determinístico + ambiental.**

## S005 — UX / Browser Acceptance

Plano ativo: `g110-workspace-browser-acceptance-plan.md`.

- [ ] T050 Refatorar tela do artigo para Knowledge Workspace: Context Header + tabs + Main Work Area.
- [ ] T050A Preservar writers atuais de Summary e Classificação sem mudança de persistência.
- [ ] T050B Integrar Review como tab própria usando `Review_Admin`/`Review_Store`/`Review_Contract`.
- [ ] T050C Integrar Histórico como projection read-only dos eventos `bdc_kb_review_event`, sem novo writer/store.
- [ ] T051 Não exibir score/AI Ready/progresso ou outras métricas não contratadas.
- [ ] T052 Preservar Summary/Classificação, vocabulários e permanência no contexto do artigo.
- [ ] T053 Implementar/validar teclado, foco, labels e feedback sem dependência exclusiva de cor.
- [ ] T054 Testar 1440px/1024px/782px/~492px e zero overflow horizontal.
- [ ] T055 Criar Browser Acceptance com fixtures controladas e cleanup obrigatório.
- [ ] T056 Validar `unreviewed -> in_review -> needs_changes -> approved` pela UI real.
- [ ] T057 Validar `NO_CHANGE`, permission denied, note required e histórico consistente.
- [ ] T058 Confirmar cleanup zero resíduos do runner/browser acceptance.

**Gate G-110: ACTIVE / IMPLEMENTAÇÃO AUTORIZADA.**

Build sugerido para W-001 + W-002: `0.3.0-dev.6`.

## S006 — Lifecycle / fechamento

- [ ] T060 Remover profiler/runners/hooks temporários, incluindo `class-review-http-diagnostics.php`, `class-review-http-cache-coherence.php` e flag de diagnostics.
- [ ] T061 Gerar package clean RC.
- [ ] T062 deactivate/activate sem regressão.
- [ ] T063 Confirmar zero resíduos de teste.
- [ ] T064 Atualizar matriz de evidência e continuidade.
- [ ] T065 Congelar baseline da SPEC-003.

**Gate G-130: NOT_RUN.**

## Regra

R-001, R-010, G-001, G-030, DS-010 e G-070 estão PASS. G-110 está formalmente aberto. A próxima alteração de runtime deve convergir a tela atual para o Knowledge Workspace da UX-001; Review não pode ser anexado como terceiro bloco vertical. G-130 somente inicia após Browser Acceptance real do G-110.