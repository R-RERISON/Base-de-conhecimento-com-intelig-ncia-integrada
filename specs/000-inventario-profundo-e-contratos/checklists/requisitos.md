# Checklist de Requisitos — SPEC-000

## Preparação

- [x] Repositórios e SHAs baseline registrados.
- [x] Constituição, Manifesto, agentes e skills preparados.
- [x] Nenhum runtime novo criado durante a SPEC-000.

## Inventários individuais

- [x] KB2Ops completo.
- [x] ASI completo.
- [x] Resumo Executivo completo.

## Cruzamento

- [x] T050 — persistência consolidada por owner/conceito.
- [x] T051 — integrações consolidadas por contrato futuro.
- [x] T052 — mapa de ownership.
- [x] T053 — matriz de sobreposição.
- [x] T054 — drifts/contratos quebrados/blockers.
- [x] T055 — catálogo de regressão e Golden Queries. _(`catalogo-testes-regressao.md`)_
- [x] T056 — matriz WordPress-first. _(`matriz-wordpress-first.md`)_
- [x] T057 — infraestrutura própria mínima. _(`infraestrutura-propria-minima.md`)_
- [ ] T058 — candidatos IA/vetor.
- [ ] T059 — paridade futura final.

## Evidência T054

- [x] D-001–D-008 classificados.
- [x] Compatibilidade separada de arquitetura permanente.
- [x] B-001–B-007 explicitados e contextuais.
- [x] adapters/aliases temporários exigem consumidor, observabilidade, rollback e gate de remoção.
- [x] nenhum alias histórico foi aprovado por inércia.

## Evidência T056

- [x] WP/Elementor continuam fonte editorial.
- [x] Summary/Review/Classificação/Settings/Security/Health permanecem em primitives WP quando suficientes.
- [x] Taxonomy foi escolhida somente onde reutilização/filtro/faceta possuem evidência.
- [x] Search Knowledge/Golden permanecem em `WP_Post` interno + Metadata/Revisions inicialmente.
- [x] WP-Cron é trigger, não durable queue.
- [x] native search é fallback, não engine de paridade final.

## Evidência T057

- [x] 12 tabelas ASI não foram tratadas como checklist.
- [x] Search Retrieval Projection foi reduzida a um store lógico futuro de documentos `post|item`.
- [x] Analytics detalhado foi postergado por B-004.
- [x] durable queue foi postergada até benchmark/B-007.
- [x] nenhuma tabela/schema/migration/runtime foi criada.

## Evidência T055 — regressão e Golden

### Política de gates

- [x] contratos classificados como `MUST | CONDICIONAL | POSTERGADO | N/A`.
- [x] MUST sem evidência = `NOT_VERIFIED / NO-GO`.
- [x] CONDICIONAL ativado sem evidência = NO-GO.
- [x] POSTERGADO implementado silenciosamente = regressão arquitetural.
- [x] status `not_configured`, `not_run`, `degraded` ou warning não são colapsados em PASS.

### Editorial/Extractor

- [x] zero write em `_elementor_data`/`post_content` definido como gate MUST.
- [x] B-001 exige corpus Elementor representativo + custom widgets + detecção de omissão.
- [x] shortcode allowlist/fallback/error non-fatal incluídos.
- [x] determinismo e side-effect free incluídos.

### Summary/Classificação/Review

- [x] allowlist/sanitização/capability/nonce/read-after-write mapeados.
- [x] B-006 exige semântica explícita de falha tardia; sucesso falso é proibido.
- [x] B-002 possui gate de profiling + migração idempotente.
- [x] AI READY preservado como regra única derivada.
- [x] evento pós-persistência confirmada + consumer idempotente mapeados.

### Search Projection/Ranking

- [x] projection unificada `post|item` protegida por determinismo/identity/hash/rebuild/freshness.
- [x] despublicação/permissão precisa ser revalidada no WordPress.
- [x] FULLTEXT + fallback lexical bounded definidos como gates.
- [x] zero-result != erro/degraded.
- [x] Search lexical funciona sem IA/vetor.

### Golden Queries

- [x] storage baseline permanece WordPress-first; nenhuma Golden table foi aprovada.
- [x] Golden é configuração de QA, não telemetria de usuário.
- [x] suíte vazia = `NOT_CONFIGURED`, nunca PASS.
- [x] suíte não executada/stale = NO-GO quando Search é afetada.
- [x] failure `blocking` = NO-GO.
- [x] warning exige decisão explícita/waiver versionado ou correção.
- [x] evidência vinculada ao conjunto ativo, rankers, extractor/index e dataset/projection.
- [x] leitura de status não executa ranking implicitamente.
- [x] Golden não depende de identity/session/journey de Analytics.
- [x] famílias mínimas de cenário foram definidas por comportamento, sem meta numérica artificial.

### Analytics/Queue negativos

- [x] baseline sem Analytics deve provar ausência de query logging silencioso.
- [x] Search funciona sem Analytics.
- [x] telemetria histórica não migra automaticamente.
- [x] baseline não depende de durable queue.
- [x] WP-Cron não é durable store.
- [x] Options/Transients não podem virar fila improvisada.
- [x] activation não inicia rebuild massivo silencioso.

### Performance/Release

- [x] benchmark real futuro é obrigatório para caminhos críticos.
- [x] T055 não inventou p95/QPS inexistentes.
- [x] guardrail estrutural não substitui benchmark.
- [x] build/package/install/upgrade/rollback continuam gates.
- [x] compatibilidade/preflight possui regressão própria quando ativada.
- [x] IA/vetor ficaram para detalhamento em T058.

## Gate final SPEC-000

- [ ] T058 concluída.
- [ ] T059 concluída.
- [ ] T090 Revisão WordPress.
- [ ] T091 Revisão Simplicidade.
- [ ] T092 Revisão Segurança.
- [ ] T093 Revisão QA/Regressão.
- [ ] T094 Revisão Produto/Conhecimento.
- [ ] T095 unknowns/blockers críticos resolvidos ou formalmente postergados por slice.
- [ ] T096 relatório final.
- [ ] T097 autorização formal de SPEC-001.

## Estado

T050–T057, incluindo **T055**, concluídas documentalmente. Próximo passo autorizado: **T058 — IA/vetor**. Runtime novo continua inexistente.
