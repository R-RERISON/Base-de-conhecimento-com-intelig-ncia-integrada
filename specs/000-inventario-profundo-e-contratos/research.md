# Pesquisa Consolidada — SPEC-000

## Objetivo

Registrar fatos comprovados e decisões documentais do cruzamento das três baselines. Hipótese não vira fato sem evidência versionada; design futuro não apaga cutover/compatibilidade.

## 1. Baselines fixadas

- KB2Ops `0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.
- ASI `4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.
- GRE `0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365`.

## 2. Fatos estruturais

### KB2Ops

- Content Extractor Elementor-aware read-only;
- review/AI READY;
- Search provisória com WP/meta LIKE;
- DS/server-rendered como referência;
- lifecycle reversível;
- gap crítico de custom widgets parcialmente extraídos;
- corpus documentado na ordem de ~700 posts;
- relatório de release não substitui suíte executável versionada.

### ASI

- retrieval lexical/FULLTEXT + fallback;
- ranking explicável por post/item;
- item identity/reconciliation;
- Golden Queries como release evidence;
- Search Intelligence/telemetria quando habilitada;
- durable queue quando workload assíncrono foi assumido;
- performance bounds separados de benchmark;
- 12 tabelas históricas que não são requisito automático futuro.

### GRE

- oito metas via Metadata API;
- `post_title` canônico;
- allowlist/sanitização/capability/nonce;
- read side-effect free;
- sem tabela/REST/AJAX/cron próprios;
- lacuna de atomicidade lógica multi-campo e drift com provider/evento esperado pelo ASI.

## 3. T050–T054 — contratos consolidados

- um conceito canônico = um owner;
- projection nunca é fonte da verdade;
- dual-write permanente proibido;
- compat/dual-read temporários possuem gate de remoção;
- persistir -> confirmar -> emitir;
- consumer idempotente;
- admin-post/server-rendered baseline;
- AJAX somente por live UX;
- REST negado sem consumidor;
- B-001–B-007 são contextuais e versionados em `mapa-contratos-quebrados.md`.

## 4. T056 — WordPress-first

Artefato: `matriz-wordpress-first.md`.

- WP/Elementor continuam fonte editorial.
- Summary e Review permanecem em Metadata/Users/Revisions quando aplicável.
- Classificação permanece em Metadata/Taxonomy; quatro campos continuam em profiling B-002.
- Search Knowledge e Golden ficam em `WP_Post` interno + Metadata/Revisions inicialmente.
- Settings/Security/Health/Cache/Scheduling usam Core.
- native search é fallback, mas não entrega sozinho representação Elementor derivada + item retrieval + ranking composto.

## 5. T057 — infraestrutura própria mínima

Artefato: `infraestrutura-propria-minima.md`.

### Aprovada

**Search Retrieval Projection** reduzida a um store lógico futuro para documentos `post|item`, reconstruível, com identity/hash/version/freshness, FULLTEXT quando suportado e fallback lexical bounded.

### Postergadas

- Analytics Facts: B-004 aberto; nenhuma persistência detalhada/query text por default.
- Durable Job State: sem benchmark/necessidade comprovada; nenhuma queue table autorizada.

Resultado líquido: 12 stores ASI históricos não renascem; uma única família própria é aprovada documentalmente.

## 6. Evidência Golden analisada em T055

ASI 4.6.8 comprova princípios úteis:

- expectativa ativa contém query, target post, item opcional, max rank e severidade;
- suite vazia retorna `not_configured`;
- failure blocking produz fail/NO-GO;
- warning failure é distinguível;
- `set_hash` + versões de ranker tornam evidência stale detectável;
- execução é explícita e status pode ser lido sem rerun;
- export/runner Golden não precisa de identity/session/journey.

T055 preserva esses **princípios**, não a tabela/implementação ASI.

## 7. T055 — regressão e Golden

Artefato canônico: `catalogo-testes-regressao.md`.

### Classes de gate

- `MUST` — ausência/falha = NO-GO.
- `CONDICIONAL` — obrigatório quando a capacidade é ativada.
- `POSTERGADO` — não nasce silenciosamente.
- `N/A` — justificativa explícita obrigatória.

### Gates definidos

- G-001 editorial/Elementor;
- G-010 Content Extractor/B-001;
- G-020 Summary/B-006;
- G-030 Classificação/B-002;
- G-040 Review/eventos;
- G-050 Search Projection;
- G-060 QueryContext/ranking;
- Golden Queries;
- G-070 scope/security;
- G-080 Analytics baseline negativo;
- G-090 queue baseline negativo;
- G-100 compatibilidade/B-003;
- G-110 UI/UX/a11y;
- G-120 performance;
- G-130 lifecycle/build/rollback;
- G-140 reservado a T058.

### Golden — decisão futura

Golden é configuração administrada de Search Quality, não log de usuário.

Storage baseline permanece WordPress-first; nenhuma tabela Golden foi autorizada.

Registro conceitual inclui query curada, target post, item opcional, max rank, severity, estado, origem/notas e revisão. A execução de release deve guardar fingerprint do conjunto e versões/referências de retrieval suficientes para detectar evidência stale.

Estados semânticos mínimos: `PASS`, `FAIL`, `NOT_CONFIGURED`, `NOT_RUN/NOT_VERIFIED`, `DEGRADED` quando aplicável.

- vazia = NOT_CONFIGURED, não PASS;
- stale/não executada = NO-GO quando Search é afetada;
- blocking fail = NO-GO;
- warning não é convertido em PASS silencioso;
- suite PASS não fecha B-001, performance ou security.

### Cobertura Golden

T055 escolheu cobertura por famílias de comportamento, não um número arbitrário: termo exato, sigla, acento/case, multi-token, linguagem natural, sinônimo/equivalência, sinais de Summary/Classificação, item/trecho, ambiguidade relevante e caso blocking de negócio quando aplicável.

## 8. Blockers após T055

| ID | Gate associado |
|---|---|
| B-001 | G-010 + corpus representativo antes de Search/RAG final |
| B-002 | G-030 + profiling/migração classificatória |
| B-003 | G-100 + preflight real de consumidores |
| B-004 | mantém Analytics detalhado postergado; política antes de reabrir |
| B-005 | item/deep-link público exige identity/anchor/browser gate |
| B-006 | G-020 + falha tardia/read-after-write sem sucesso falso |
| B-007 | queue/async somente se reaberta com stale/retry/recovery observáveis |

## 9. Performance

T055 não inventa NFRs ainda inexistentes. A futura SPEC deverá definir thresholds antes do GO e medir corpus/configuração reais: extraction/index/search, p50/p95, wall time, DB queries, memória, rebuild, FULLTEXT/fallback e casos pesados.

O benchmark ASI de 100k/200k pertence ao desenho Analytics histórico e não é requisito automático do novo produto.

## 10. Próximo passo — T058

Avaliar IA/vetor por capacidade, custo e degradação, preservando:

- lexical independente;
- retrieval antes de síntese;
- IA assistiva;
- humano como autoridade;
- NO_CHANGE/hash para evitar retrabalho;
- rastreabilidade e orçamento.

## Estado

T050–T057, incluindo **T055**, concluídas documentalmente. Próximo: **T058**. Nenhum runtime foi autorizado.
