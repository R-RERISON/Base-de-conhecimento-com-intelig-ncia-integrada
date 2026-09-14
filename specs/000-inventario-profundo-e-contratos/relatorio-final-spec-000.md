# T096 — Relatório Final da SPEC-000

> Estado: **CONCLUÍDO — RECOMENDAÇÃO GO CONDICIONADO PARA T097 AUTORIZAR SPEC-001**.  
> Baseline de fechamento: `main @ d5fdaf4c9416e22d065a3fa3b0142f52286cd77f`.  
> Escopo: síntese final de T000–T095. Nenhum runtime foi criado.

## 1. Missão da SPEC-000

A SPEC-000 existiu para responder, antes de escrever o novo plugin:

> **O que existe hoje, o que realmente precisa sobreviver, quem é owner de cada conceito e qual é a menor arquitetura futura suficiente?**

O trabalho inventariou três referências:

- KB2Ops `0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`;
- ASI `4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1`;
- GRE `0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365`.

E consolidou seus contratos em uma arquitetura WordPress-first, modular, incremental e anti-big-bang.

---

## 2. Trabalho concluído

### Preparação/governança
- T000–T002: baseline, Constituição, Manifesto, agentes, skills e roadmap.

### Inventários
- T010–T019: KB2Ops.
- T020–T034: ASI.
- T040–T047: GRE.

### Cruzamento
- T050: persistência por conceito/owner.
- T051: integrações/hooks/rotas.
- T052: ownership.
- T053: sobreposição.
- T054: drifts, compatibilidade e blockers.
- T055: regressão/Golden.
- T056: WordPress-first.
- T057: infraestrutura própria mínima.
- T058: IA/vetor.
- T059: matriz de paridade futura final.

### Revisões finais
- T090: Arquiteto WordPress — PASS.
- T091: Crítico de Simplicidade — PASS.
- T092: Segurança — PASS arquitetural.
- T093: QA/Regressão — PASS documental.
- T094: Produto/Conhecimento — PASS.
- T095: blockers/unknowns por slice — zero blocker aberto para SPEC-001 candidata.

---

## 3. Arquitetura final

### Fonte da verdade

**Editorial:** WordPress/Elementor.

Invariantes:

- `post_title`, `post_content`, `_elementor_data`, status/publicação permanecem do WordPress/Elementor;
- pipeline derivado nunca escreve `_elementor_data`;
- pipeline derivado nunca reescreve `post_content` silenciosamente;
- plugin não publica em nome do autor por efeito colateral.

### Owners lógicos

- Editorial WordPress/Elementor;
- Resumo Executivo;
- Classificação de Conhecimento;
- Revisão/Governança;
- Content Extraction;
- Search Knowledge;
- Search Indexing;
- Search Quality;
- Analytics/Search Intelligence;
- Core Configuration;
- Operations/Lifecycle;
- AI Assist.

Um conceito canônico possui um owner. Projection/cache/index/vector nunca é fonte da verdade.

---

## 4. WordPress-first

Primitives preferidas:

- WP_Post;
- Metadata API;
- Taxonomy API;
- Options/Settings;
- Users/Roles/Capabilities;
- Nonces;
- hooks/actions específicos;
- admin-post;
- AJAX somente se live UX exigir;
- REST somente com consumidor real;
- Site Health;
- Transients/Object Cache quando medido;
- WP-Cron apenas como trigger;
- WordPress HTTP API para integração externa.

T090 não encontrou infraestrutura própria adicional injustificada.

---

## 5. Infraestrutura própria aprovada

Apenas uma família foi aprovada:

### Search Retrieval Projection

- derivada e reconstruível;
- inicialmente um único store lógico para documentos `post|item`;
- FULLTEXT quando suportado;
- fallback lexical bounded;
- WordPress revalida status/scope/capability;
- B-001 + Golden + benchmark antes de produção.

T091/T094 simplificam sua ordem:

1. Search post-level primeiro quando suficiente;
2. item/deep-link somente por evidência.

Nenhuma tabela foi criada na SPEC-000.

---

## 6. Capacidades deliberadamente postergadas

- Search Analytics detalhado/query logging;
- durable queue;
- embeddings/vector store;
- semantic/hybrid retrieval;
- model reranking;
- agentes/tools;
- Foundry Agent File Search como core;
- Word Cloud;
- GAC sem consumidor comprovado;
- dashboards/rollups sem pergunta de produto;
- SPA/REST sem consumidor;
- framework genérico de migrations/events/repositories/cache/provider.

Capacidade postergada implementada silenciosamente é regressão arquitetural.

---

## 7. IA

Prioridades:

- P0: determinístico/core;
- P1: IA assistiva sob demanda;
- P2: RAG/síntese opcional sobre retrieval confiável;
- P3: embeddings/semantic/rerank por evidência;
- P4: agentes/tools por caso multi-step comprovado.

Regras:

- IA sugere; humano decide; owner persiste;
- retrieval precede síntese;
- Search lexical funciona sem IA;
- Microsoft Foundry é provider candidato, não domínio;
- primeiro runtime pode ter zero IA externa;
- provider seam só nasce com caso real;
- custo/budget/NO_CHANGE/data egress são gates obrigatórios quando IA existir.

---

## 8. Segurança

T092 fixou NO-GO transversal para:

- mutação sem capability na ação/objeto;
- mutação browser sem CSRF adequado;
- IDOR;
- mass assignment;
- output sem escaping contextual;
- SQL dinâmico inseguro;
- projection usada como autoridade;
- destrutivo via GET/sem intenção explícita;
- secrets expostos;
- SSRF sem mitigação;
- IA persistindo owner diretamente;
- telemetria detalhada sem B-004.

Toda SPEC mutável deve possuir matriz:

`ação -> ator -> capability -> método -> nonce/CSRF -> inputs/validação -> persistência -> confirmação -> diagnóstico`.

---

## 9. QA e regressão

Estados de evidência:

`PASS | FAIL | NOT_RUN | NOT_CONFIGURED | STALE | N/A | POSTERGADO | WAIVED`.

Regras:

- PASS vazio não existe;
- gate ativo NOT_RUN/NOT_CONFIGURED/STALE = NO-GO;
- N/A exige justificativa;
- Golden blocking fail = NO-GO;
- evidência stale não é evidência corrente;
- UI crítica exige browser/manual quando necessário;
- package futuro deve ser o mesmo artefato testado.

Toda futura SPEC possui Matriz de Evidência própria.

---

## 10. Dados que não podem ser perdidos

Preservar até decisão/cutover comprovado:

1. WP_Post/Elementor/taxonomias editoriais;
2. oito valores GRE por post respeitando owners futuros;
3. Review/include_ai/notas/reviewer/history KB2Ops válidos;
4. classificações KB2Ops efetivamente usadas;
5. Search Knowledge ASI manual real;
6. Golden Queries ASI úteis;
7. configuração necessária à coexistência durante transição.

Não são canônicos automaticamente:

- índices/caches;
- queue;
- telemetria histórica;
- quality_daily;
- migrations registry;
- embeddings/vectors;
- projections reconstruíveis.

---

## 11. Blockers B-001–B-007

Após T095:

| Blocker | SPEC-001 candidata | Destino |
|---|---|---|
| B-001 Extractor | não aplicável | Search/RAG |
| B-002 profiling classificação | não aplicável | Classificação/cutover |
| B-003 preflight consumidores | não bloqueia dev/homolog | cutover/aliases/removal |
| B-004 Analytics/query policy | não aplicável | Analytics |
| B-005 anchors/deep-link | não aplicável | item-level Search |
| B-006 write multi-campo | **fechado conceitualmente** | Summary |
| B-007 stale/async | não aplicável | queue/async |

**BLOCKER_SPEC001 aberto: zero.**

---

## 12. B-006 — decisão final para Summary

Para save dos três campos:

`authorize -> validate all -> snapshot -> diff -> write changes -> read-after-write -> compare`

Se estado final != esperado:

`FAIL -> compensação best-effort -> reread`

Resultados:

- estado esperado -> SUCCESS;
- snapshot restaurado -> FAIL seguro;
- compensação incompleta -> PARTIAL_FAILURE_CRITICAL explícito.

`update_post_meta()` isolado não define sucesso; estado relido define.

---

## 13. Candidato SPEC-001

### Nome conceitual

**Core mínimo + Summary narrativo**.

### Usuário primário

Analista de Conhecimento.

### Jornada

`selecionar artigo -> ler Summary -> editar objective/escalation/important -> salvar -> reler -> confirmar estado`

### Storage inicial canônico

- `_bdc_es_objective`;
- `_bdc_es_escalation`;
- `_bdc_es_important`.

Reutilizar as chaves GRE evita migration e preserva dados.

### Segurança

- `current_user_can( 'edit_post', $post_id )` no objeto;
- POST + nonce;
- allowlist de 3 campos;
- validação/sanitização;
- escaping contextual;
- read-after-write;
- zero mass assignment.

### Superfície

- wp-admin server-rendered;
- sem AJAX/REST/SPA;
- sem settings page;
- sem tabela/schema/migration;
- sem Search/Golden/Analytics/IA;
- sem event bus/history/audit genérico.

### Baseline obrigatória da própria SPEC

Antes do código, enumerar os `post_type` reais pertencentes à Base de Conhecimento. Se não for possível provar o target, implementação fica NOT_READY.

### Rollback

Desativar o novo plugin preserva postmeta. Nenhuma limpeza automática.

---

## 14. Ordem recomendada posterior

Sujeita às futuras SPECs:

1. SPEC-001 — Summary narrativo;
2. Classificação mínima — eixo inicial recomendado `knowledge_type`;
3. Review mínimo;
4. Content Extractor + Search post-level + Golden mínimo;
5. classificação adicional/item/deep-link/Search Knowledge;
6. Analytics/IA e demais capacidades apenas por evidência.

---

## 15. Riscos residuais

Não são blockers da SPEC-001, mas permanecem ativos para seus slices:

- extração parcial/custom widgets;
- stale projection;
- profiling classificatório;
- aliases/consumidores reais;
- query logging/privacidade;
- item identity/deep-link;
- queue/async;
- SSRF/secrets/data egress;
- prompt injection;
- semantic/vector drift;
- benchmark e escala reais.

---

## 16. Recomendação ao T097

### Recomendação

**GO CONDICIONADO PARA AUTORIZAR A CRIAÇÃO DA SPEC-001.**

Justificativa:

- arquitetura consolidada;
- WordPress-first aprovado;
- simplicidade aprovada;
- segurança aprovada documentalmente;
- QA definido;
- valor de produto identificado;
- zero blocker aplicável aberto;
- rollback simples;
- nenhum runtime legado precisa ser destruído;
- escopo pequeno e homologável.

### Condição

T097 deve autorizar **a SPEC-001 e seu trabalho**, não declarar a funcionalidade pronta.

A SPEC-001 ainda deve:

- inventariar post types reais antes do código;
- criar sua Matriz de Evidência;
- cumprir B-006 em implementação/testes;
- passar DoD e seus gates antes de qualquer release/cutover.

---

## 17. Estado final T096

- SPEC-000 inventário/cruzamento/revisões: concluídos até T096;
- runtime novo: inexistente;
- schema/tabela/provider/vector: inexistentes;
- blocker SPEC-001 aberto: zero;
- recomendação T097: **GO condicionado**;
- autoridade final: T097.