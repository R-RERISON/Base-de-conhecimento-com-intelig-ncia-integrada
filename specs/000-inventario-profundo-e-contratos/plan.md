# Plano — SPEC-000 Inventário Profundo e Contratos

## Objetivo

Ler, decompor e cruzar os três projetos de referência em contratos verificáveis antes de qualquer runtime novo.

## Fases concluídas

- [x] inventário/classificação KB2Ops, ASI e GRE;
- [x] T050 persistência;
- [x] T051 integrações;
- [x] T052 ownership;
- [x] T053 sobreposição;
- [x] T054 drifts/compatibilidade/blockers;
- [x] T056 WordPress-first;
- [x] T057 infraestrutura própria mínima;
- [x] T055 regressão/Golden;
- [x] T058 IA/vetor;
- [ ] T059 paridade futura final.

## Arquitetura consolidada até T058

### WordPress-first

WordPress/Elementor continuam fonte editorial. Summary, Review, Classificação, Settings, segurança e health permanecem em primitives Core sempre que suficientes.

### Infra própria

A única família de persistência própria aprovada no baseline é a futura **Search Retrieval Projection** unificada para documentos `post|item`, reconstruível e não-canônica.

Analytics detalhado e durable queue continuam postergados.

### Regressão

T055 definiu gates MUST/CONDICIONAL/POSTERGADO/N/A, Golden como evidência de Search e benchmarks reais como gate de runtime.

### IA/vetor — T058

Artefato canônico: `matriz-ia-vetor.md`.

Prioridades:

- **P0:** determinístico/core, obrigatório antes de IA;
- **P1:** IA assistiva sob demanda — Classificação ou Summary, um slice por vez;
- **P2:** RAG/síntese opcional, retrieval-first e inicialmente lexical-capable;
- **P3:** embeddings/semantic/hybrid/rerank somente após lacuna mensurável + Golden/benchmark;
- **P4:** agentes/tools somente com jornada multi-etapa comprovada.

Decisões:

- pré-análise determinística: MANTER;
- assistente de Classificação: APROVADO OPCIONAL;
- assistente de Summary: APROVADO OPCIONAL;
- LLM em toda query: DESCARTAR baseline;
- RAG/síntese: APROVADO OPCIONAL POSTERIOR;
- chunking adicional: POSTERGADO/condicional;
- embeddings: POSTERGADO COM GATE;
- hybrid semantic retrieval: POSTERGADO COM GATE;
- model reranking: POSTERGADO COM GATE;
- Microsoft Foundry: provider preferencial candidato, desacoplado;
- Foundry File Search: não é core/search canônico; somente projection eventual de agente específico;
- agentes: POSTERGADOS/NEGADOS no baseline.

## G-140 após T058

`matriz-ia-vetor.md` detalha:

- G-140A independência/degradação;
- G-140B provider/rastreabilidade/data egress;
- G-140C human-in-the-loop;
- G-140D custo/budget/NO_CHANGE;
- G-140E embedding/semantic/hybrid;
- G-140F RAG/síntese;
- G-140G agentes/tools;
- G-140H provider-managed knowledge/File Search.

## Regra de custo

Toda futura chamada de IA deve ser atribuível a operação, provider/model/deployment, prompt/config version, objeto/contexto, volume, tokens/unidades quando disponíveis, custo estimado/real, duração, estado e timestamp.

Batch sem preview, estimativa, budget, limite e stop condition é NO-GO. Se exigir worker durável, F-057-03/B-007 deve ser reaberto formalmente.

## Primeiro runtime

T058 confirma que o primeiro runtime pode nascer com **zero IA externa**. Isso não é dívida.

Quando IA for autorizada, o primeiro slice deverá escolher uma jornada estreita — Classificação assistida **ou** Summary assistido — e preservar Apply humano separado.

## Próximo passo — T059

T059 deve consolidar a matriz de paridade final e responder, por capacidade:

1. owner lógico;
2. origem/baseline;
3. comportamento futuro;
4. primitive/storage;
5. `primeiro runtime | posterior | postergado`;
6. gates e blockers;
7. compatibilidade/cutover;
8. fallback/degradação;
9. dado que não pode ser perdido;
10. decisão final da SPEC-000.

T059 não resolve por código. Depois dela vêm T090–T097.

## Gate final

Nenhuma SPEC de runtime começa antes de T059 + revisões T090–T097. T097 é a única autorização formal para SPEC-001.
