# Riscos, Drifts e Dívidas — SPEC-000

> Estado após **T091**. Fontes centrais: `matriz-paridade-futura.md`, `revisao-wordpress-t090.md` e `revisao-simplicidade-t091.md`.

## 1. Drifts D-001–D-008

Permanecem válidos:

- D-001 `Objective_Provider`: adapter só por coexistência comprovada.
- D-002 evento Objective: somente pós-write confirmado.
- D-003 `post_content` vs Elementor: extractor único quando necessário; B-001.
- D-004 múltiplos parsers: descartados.
- D-005 GAC: fora do core.
- D-006 classificação duplicada: owner resolvido; B-002 no cutover.
- D-007 UI/CSS fragmentados: DS único, porém incremental.
- D-008 AI READY: regra única enquanto vigente.

## 2. Blockers B-001–B-007

Continuam contextuais:

- B-001 Search/RAG/embedding produtivos.
- B-002 profiling/cutover classificatório.
- B-003 retirada de plugins/aliases/adapters.
- B-004 Analytics/query logging.
- B-005 deep-link público de item.
- B-006 write path composto definitivo.
- B-007 durable queue/async indexing.

T091 reforça que nenhum blocker deve antecipar infraestrutura de uma capacidade que ainda não existe.

## 3. Riscos ativos preservados

- extração parcial/custom widgets;
- projection stale;
- fallback lexical ilimitado;
- Golden stale/vazia tratada como PASS;
- adapter permanente;
- evento antes da persistência;
- supercoleta Analytics;
- queue prematura/falsa;
- activation pesada;
- scans/meta LIKE sem bounds;
- benchmark fictício;
- UI fragmentada;
- LLM no caminho crítico;
- IA persistindo owner;
- provider lock-in;
- batch sem budget/NO_CHANGE;
- embedding drift;
- semantic piorando Golden blocking;
- prompt injection;
- secrets em logs;
- SSRF/provider endpoint;
- “primeiro runtime” virar big-bang.

## 4. Guardrails T090 preservados

- não duplicar bounded history + meta revisions sem necessidade;
- Search Knowledge/Golden WordPress-first;
- taxonomias internas fail-closed para exposição pública;
- versão mínima WordPress deve ser compatível com features usadas;
- provider endpoint configurável passa por revisão SSRF/allowlist.

## 5. Novos riscos T091

### X-048 — fundação antes de produto

**Risco:** construir Core genérico, DS completo, settings, extractor, Search e operações antes de um fluxo ponta a ponta.  
**Tratamento:** primeira SPEC = um vertical slice mínimo; infraestrutura só quando consumida.

### X-049 — Content Extractor sem consumidor

**Risco:** gastar esforço e fixar contrato antes da Search/IA que realmente o validará.  
**Tratamento:** extractor nasce com primeiro consumidor; B-001 é gate contextual.

### X-050 — DS virar projeto paralelo

**Risco:** biblioteca extensa sem telas reais.  
**Tratamento:** tokens/componentes apenas conforme uso, mantendo qualidade visual/a11y.

### X-051 — taxonomias/classificação big-bang

**Risco:** registrar todos os eixos, migrar tudo e aumentar superfície sem prioridade de produto.  
**Tratamento:** um eixo/slice; B-002 no cutover.

### X-052 — abstração arquitetural sem segundo caso

**Risco:** event bus, repositories, DI container, cache service e factories aumentarem acoplamento sem problema real.  
**Tratamento:** descartados no baseline; reentrada somente com ADR e caso concreto.

### X-053 — Search Knowledge antecipado

**Risco:** vocabulary/bindings/rules virarem subsistema antes de existir falha real do ranker básico.  
**Tratamento:** postergar até Golden/uso comprovar necessidade.

### X-054 — item/deep-link antecipado

**Risco:** cardinalidade, identidade e B-005 elevarem complexidade antes de provar valor além do post-level Search.  
**Tratamento:** post-level primeiro; item/deep-link posterior.

### X-055 — Golden UI excessiva

**Risco:** construir produto de administração de Golden antes de precisar da suíte como gate.  
**Tratamento:** contrato/evidência primeiro; CRUD visual só se curadoria real exigir.

### X-056 — Operations Center sem operação

**Risco:** painel/migration framework/job UI antes de queue/migração real.  
**Tratamento:** descartado; ferramentas operacionais nascem por caso.

### X-057 — provider seam genérico antecipado

**Risco:** abstração multi-provider antes do primeiro caso de IA.  
**Tratamento:** primeiro caso usa adapter mínimo; interface/factory só com segundo problema real.

## 6. Resultado T091

- finding bloqueante: **0**;
- nova infraestrutura autorizada: **0**;
- capacidades avançadas reabertas: **0**;
- abstrações genéricas removidas do baseline: event bus, repositories, container, cache service, migration/operations frameworks e provider factory antecipada;
- runtime criado: **não**.

## 7. Próximo passo — T092

Revisar segurança da arquitetura mínima, com foco em capability, CSRF, IDOR, sanitização/escaping, superfícies públicas, compatibilidade, SSRF/secrets/data egress, prompt injection, Search scope e ações destrutivas.

## Status

**T091 concluída documentalmente.** Próximo passo: **T092**. SPEC-001 permanece bloqueada até T097.