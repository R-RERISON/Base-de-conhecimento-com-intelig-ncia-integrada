# Checklist de Requisitos — SPEC-000

## Preparação e inventários

- [x] Repositórios/SHAs baseline registrados.
- [x] Constituição, Manifesto, agentes e skills preparados.
- [x] KB2Ops completo.
- [x] ASI completo.
- [x] Resumo Executivo completo.
- [x] Nenhum runtime novo criado durante T000–T091.

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

- [x] T090 — Arquiteto WordPress.
- [x] T091 — Crítico de Simplicidade. _(`revisao-simplicidade-t091.md`)_
- [ ] T092 — Segurança.
- [ ] T093 — QA/Regressão.
- [ ] T094 — Produto/Conhecimento.
- [ ] T095 — unknowns/blockers.
- [ ] T096 — relatório final.
- [ ] T097 — GO/NO-GO SPEC-001.

## Evidência T091 — princípio de negação

- [x] primeira SPEC futura não será plataforma/big-bang.
- [x] Core só nasce na medida consumida pelo slice.
- [x] Summary narrativo foi identificado como candidato mínimo para SPEC-001, sujeito a T094/T095/T097.
- [x] Content Extractor foi postergado até primeiro consumidor real.
- [x] DS será incremental por tela real.
- [x] Settings só serão criados quando consumidos.
- [x] Review foi separado do primeiro Summary slice.
- [x] Classificação será entregue por eixos/slices.
- [x] histórico não terá mecanismos duplicados sem requisito.
- [x] event bus próprio foi descartado no baseline.
- [x] repository layer genérico foi descartado no baseline.
- [x] DI/service container genérico foi descartado no baseline.
- [x] cache service genérico foi descartado no baseline.
- [x] Site Health só cobre capacidades existentes.
- [x] Search customizada permanece posterior.
- [x] post-level Search deve preceder item/deep-link quando suficiente.
- [x] Search Knowledge foi postergado até necessidade observada.
- [x] Golden continua gate de Search sem exigir UI CRUD completa inicialmente.
- [x] Operations Center/migration framework genérico foram descartados.
- [x] adapter framework de compatibilidade foi rejeitado; B-003 decide caso a caso.
- [x] IA P1 só nasce após owner estável.
- [x] provider factory/multi-provider abstraction não nasce antes de segundo caso real.
- [x] RAG/P3/P4 continuam postergados.
- [x] REST/SPA continuam fora sem consumidor.

## Garantias que simplicidade não removeu

- [x] capability + nonce + sanitização + escaping.
- [x] read-after-write.
- [x] B-006 para write composto.
- [x] rollback/coexistência quando aplicável.
- [x] B-001 quando extractor for crítico.
- [x] Golden/benchmark/security quando Search nascer.
- [x] DS/a11y para telas reais.
- [x] dados canônicos preservados.

## Resultado T091

- [x] findings bloqueantes = 0.
- [x] nenhuma nova infraestrutura autorizada.
- [x] nenhuma capacidade postergada foi reaberta.
- [x] nenhum runtime/schema/provider/vector criado.

## Gate final SPEC-000

- [x] T059 concluída.
- [x] T090 concluída.
- [x] T091 concluída.
- [ ] T092 Revisão Segurança.
- [ ] T093 Revisão QA/Regressão.
- [ ] T094 Revisão Produto/Conhecimento.
- [ ] T095 unknowns/blockers críticos fechados ou formalmente postergados por slice.
- [ ] T096 relatório final.
- [ ] T097 autorização formal de SPEC-001.

## Próximo passo

**T092 — Revisão de Segurança.**

Critério: threat model/finding objetivo por superfície, sem criar runtime, classificando `PASS | ENDURECER | POSTERGAR | BLOQUEAR`.

## Estado

T050–T059 + T090 + T091 concluídos documentalmente. Runtime/Foundry/vector continuam inexistentes. SPEC-001 permanece bloqueada até T097.