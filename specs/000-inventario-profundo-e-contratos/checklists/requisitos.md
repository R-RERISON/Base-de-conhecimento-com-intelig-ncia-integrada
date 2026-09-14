# Checklist de Requisitos — SPEC-000

## Preparação e inventários

- [x] Repositórios/SHAs baseline registrados.
- [x] Constituição, Manifesto, agentes e skills preparados.
- [x] KB2Ops completo.
- [x] ASI completo.
- [x] Resumo Executivo completo.
- [x] Nenhum runtime novo criado durante T000–T090.

## Cruzamento

- [x] T050 — persistência.
- [x] T051 — integrações.
- [x] T052 — ownership.
- [x] T053 — sobreposição.
- [x] T054 — drifts/blockers/compatibilidade.
- [x] T055 — regressão e Golden.
- [x] T056 — WordPress-first.
- [x] T057 — infraestrutura própria mínima.
- [x] T058 — IA/vetor.
- [x] T059 — paridade futura final.

## Revisões finais

- [x] T090 — Arquiteto WordPress. _(`revisao-wordpress-t090.md`)_
- [ ] T091 — Crítico de Simplicidade.
- [ ] T092 — Segurança.
- [ ] T093 — QA/Regressão.
- [ ] T094 — Produto/Conhecimento.
- [ ] T095 — unknowns/blockers.
- [ ] T096 — relatório final.
- [ ] T097 — GO/NO-GO SPEC-001.

## Evidência T090 — WordPress-first

- [x] `WP_Post`/Elementor permanecem fonte editorial.
- [x] Metadata API continua suficiente para Summary/Review.
- [x] Taxonomy/Metadata continuam suficientes para Classificação.
- [x] quatro unknowns classificatórios não justificam tabela própria.
- [x] Search Knowledge/Golden continuam WordPress-first inicialmente.
- [x] Search Retrieval Projection própria foi reavaliada e permaneceu justificada.
- [x] Site Health continua primitive de diagnóstico.
- [x] WP-Cron continua scheduler/trigger, não durable queue.
- [x] admin-post continua baseline de mutação administrativa.
- [x] AJAX permanece condicional a live UX.
- [x] REST permanece negado sem consumidor formal.
- [x] WordPress HTTP API continua primeira opção para provider externo.
- [x] nenhum SDK/provider virou dependência obrigatória.

## Simplificações T090

- [x] bounded review history + meta revisions concorrentes foram proibidos sem requisito explícito.
- [x] Search Knowledge/Golden não ganham tabela/admin CRUD próprio antes de esgotar primitives WP.

## Investigações T090

- [x] versão mínima WordPress para `revisions_enabled` registrada como item T095/SPEC aplicável.
- [x] exposição pública/rewrite de taxonomias sistêmicas deve começar fail-closed.
- [x] provider endpoint configurável foi encaminhado para T092/SPEC de IA por risco SSRF/allowlist.

## Resultado T090

- [x] findings bloqueantes = 0.
- [x] nenhuma infraestrutura adicional autorizada.
- [x] nenhuma capacidade postergada reaberta.
- [x] nenhum runtime/schema/provider/vector criado.

## Gate final SPEC-000

- [x] T059 concluída.
- [x] T090 concluída.
- [ ] T091 Revisão Simplicidade.
- [ ] T092 Revisão Segurança.
- [ ] T093 Revisão QA/Regressão.
- [ ] T094 Revisão Produto/Conhecimento.
- [ ] T095 unknowns/blockers críticos fechados ou formalmente postergados por slice.
- [ ] T096 relatório final.
- [ ] T097 autorização formal de SPEC-001.

## Próximo passo

**T091 — Revisão do Crítico de Simplicidade.**

Critério: tentar remover cada camada/capacidade não essencial e registrar findings `MANTER | SIMPLIFICAR | POSTERGAR | DESCARTAR | BLOQUEAR`, sem criar runtime.

## Estado

T050–T059 + T090 concluídos documentalmente. Runtime/Foundry/vector continuam inexistentes. SPEC-001 permanece bloqueada até T097.
