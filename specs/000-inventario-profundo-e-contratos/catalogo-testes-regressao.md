# Catálogo de Testes, Regressão e Golden Queries — SPEC-000 — T055

> Estado: **T055 concluída documentalmente**.  
> Baseline de entrada: `main @ c68b10644f247e07866d26f08654137be0253eb7`.  
> Baselines de referência: ASI `4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1`, GRE `0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365`, KB2Ops `0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.
>
> Este catálogo define **quais contratos futuros precisam de evidência executável**. Não cria testes de runtime, fixtures, CPTs, tabelas, endpoints, Golden dataset real nem implementação. A SPEC-001 continua bloqueada até T097.

## 1. Objetivo

T055 transforma as decisões T050–T057 em uma política verificável de regressão.

A pergunta deixa de ser “o legado possuía este teste?” e passa a ser:

> **Qual comportamento futuro é obrigatório, qual risco ele controla e qual evidência mínima impede regressão silenciosa?**

O catálogo histórico permanece útil como fonte de aprendizado, mas não é checklist de reprodução do ASI/GRE/KB2Ops.

## 2. Classes de gate

Cada contrato futuro recebe uma destas classes:

- **MUST** — obrigatório no baseline quando a capacidade correspondente existir; falha impede GO.
- **CONDICIONAL** — só se torna obrigatório quando a feature/slice ativar a capacidade.
- **POSTERGADO** — capacidade deliberadamente fora do baseline; ausência não é falha, mas implementação silenciosa é regressão.
- **N/A** — explicitamente não aplicável à SPEC/release avaliada; deve haver justificativa, não omissão.

### Regra de falha

- gate MUST falhou -> **NO-GO**;
- gate MUST sem evidência -> **NOT_VERIFIED / NO-GO**;
- gate CONDICIONAL ativado sem evidência -> **NO-GO**;
- gate POSTERGADO implementado sem reabertura formal -> **NO-GO arquitetural**;
- warning conhecido -> exige registro explícito, owner e decisão; não pode desaparecer do relatório.

## 3. Níveis de evidência

Testes por inspeção textual são somente guardrails auxiliares. A evidência prioritária é comportamental.

1. **Unitário puro** — normalização, funções determinísticas, regras de domínio, scoring e state transitions.
2. **Integração WordPress real** — Metadata/Taxonomy APIs, capabilities, nonces, hooks, lifecycle, fixtures Elementor.
3. **Contrato de persistência/projection** — read-after-write, idempotência, rebuild, freshness e falhas.
4. **Golden Queries** — comportamento de retrieval/ranking em dataset governado.
5. **Browser/E2E** — fluxos administrativos/públicos, acessibilidade básica e bypasses.
6. **Benchmark** — caminhos críticos medidos com corpus representativo.
7. **Release/package** — instalação, upgrade, rollback, pacote determinístico e evidência de homologação.

## 4. Fontes históricas usadas sem copiá-las literalmente

### ASI 4.6.8

A baseline comprova valor em:

- ranking de post/item;
- identidade de item;
- FULLTEXT + fallback;
- Golden Queries;
- stale-state/curadoria;
- scope e navegação fail-closed;
- queue durável quando assíncrona;
- telemetria correlacionada quando Analytics existe;
- performance bounds + benchmark separado;
- build/release e diagnostics.

A Golden Suite ASI também estabelece princípios que T055 preserva:

- suíte vazia = `not_configured`, nunca PASS;
- expectativa possui query, alvo, rank máximo e severidade;
- falha blocking = NO-GO;
- alteração do conjunto/ranker invalida evidência anterior;
- execução de ranking é explícita; leitura de status não executa ranking;
- Golden não depende de identidade/session/journey de Analytics.

### GRE 0.6.0

Preservar como regressão:

- Metadata API/Meta Contract;
- allowlist/sanitização;
- capability por objeto;
- partial update;
- empty-delete;
- read-after-write;
- server-rendered baseline;
- package reproduzível;
- ausência de side effects em leitura.

Gaps conhecidos que precisam virar testes futuros: B-006 e evento pós-write confirmado.

### KB2Ops 0.2.1

Preservar comportamento, não o relatório estático:

- Content Extractor Elementor-aware;
- Review/AI READY;
- Search scopes e detail recheck;
- Design System/a11y;
- lifecycle reversível;
- package determinístico.

Como a baseline não contém suíte executável equivalente ao relatório de release, todo contrato KB2Ops crítico precisa reaparecer como teste versionado no novo projeto.

---

# 5. Gate G-001 — Editorial e fronteira WordPress/Elementor

**Classe:** MUST.

Provar:

- nenhuma rotina derivada escreve `_elementor_data`;
- nenhuma rotina derivada reescreve `post_content` silenciosamente;
- `post_title` continua canônico;
- edição/publicação oficial continuam no WordPress/Elementor;
- projection/cache/vector/IA podem ser removidos/reconstruídos sem restaurar conteúdo editorial;
- leitura de conteúdo não produz mutações colaterais.

**NO-GO:** qualquer write editorial não solicitado explicitamente pelo fluxo editorial oficial.

# 6. Gate G-010 — Content Extractor / B-001

**Classe:** MUST antes de Search/RAG produtivos.

Fixtures mínimas:

1. `post_content` sem Elementor;
2. Elementor JSON válido com widgets textuais conhecidos;
3. nested containers/sections;
4. entities, whitespace, listas, headings e tabelas;
5. JSON inválido/fallback seguro;
6. widget conhecido + custom widget relevante para provar detecção de extração parcial;
7. shortcode allowlist (`table`/`tablepress`) e shortcode ausente/falhando;
8. conteúdo sem texto útil;
9. estrutura com imagens/tabelas/headings sem duplicação indevida;
10. caracteres acentuados/Unicode.

Contratos:

- output determinístico para a mesma fonte/version;
- cache por request não altera resultado;
- custom widget relevante não some silenciosamente;
- falha é diagnosticável e non-fatal quando possível;
- nenhum `do_shortcode()` irrestrito;
- zero writes editoriais.

**B-001 só pode fechar com corpus representativo real + fixtures versionadas.** Unit test de parser isolado não basta.

# 7. Gate G-020 — Summary / Metadata

**Classe:** MUST quando Summary entrar no slice.

Provar:

- `objective`, `escalation`, `important` pertencem ao Summary owner;
- `post_title` não é duplicado em meta;
- allowlist de campos;
- sanitização por campo;
- `edit_post` por objeto + nonce em mutação;
- omitted field permanece intacto;
- vazio remove meta quando o contrato assim definir;
- read-after-write confirma estado final;
- leitura é side-effect free;
- compatibilidade dos oito valores históricos não cria owner paralelo.

## B-006 — falha multi-campo

Antes do write path composto definitivo, a SPEC de implementação deve escolher e testar explicitamente a semântica de falha tardia: falha total, parcial detectável ou compensação. T055 não escolhe a estratégia, mas **proíbe sucesso falso**.

# 8. Gate G-030 — Classificação

**Classe:** MUST quando cada conceito entrar no slice; migração/cutover condicionados a B-002.

Provar por conceito:

- owner único;
- cardinalidade e vocabulário coerentes;
- Metadata versus Taxonomy conforme decisão versionada;
- `service != affected_service` sem profiling que prove equivalência;
- `technologies != systems_involved` sem profiling que prove equivalência;
- audiência unificada semanticamente;
- filtros/facetas não alteram dados editoriais;
- migração é idempotente e não perde valores históricos.

**Não existe teste que autorize escolher Taxonomy apenas porque o campo “parece classificatório”.**

# 9. Gate G-040 — Revisão, governança e eventos

**Classe:** MUST quando Review entrar no slice.

Provar:

- estados de review permitidos e transições determinísticas;
- reviewer/time derivam de WP Users/estado confirmado;
- history permanece bounded enquanto esse for o contrato;
- `include_ai` é decisão humana;
- AI READY = `publish + approved + 8/8 + include_ai` enquanto essa baseline permanecer vigente;
- approval de artigo != Apply de Search Knowledge;
- evento só é emitido depois de persistência confirmada;
- consumer de evento é idempotente;
- falha do downstream não reverte silenciosamente o canônico já confirmado.

# 10. Gate G-050 — Search Retrieval Projection

**Classe:** MUST para a futura Search própria.

T057 autorizou **um único store lógico de documentos derivados `post|item`** como baseline arquitetural.

Provar:

- um post e N itens coexistem no mesmo modelo lógico inicial;
- `document_key`/item identity são estáveis;
- mesma fonte + mesma versão gera projection determinística;
- `source_hash`/`content_hash` permitem `NO_CHANGE` e reconciliação;
- rebuild é idempotente;
- remoção/despublicação invalida exposição;
- projection stale nunca vira autoridade de publicação/permissão;
- scope/status/capability são revalidados no WordPress antes da exposição final;
- failure/rebuild da projection não altera canônico;
- estado `healthy|stale|degraded|failed` ou semântica equivalente é distinguível;
- nenhum parser alternativo bypassa o Content Extractor.

## FULLTEXT + fallback

Provar no ambiente MariaDB/MySQL suportado:

- caminho FULLTEXT funcional quando disponível;
- fallback lexical bounded quando indisponível/degradado;
- fallback não executa LIKE ilimitado em `_elementor_data`;
- ausência de FULLTEXT não exige IA/vetor;
- resultado degradado é diagnosticável.

**Separar post index e item index em stores físicos diferentes exige nova evidência/benchmark; não é baseline T055.**

# 11. Gate G-060 — QueryContext, ranking e explicabilidade

**Classe:** MUST quando Search entrar no slice.

Provar:

- normalização determinística;
- accents/case e tokens tratados conforme contrato;
- limites de tamanho/tokens;
- ranking reproduzível com sinais observáveis;
- nenhuma mudança de ranking ocorre silenciosamente sem versionamento/evidência;
- Search lexical funciona sem IA/vetor;
- cache não altera semanticamente a ordenação;
- zero result é distinto de erro/degraded.

---

# 12. Golden Queries — contrato canônico futuro

## 12.1 Storage baseline

T056 decidiu **`WP_Post` interno + Metadata/Revisions** como primitive inicial de Search Quality/Golden.

T055 não autoriza tabela Golden.

Golden Query é **configuração de QA governada**, não telemetria de usuário.

## 12.2 Registro conceitual mínimo

Nomes finais permanecem abertos, mas cada expectativa ativa precisa representar:

- query curada;
- query normalizada/identidade estável;
- `expected_post_id` canônico;
- `expected_item_key` opcional;
- `max_rank`;
- severidade `blocking|warning`;
- estado ativo/inativo;
- origem governada (`manual`, import validado ou equivalente);
- notas/racional;
- autor/revisor + datas via WordPress;
- revisão/versionamento quando aplicável.

Query de Golden não deve ser importada automaticamente de logs reais. Qualquer origem em telemetria futura depende de B-004 + revisão/minimização.

## 12.3 Famílias mínimas de cenário

O dataset ativo da Search deve cobrir, quando existirem no corpus:

- termo exato/título;
- sigla/acrônimo;
- acento/case/normalização;
- consulta multi-token;
- consulta de linguagem natural;
- sinônimo/equivalência governada;
- sinal de Summary/Classificação quando usado pelo ranker;
- item/trecho com identidade estável;
- consultas ambíguas relevantes com expectativa de rank explícita;
- pelo menos um caso crítico de negócio marcado `blocking`.

T055 não inventa quantidade mínima numérica. Cobertura é por **família de risco/comportamento**, não por inflar contagem.

## 12.4 Execução

Golden execution deve ser explícita e read-only em relação ao conteúdo/ranking.

A evidência de uma execução deve registrar, no mínimo:

- status `pass|fail|not_configured|not_run` ou semântica equivalente;
- hash/version do conjunto ativo;
- versão do contrato/ranker de post;
- versão do item ranker/identity quando aplicável;
- versão do extractor/indexer quando afeta retrieval;
- referência da projection/dataset usada na execução;
- data/hora;
- por expectativa: esperado, rank real, pass/fail e evidência suficiente para diagnóstico.

A leitura de status em dashboard/Site Health não deve executar ranking implicitamente.

## 12.5 Invalidação de evidência

Resultado anterior deixa de ser evidência corrente quando muda qualquer elemento material, incluindo:

- conjunto Golden ativo;
- expectativa/rank/severidade;
- ranker/QueryContext;
- item ranker/identity;
- extractor/index contract;
- dataset/projection usada para o release candidate.

A implementação pode escolher a estratégia técnica de fingerprint/versionamento, mas não pode declarar evidência antiga como atual sem provar equivalência.

## 12.6 Regras GO/NO-GO

- zero Golden ativa -> `NOT_CONFIGURED` -> **NO-GO para release inicial/alteração de Search**;
- Golden não executada na revisão corrente -> `NOT_RUN` -> **NO-GO**;
- falha `blocking` -> **NO-GO**;
- falha `warning` -> não vira PASS silencioso; exige decisão/waiver versionado ou correção antes do GO;
- suíte PASS com evidência stale -> **NO-GO**;
- suíte PASS não substitui benchmark, security ou B-001.

## 12.7 Segurança/privacidade

- Golden não armazena IP, session hash, identity hash ou journey;
- export/evidência não depende de Analytics;
- mutação exige capability própria ou `manage_options`, nonce e POST;
- execução não concede capability de edição de conteúdo alvo.

---

# 13. Gate G-070 — Search scope, segurança e exposição

**Classe:** MUST para Search.

Provar:

- scopes `published`, `approved`, `ai_ready` ou sucessores têm semântica única;
- detail route não contorna scope;
- `require_login`, se existir, não pode ser bypassado;
- resultado da projection é revalidado contra post canônico;
- output escaped;
- mutações admin usam POST + nonce + capability;
- AJAX live, se existir, possui nonce/rate-limit apropriado;
- REST não nasce sem consumidor formal.

# 14. Gate G-080 — Analytics baseline negativo

**Classe:** MUST como **prova de ausência** no baseline T057.

Enquanto F-057-02 estiver POSTERGADA:

- Search funciona sem Analytics;
- falha/ausência de Analytics não derruba Search;
- query text não é persistida silenciosamente;
- não existem stores events/interactions/outcomes por acidente;
- telemetria histórica não é migrada automaticamente.

Se Analytics detalhado for reaberto, este gate é substituído por uma suíte CONDICIONAL que exige primeiro B-004 e então cobre finalidade, minimização, retention, acesso, integridade, idempotência e performance.

# 15. Gate G-090 — Queue baseline negativo

**Classe:** MUST como **prova de independência** enquanto F-057-03 estiver POSTERGADA.

Provar:

- funcionamento do baseline não depende de durable queue;
- indexação por post/rebuild bounded possuem caminho explícito sem queue;
- WP-Cron, se usado, é trigger e não durable store;
- Options/Transients não são fila improvisada;
- activation não inicia rebuild massivo silencioso.

Se fila futura for aprovada, tornam-se MUST: claim atômico, lease, retry/backoff, attempts budget, dead/recovery, worker bounded, idempotência, observabilidade e B-007.

# 16. Gate G-100 — Compatibilidade e cutover

**Classe:** CONDICIONAL à coexistência/preflight.

Para qualquer adapter/dual-read/alias:

- consumidor real identificado;
- owner canônico explícito;
- modo limitado documentado;
- observabilidade de uso;
- rollback;
- gate de remoção;
- teste de equivalência;
- dual-write permanente proibido.

B-003 permanece obrigatório antes de remover aliases/plugins antigos.

Shortcodes históricos em preflight:

- `[asi_search_form]`;
- `[bdc_word_cloud]`;
- `[bdc_resumo_executivo]`;
- `[kb2ops_search]`;
- `[kb2ops_portal]`.

Nenhum alias é aprovado por T055.

# 17. Gate G-110 — UI/UX e acessibilidade

**Classe:** MUST para superfícies implementadas.

Provar via DOM/browser quando aplicável:

- Design System único;
- sem segunda sidebar dentro do wp-admin;
- foco visível;
- teclado nos fluxos principais;
- estados não dependem só de cor;
- responsividade nos breakpoints definidos pela SPEC;
- erro/sucesso/bloqueio claros;
- server rendering funciona sem JavaScript quando o fluxo baseline assim exigir;
- progressive enhancement não altera autorização.

# 18. Gate G-120 — Performance e bounds

**Classe:** MUST para caminhos críticos implementados.

T055 não inventa SLA numérico que o ambiente ainda não mediu.

A SPEC de implementação deve definir thresholds antes do GO e registrar benchmark reproduzível com:

- corpus representativo e tamanho registrado;
- distribuição de itens por post;
- fixtures Elementor leves e pesadas;
- p50/p95 e pior caso relevante de Content Extraction/indexação/Search;
- wall time;
- quantidade de queries DB;
- memória;
- duração de rebuild bounded;
- FULLTEXT e fallback;
- concorrência aplicável;
- configuração MariaDB/MySQL relevante.

O histórico ASI de 100k buscas/200k interações é referência daquele Analytics, **não requisito automático** deste produto.

Guardrail estrutural nunca substitui benchmark de ambiente.

# 19. Gate G-130 — Lifecycle, dados, build e rollback

**Classe:** MUST para release instalável.

Provar:

- activation leve e não destrutiva;
- nenhum DROP/purge/rebuild massivo implícito;
- defaults só quando ausentes;
- upgrade/migration idempotentes quando existirem;
- uninstall não destrutivo por default;
- purge exige opt-in explícito, capability, nonce e confirmação;
- posts/Elementor nunca são apagados pelo purge do plugin;
- pacote possui uma única raiz instalável;
- runtime allowlist/artefatos de engenharia controlados;
- versão consistente;
- build reproduzível/checksum;
- instalação/upgrade/rollback testados;
- `CONTINUIDADE.md` e documentação atualizados.

# 20. Gate G-140 — IA/vetor

**Classe:** POSTERGADO para T058.

T055 fixa apenas invariantes que já são constitucionais:

- lexical funciona sem IA/vetor;
- retrieval precede síntese;
- IA não é autoridade editorial;
- saída de IA não persiste canônico sem decisão humana;
- operação de IA em massa não acontece silenciosamente.

T058 deve detalhar gates específicos de provider, embedding, chunks, custo, NO_CHANGE, fallback e rastreabilidade.

---

# 21. Mapa de blockers -> evidência

| Blocker | Evidência obrigatória antes do fechamento aplicável |
|---|---|
| **B-001** | corpus Elementor representativo + fixtures custom widget + detecção de omissão + regressão do extractor |
| **B-002** | profiling/cardinalidade/vocabulário/colisões/filtros + teste de migração idempotente |
| **B-003** | preflight real de consumidores + regressão de aliases/adapters aprovados |
| **B-004** | política de finalidade/minimização/retention/acesso antes de qualquer Analytics detalhado |
| **B-005** | identidade de destino/anchors + browser/deep-link fail-closed antes da paridade pública de item |
| **B-006** | testes de falha tardia/read-after-write/resultado parcial ou compensação conforme semântica escolhida |
| **B-007** | stale/lease/retry/dead/recovery/observabilidade se async queue for reaberta |

Blocker contextual sem capacidade ativa pode permanecer postergado; ele não pode ser marcado PASS artificialmente.

# 22. Matriz mínima por tipo de mudança futura

| Mudança | Gates mínimos |
|---|---|
| Summary/Review | G-001, G-020, G-040, G-070 admin, G-130 + B-006 se write composto |
| Classificação | G-001, G-030, G-070, G-130 + B-002 no cutover |
| Content Extractor | G-001, G-010, G-120 |
| Search lexical/ranking | G-001, G-010, G-050, G-060, Golden, G-070, G-120, G-130 |
| Item/deep-link público | Search gates + B-005 + browser/E2E |
| Compat/cutover | gates do domínio + G-100 + B-003 |
| Analytics detalhado futuro | G-080 deixa de ser negativo; B-004 + nova suíte específica obrigatória |
| Queue futura | G-090 deixa de ser negativo; B-007 + lifecycle de queue obrigatório |
| IA/vetor | definir em T058 + preservar Search lexical independente |

# 23. Evidência de release futura

Um release report deve distinguir no mínimo:

- `PASS`;
- `FAIL`;
- `NOT_VERIFIED`;
- `NOT_CONFIGURED`;
- `N/A` com justificativa;
- `DEGRADED` quando o produto ainda opera com capacidade reduzida aprovada.

É proibido colapsar `not_configured`, `not_run`, `degraded` ou warning em PASS.

A evidência deve registrar commit/build, ambiente relevante, testes executados, resultados, Golden run, benchmarks aplicáveis, gaps e waivers.

# 24. Revisão pelos papéis da SPEC

### Arquiteto WordPress

**APROVA:** testes protegem WordPress-first e impedem tabela/API/job de reaparecer por conveniência.

### Arquiteto de Conhecimento

**APROVA:** Golden é expectativa governada; não é log de usuário. Conteúdo/Resumo/Classificação continuam owners separados de Search Quality.

### Especialista de Search/Retrieval

**APROVA:** Golden, projection, FULLTEXT/fallback, ranker e item identity possuem gates independentes e complementares.

### Especialista de Segurança/Privacidade

**APROVA:** scope recheck, capabilities/nonces, baseline sem query logging e telemetria condicionada a B-004.

### Especialista de Performance

**APROVA COM MEDIÇÃO FUTURA:** T055 define o que medir sem fabricar p95/QPS inexistentes.

### Especialista de QA/Regressão

**APROVA:** relatório histórico não conta como teste reproduzível; contratos críticos exigem evidência executável/versionada.

### Crítico de Simplicidade

**APROVA:** features postergadas possuem testes de ausência/independência, não infraestrutura prematura.

# 25. Critério de fechamento T055

- [x] contratos críticos T050–T057 possuem gate futuro mapeado;
- [x] testes históricos foram separados de contratos futuros;
- [x] Golden storage permanece WordPress-first e sem tabela própria;
- [x] Golden vazia/não executada/stale nunca é PASS;
- [x] failure blocking = NO-GO;
- [x] warning exige decisão explícita;
- [x] Golden é independente de Analytics/identidade de usuário;
- [x] B-001–B-007 possuem evidência/gate contextual;
- [x] Search projection, fallback, scope e freshness possuem regressões definidas;
- [x] Analytics postergado possui gate negativo contra logging silencioso;
- [x] Queue postergada possui gate negativo contra dependência/queue improvisada;
- [x] performance exige benchmark real, sem números inventados;
- [x] lifecycle/build/rollback continuam release gates;
- [x] IA/vetor foram deixados explicitamente para T058;
- [x] nenhum runtime/teste/schema/Golden dataset real foi criado.

## 26. Próximo passo autorizado

**T058 — identificar e priorizar candidatos a IA/vetor**, preservando os gates T055 e o princípio de que Search lexical continua funcional sem essas capacidades.
