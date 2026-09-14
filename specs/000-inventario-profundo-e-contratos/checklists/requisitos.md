# Checklist de Requisitos — SPEC-000

## Preparação e inventários

- [x] Repositórios/SHAs baseline registrados.
- [x] Constituição, Manifesto, agentes e skills preparados.
- [x] KB2Ops completo.
- [x] ASI completo.
- [x] Resumo Executivo completo.
- [x] Nenhum runtime novo criado durante T000–T058.

## Cruzamento

- [x] T050 — persistência.
- [x] T051 — integrações.
- [x] T052 — ownership.
- [x] T053 — sobreposição.
- [x] T054 — drifts/blockers/compatibilidade.
- [x] T055 — regressão e Golden.
- [x] T056 — WordPress-first.
- [x] T057 — infraestrutura própria mínima.
- [x] T058 — IA/vetor. _(`matriz-ia-vetor.md`)_
- [ ] T059 — paridade futura final.

## Evidência T056/T057/T055 preservada

- [x] WP/Elementor continuam fonte editorial.
- [x] Summary/Review/Classificação/Settings/Security/Health usam primitives WP quando suficientes.
- [x] Search Retrieval Projection é a única família própria aprovada no baseline.
- [x] Analytics detalhado e durable queue continuam postergados.
- [x] Golden é QA governada; vazia/not-run/stale não é PASS.
- [x] benchmark real futuro é obrigatório; p95/QPS não foram inventados.
- [x] Search lexical funciona sem IA/vetor.

## Evidência T058 — princípio de negação

- [x] regra determinística/metadado/Search são avaliados antes de LLM.
- [x] pré-análise local determinística foi mantida como P0.
- [x] LLM em toda query foi rejeitado no baseline.
- [x] embeddings/vector não ganharam direito de existir apenas por modernidade.
- [x] agentes não ganharam direito de existir sem jornada multi-step comprovada.

## Evidência T058 — IA assistiva P1

- [x] Assistente de Classificação aprovado como opcional/sob demanda.
- [x] Assistente de Summary aprovado como opcional/sob demanda.
- [x] primeiro slice futuro deve escolher uma jornada, não ambas automaticamente.
- [x] sugestão e Apply são ações distintas.
- [x] IA não chama diretamente writers canônicos.
- [x] Apply humano exige handler/capability/nonce/read-after-write aplicáveis.
- [x] saída deve ser estruturada/validada e mostrar evidência/incerteza.
- [x] modelo pode sugerir novo termo apenas como candidato, sem persistir.
- [x] `include_ai`/AI READY não foi redefinido como permissão de autoria assistida.
- [x] primeiro runtime pode ter zero IA externa.

## Evidência T058 — RAG P2

- [x] retrieval precede síntese.
- [x] RAG pode nascer sobre retrieval lexical; vetor não é pré-requisito.
- [x] fontes/evidências são obrigatórias.
- [x] abstenção por evidência insuficiente é comportamento válido.
- [x] falha/timeout/quota pode degradar para retrieval sem síntese.
- [x] corpus produtivo respeita AI READY quando o gate estiver ativo.
- [x] conteúdo recuperado é dado, não instrução confiável.

## Evidência T058 — embeddings/semantic/rerank P3

- [x] embeddings postergados até lacuna lexical mensurável + Golden.
- [x] raw Elementor/JSON não pode ser embedding source.
- [x] chunking adicional não cria parser paralelo.
- [x] fingerprint inclui source/extractor/chunk/model/config suficientes para detectar incompatibilidade.
- [x] mudança de modelo/dimensão não reutiliza embedding incompatível.
- [x] re-embed total sem diff/budget é NO-GO.
- [x] semantic/hybrid precisa comparar contra baseline lexical no mesmo corpus/Golden.
- [x] critério de ganho deve ser fixado antes do experimento.
- [x] lexical permanece fallback.
- [x] rerank futuro é top-K bounded/fail-open, nunca LLM sobre corpus completo.

## Evidência T058 — provider/Foundry

- [x] Microsoft Foundry foi classificado como provider preferencial candidato, não domínio.
- [x] provider seam mínimo só nasce junto com caso real.
- [x] WordPress HTTP API é primeira opção quando adequada.
- [x] provider/model/deployment/prompt/config precisam ser versionados.
- [x] timeout/quota/auth/provider errors precisam ser explícitos.
- [x] secrets não podem aparecer em logs/exports.
- [x] failover não pode trocar silenciosamente provider/região/custo.
- [x] Foundry Agent File Search foi rejeitado como Search/RAG canônico.
- [x] store gerenciado futuro só pode ser projection de caso específico com lineage/hash/freshness/custo.
- [x] nenhum preço/default atual do provider virou contrato arquitetural.

## Evidência T058 — custo/NO_CHANGE

- [x] AI Operation Receipt conceitual definido sem aprovar storage próprio.
- [x] operação/provider/model/contexto/volume/tokens/custo/duração/status/timestamp são rastreáveis quando aplicáveis.
- [x] batch exige preview, estimativa, budget, limite e stop condition.
- [x] activation/publicação/page load não dispara batch de IA por default.
- [x] batch que exigir worker durável reabre F-057-03/B-007.
- [x] `NO_CHANGE` usa fingerprints de source/prompt/model/config relevantes.

## Evidência T058 — segurança/privacidade

- [x] data egress precisa ser explícito/minimizado.
- [x] query enviada a provider não autoriza query logging local; B-004 permanece independente.
- [x] secrets não entram em prompt/log/export.
- [x] prompt injection do conteúdo não concede tool/capability.
- [x] agentes futuros são read-only por default.
- [x] mutação de agente futura exige confirmação humana e handler canônico.

## G-140 após T058

Canônico em `matriz-ia-vetor.md`:

- [x] G-140A — independência/degradação.
- [x] G-140B — provider/rastreabilidade/data egress.
- [x] G-140C — human-in-the-loop.
- [x] G-140D — custo/budget/NO_CHANGE.
- [x] G-140E — embedding/semantic/hybrid.
- [x] G-140F — RAG/síntese.
- [x] G-140G — agentes/tools.
- [x] G-140H — provider-managed knowledge/File Search.

## Gate final SPEC-000

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

T050–T058 concluídas documentalmente. Próximo passo autorizado: **T059**. Runtime/Foundry/vector continuam inexistentes.
