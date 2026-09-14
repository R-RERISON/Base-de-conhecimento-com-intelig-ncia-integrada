# Checklist de Requisitos — SPEC-000

## Preparação e inventários

- [x] Repositórios/SHAs baseline registrados.
- [x] Constituição, Manifesto, agentes e skills preparados.
- [x] KB2Ops completo.
- [x] ASI completo.
- [x] Resumo Executivo completo.
- [x] Nenhum runtime novo criado durante T000–T059.

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
- [x] T059 — paridade futura final. _(`matriz-paridade-futura.md`)_

## Evidência T059 — matriz final

- [x] cada capacidade possui owner lógico ou unknown restrito/documentado.
- [x] cada capacidade possui primitive/storage aprovado ou estado postergado.
- [x] cada capacidade foi classificada temporalmente: `PRIMEIRO_RUNTIME | POSTERIOR | POSTERGADO | COMPAT_CUTOVER | DESCARTADO`.
- [x] “PRIMEIRO_RUNTIME” não foi tratado como big-bang; vertical slices continuam obrigatórios.
- [x] gates G-001–G-140 e blockers B-001–B-007 foram associados às capacidades corretas.
- [x] blockers contextuais não foram convertidos em bloqueio global.
- [x] dados históricos que não podem ser perdidos foram explicitados.
- [x] projections reconstruíveis foram separadas de dados canônicos de cutover.
- [x] fallback/degradação foi definido para Search, IA, RAG, semantic futuro, queue inexistente e compatibilidade.
- [x] compatibilidade não escolhe arquitetura permanente.
- [x] nenhum alias/shortcode histórico foi aprovado automaticamente.
- [x] dual-write permanente continua proibido.

## Evidência T059 — WordPress-first

- [x] WP/Elementor continuam fonte editorial absoluta.
- [x] Summary permanece Metadata API.
- [x] Review/Governança permanece Metadata + Users + Revisions quando aplicável.
- [x] Settings/Security/Health usam Core.
- [x] Taxonomy não foi escolhida apenas por aparência classificatória.
- [x] quatro unknowns classificatórios permanecem limitados a Metadata vs Taxonomy; não justificam tabela própria.
- [x] Search Knowledge/Golden permanecem WordPress-first inicialmente.
- [x] Search Retrieval Projection continua a única família persistente própria aprovada.
- [x] WP-Cron continua trigger, não durable store.

## Evidência T059 — Search/Quality

- [x] Search lexical/projection é POSTERIOR a extractor representativo.
- [x] native WP search é fallback, não engine de paridade final.
- [x] Golden precisa existir/estar atual antes de release/mudança Search.
- [x] B-001 + Golden + benchmark + security continuam independentes.
- [x] deep-link público permanece separado sob B-005.
- [x] índice/item identity são derivados e reconstruíveis.

## Evidência T059 — IA/vetor

- [x] primeiro runtime pode ter zero IA externa.
- [x] P1 continua opcional/posterior e human-in-the-loop.
- [x] P2 continua retrieval-first e pode ser lexical-first.
- [x] P3/P4 continuam postergados.
- [x] Foundry continua provider candidato, não domínio.
- [x] File Search continua fora do Search/RAG core.
- [x] vector technology continua não escolhida.

## Evidência T059 — cutover

- [x] WP/Elementor/taxonomias editoriais marcados para preservação.
- [x] oito valores GRE marcados para preservação até migração comprovada.
- [x] review/include_ai/notas/revisor/histórico KB2Ops válidos preservados.
- [x] classificações KB2Ops realmente usadas preservadas.
- [x] Search Knowledge ASI manual real preservado quando existir.
- [x] Golden ASI útil preservado quando existir.
- [x] telemetria histórica não migra automaticamente.
- [x] índices/caches/queue/rollups/migrations registry/embeddings não foram promovidos a canônicos.

## Gate final SPEC-000

- [x] T059 concluída.
- [ ] T090 Revisão WordPress.
- [ ] T091 Revisão Simplicidade.
- [ ] T092 Revisão Segurança.
- [ ] T093 Revisão QA/Regressão.
- [ ] T094 Revisão Produto/Conhecimento.
- [ ] T095 unknowns/blockers críticos fechados ou formalmente postergados por slice.
- [ ] T096 relatório final.
- [ ] T097 autorização formal de SPEC-001.

## Próximo passo

**T090 — Revisão do Arquiteto WordPress.**

Critério: revisar toda a matriz final contra primitives do Core e retornar findings classificados em `APROVADO | SIMPLIFICAR | BLOQUEAR | INVESTIGAR`, sem criar runtime.

## Estado

T050–T059 concluídas documentalmente. Runtime/Foundry/vector continuam inexistentes. SPEC-001 permanece bloqueada até T097.
