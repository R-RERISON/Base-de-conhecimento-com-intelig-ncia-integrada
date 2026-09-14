# Plano — SPEC-000 Inventário Profundo e Contratos

## Objetivo

Ler, decompor e cruzar os três projetos de referência em contratos verificáveis antes de qualquer runtime novo.

## Fase de inventário/cruzamento — concluída

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
- [x] T059 paridade futura final.

## Arquitetura consolidada após T059

### Fonte da verdade

- Editorial: WordPress/Elementor.
- Summary: Metadata API.
- Classificação: Metadata/Taxonomy WordPress conforme conceito/profiling.
- Review/Governança: Metadata + WP Users + Revisions quando aplicável.
- Search Knowledge/Quality: registros internos WordPress-first inicialmente.
- Search Indexing: única projection própria aprovada, reconstruível.
- IA: assistiva, opcional e sem ownership canônico.

### Fronteira temporal

**PRIMEIRO_RUNTIME** é uma onda de vertical slices, não um pacote monolítico.

Sequência de risco recomendada, se T097 autorizar:

1. Core/Settings/Security/Design System mínimo;
2. Summary e write path seguro;
3. Review/Governança;
4. Classificação por conceitos já resolvidos;
5. Content Extractor e B-001;
6. Search Quality/Golden + Search Knowledge mínimos;
7. Search lexical/projection/ranking;
8. compatibilidade/cutover sob B-003;
9. uma única jornada IA P1, depois de owner estável;
10. RAG P2 após retrieval confiável;
11. P3/P4 somente por reabertura formal.

### Infra própria

Apenas a **Search Retrieval Projection `post|item`** permanece aprovada no baseline. Nenhum DDL foi escolhido.

Continuam fora do baseline:

- Analytics detalhado;
- durable queue;
- vector store/embeddings;
- semantic/hybrid/rerank;
- agentes/tools;
- rollups/materializações especulativas;
- REST/SPA sem consumidor.

## Dados de cutover

Não podem ser perdidos sem decisão:

- WP_Post/Elementor/taxonomias editoriais;
- oito valores GRE;
- review/include_ai/notas/revisor/histórico KB2Ops válidos;
- classificações KB2Ops realmente utilizadas;
- vocabulary/bindings/relevance rules ASI manuais reais;
- Golden Queries ASI úteis.

Índices, caches, filas, telemetria, migrations registry e outros derivados não são automaticamente migrados.

## Blockers por slice

- B-001 -> Search/RAG/embedding.
- B-002 -> profiling/cutover classificatório.
- B-003 -> retirada de aliases/plugins/adapters.
- B-004 -> Analytics/query logging.
- B-005 -> deep-link/anchors públicos de item.
- B-006 -> write path composto definitivo.
- B-007 -> durable queue/async indexing.

T095 deve decidir cada blocker no contexto da capacidade que pretende habilitar. Não converter blocker contextual em NO-GO global sem dependência real.

## IA/vetor

- P0 determinístico primeiro.
- P1 Classificação ou Summary assistidos, uma jornada por vez.
- P2 RAG opcional e lexical-first possível.
- P3 embeddings/semantic/rerank postergados.
- P4 agentes/tools postergados.
- Microsoft Foundry é provider preferencial candidato, nunca domínio.

## Próxima fase — revisões independentes

1. **T090 — Arquiteto WordPress**: eliminar reconstrução do Core e validar primitives.
2. **T091 — Crítico de Simplicidade**: tentar remover classes/stores/endpoints/capacidades.
3. **T092 — Segurança**: capabilities, nonces, data egress, secrets, prompt injection, cutover.
4. **T093 — QA/Regressão**: gates G-001–G-140, Golden, evidência e estados de NO-GO.
5. **T094 — Produto/Conhecimento**: jornadas, valor, ordem dos slices e paridade percebida.
6. **T095 — Unknowns/blockers**: fechar ou postergar formalmente por slice.
7. **T096 — Relatório final**.
8. **T097 — GO/NO-GO para SPEC-001**.

## Gate

Nenhuma SPEC de runtime começa antes de T097. T059 apenas tornou a arquitetura auditável; não autorizou implementação.
