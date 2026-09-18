# SPEC-005 — Search Lexical e Golden Queries

**Status:** ATIVA — DISCOVERY / IMPLEMENTAÇÃO DE RUNTIME BLOQUEADA ATÉ DoR

**Branch:** `spec005-search-lexical-golden-queries`

**Baseline:** `main @ 07f877b2978429dc6b31fbe172e6ce8fca7ee634`

**Pré-requisito:** SPEC-004 CLOSED/main — satisfeito.

**Idioma:** pt-BR.

**Gate atual:** R-500 PASS/CLOSED; T511.2 PASS AMBIENTAL; T513.1 evidência ambiental analisada; T514.2 PASS LOCAL / ambiental pendente; R-510 OPEN.

> **Mantra:** “Quem não sabe onde está, não sabe para onde quer ir”.

## 1. Problema

A Base de Conhecimento precisa recuperar respostas oficiais de forma determinística antes de receber semantic search, vetores, reranking ou IA. O runtime atual possui apenas pesquisa administrativa da Knowledge List baseada em `WP_Query` com parâmetro `s`; não existe Search Retrieval canônico, ranking próprio versionado ou suíte de Golden Queries no novo plugin.

A base é heterogênea e a SPEC-004 provou que o conteúdo semântico não pode ser inferido apenas de markup bruto. Busca futura deve consumir a camada semântica validada pelo Content Extractor/Knowledge Document sem transformar projeções em fonte editorial.

## 2. Usuários

### Primário
Pessoa que precisa localizar rapidamente um artigo oficial/resposta confiável.

### Secundário
Analista de Conhecimento que precisa entender por que um resultado apareceu, validar qualidade e proteger ranking contra regressões.

### Operacional
Administrador autorizado que executa rebuild/diagnóstico/Golden Suite de forma explícita.

## 3. Baseline comprovada

### Novo plugin
- post type canônico atual: `post`;
- Knowledge List usa `WP_Query` + `s`;
- busca administrativa inclui estados editáveis e `perm=editable`;
- não há índice lexical próprio;
- não há tabela Search;
- não há Golden Queries no runtime;
- não há busca vetorial;
- não há IA no retrieval;
- Content Extractor/KD 2.1.0 estão fechados na SPEC-004.

### Corpus ambiental mais recente
Baseline E6 da SPEC-004: 623 artigos:
- 535 `legacy_html`;
- 41 `plain_text`;
- 34 `elementor`;
- 5 `gutenberg`;
- 5 `mixed`;
- 3 `empty`.

### Referência ASI
ASI 4.6.8 @ `c0ddff89caad529ce1bcdc645eb795e4a9b187a1` é referência histórica/funcional, **não dependência de runtime** e não fonte de código.

Contratos preservados:
- lexical antes de semantic;
- normalização determinística e bounded;
- ranking explicável;
- FULLTEXT com fallback lexical quando justificado;
- Golden Queries versionadas;
- suíte vazia = `NOT_CONFIGURED`;
- blocking fail = NO-GO;
- evidência de Golden fica stale quando versão de ranking muda;
- execução da suíte é explícita;
- telemetria não faz parte desta SPEC.

## 4. Jornada mínima da SPEC

`consulta -> normalização -> retrieval lexical -> ranking -> resultados oficiais -> abrir artigo -> executar Golden Suite -> comparar expectativa -> PASS/FAIL explícito`

A primeira implementação será **post-level**. Item-level/deep-link só entra mediante evidência e gate próprio.

## 5. Resultado esperado

1. contrato de query normalization v1;
2. Search Document post-level derivado e reconstruível;
3. engine lexical determinístico e versionado;
4. ranking mínimo explicável;
5. resultado contendo pelo menos post_id, título, URL oficial, score e sinais;
6. fallback/degraded state explícito;
7. Golden Queries environment-owned e versionadas;
8. runner explícito com relatório machine-readable;
9. benchmark WordPress-first;
10. UI mínima homologável compatível com Visual Contract v2;
11. zero dependência de vetor/IA.

## 6. WordPress-first

Antes de criar schema próprio, comparar:

1. `WP_Query`/busca nativa sobre conteúdo oficial;
2. `WP_Query` + filtros/hooks mínimos;
3. projeção lexical reconstruível baseada no Content Extractor;
4. FULLTEXT somente se cobertura/performance justificarem;
5. LIKE bounded somente como fallback controlado.

Search Retrieval Projection é permitida pela SPEC-000, mas **não está automaticamente autorizada**. B-001 + benchmark + Golden devem provar necessidade.

## 7. Princípio de negação

Não criar nesta SPEC sem evidência:
- 12 tabelas ASI;
- fila durável;
- analytics/query logging;
- vocabulary persistido;
- bindings;
- relevance rules administráveis;
- item index;
- anchors/deep-links;
- REST;
- SPA;
- semantic search;
- embeddings;
- reranking por modelo;
- IA;
- Word Cloud;
- Search Intelligence.

Pergunta obrigatória: **qual parte pode ser removida sem perder retrieval confiável e Golden regression?**

## 8. Fonte da verdade

- editorial: WordPress;
- conhecimento derivado: Content Extractor/KD;
- Search Document/índice: projeção reconstruível;
- Golden Query: expectativa de qualidade, nunca conteúdo editorial.

Busca não pode alterar post, metadata editorial, taxonomia ou review.

## 9. Query normalization v1

Deve ser determinística e bounded:
- trim;
- collapse whitespace;
- remover markup;
- normalização de caixa;
- normalização acentual somente se comprovadamente segura para matching;
- tokens limitados;
- query original preservada para explicação;
- nenhuma expansão hardcoded de domínio no v1;
- sem IA.

Erros de digitação, siglas e aliases devem entrar primeiro na Golden Suite; mecanismo de expansão só nasce após lacuna comprovada.

## 10. Ranking v1

Princípio inicial, sujeito ao gate de baseline:
- exact title > partial title;
- título > summary/objective;
- headings/estrutura > corpo;
- classificação canônica pode ser sinal se comprovado;
- cobertura de tokens deve ser observável;
- recência não deve superar relevância textual por default;
- score deve ter versão de algoritmo;
- empates precisam ser determinísticos.

Nenhum peso do ASI é herdado automaticamente.

## 11. Golden Queries

Cada expectativa deve conter no mínimo:
- id estável;
- query original;
- query normalizada;
- expected_post_id;
- max_rank;
- severity: `blocking|warning`;
- rationale;
- source/provenance;
- active;
- versão do contrato.

Opcional futuro: expected item/deep-link.

Regras:
- conjunto vazio = NOT_CONFIGURED;
- blocking failure = gate FAIL;
- warning não bloqueia;
- resultado deve registrar actual rank e top IDs;
- set hash determinístico;
- mudança do ranker invalida evidência anterior;
- execução somente por ação explícita;
- relatório não inclui identidade/telemetria.

## 12. Segurança

Search read path:
- não mutante;
- respeitar visibilidade/status/capabilities no contexto administrativo;
- superfície pública futura só pode retornar conteúdo publicável e autorizado;
- SQL sempre preparado;
- limites rígidos de query, tokens e resultados;
- output escapado;
- nenhuma projection decide autorização.

Golden management/run:
- capability dedicada ou capacidade WordPress mínima justificada;
- POST + nonce para mutações;
- allowlist de campos;
- suíte executada explicitamente.

## 13. UI/UX

Visual Contract v2 é obrigatório.

A SPEC não altera o shell do WordPress.

Primeiro slice de homologação deve possuir:
- input de consulta;
- loading quando aplicável;
- resultados hierárquicos;
- empty state;
- erro técnico distinto de zero-result;
- degraded/fallback state;
- teclado/focus;
- responsividade 782px/520px.

**Superfície inicial decidida em R-500: ADMIN-FIRST / Knowledge List.** Superfície pública permanece postergada; nenhuma busca global do tema/WordPress será interceptada.

## 14. Dados e persistência

Tabelas próprias são permitidas quando necessárias e aprovadas por gate. O requisito é ownership BDC, lifecycle próprio e independência total do ASI.

Candidatos, em ordem:
1. zero schema próprio;
2. Options apenas para configuração/versões pequenas;
3. uma Search Retrieval Projection post-level reconstruível;
4. Golden Query persistence mínima.

Qualquer tabela exige documento de decisão contendo volume, query pattern, índice, rebuild, uninstall, rollback e benchmark.

## 15. Performance

Baseline deve medir:
- latência p50/p95;
- resultados examinados;
- memória quando observável;
- tempo de rebuild quando existir projection;
- comportamento sem FULLTEXT;
- comportamento com corpus real.

Metas numéricas finais só serão congeladas após R-500; inventar SLA antes de medir é proibido.

## 16. Gates

### R-500 — Search Baseline / Definition of Ready
- confirmar corpus/post type/status/scope;
- medir `WP_Query s`;
- provar cobertura/gaps contra Content Extractor;
- levantar consultas reais/candidatas;
- decidir superfície inicial;
- benchmark inicial.

### R-510 — Golden Dataset v1
- conjunto real não vazio;
- expectativas com origem humana/curada;
- continuidade objetiva validável automaticamente;
- ambiguidade não resolvível objetivamente entra em `AMBIGUOUS_QUARANTINED`, sem escolha manual obrigatória;
- blocking/warning;
- hash/version;
- cobertura de classes: termo simples, composto, sigla, linguagem natural, variação/erro quando real.

### Automação T513/T514
Contrato: `r510-automated-golden-validation-contract-v1.md`.

O Auto Validator pode confirmar uma expectativa existente, mas não pode criar ou substituir `expected_post_id`. Estados: `AUTO_PASS | AMBIGUOUS_QUARANTINED | AUTO_FAIL`.

ADR-005-002 separa Golden Relevance, Technical Challenge e Real-world Query Enrichment. Synthetic/corpus-derived nunca é apresentado como uso real.

### G-520 — Search Contract v1
- normalizer/ranker/result contract fechado;
- WordPress-first decision;
- storage decision;
- security matrix;
- rollback.

### G-530 — Lexical Engine Local
- implementação mínima;
- testes determinísticos;
- nenhuma IA/vetor;
- zero write editorial.

### G-540 — Corpus/Index Compatibility
- full-corpus;
- determinismo;
- zero fatal;
- coverage explícita;
- rebuild/idempotência se projection existir.

### G-550 — Golden Query Gate
- execução explícita;
- blocking=0;
- evidence current;
- relatório machine-readable.

### G-560 — Human Search Acceptance
- relevância humana;
- zero-result/error/degraded;
- UX/accessibility.

### G-570 — Security & Performance
- capability/scope;
- SQL/prepared;
- bounds;
- p50/p95;
- abuse cases básicos.

### G-580 — Lifecycle/Rebuild
- activation leve;
- update não reindexa destrutivamente;
- projection reconstruível;
- uninstall não destrutivo por default.

### G-590 — RC
- mesmo artefato testado;
- manifest/checksum;
- regressão;
- PR review/merge.

## 17. Aceite

SPEC-005 só pode fechar quando:
- Golden Suite ativa e não vazia;
- zero blocking failure;
- ranking determinístico;
- WordPress continua autoridade de visibilidade;
- Search funciona sem IA/vetor;
- fallback é honesto;
- nenhuma regressão SPEC-001–004;
- Visual Contract cumprido;
- evidência ambiental registrada.

## 18. Rollback

Até G-590:
- feature flag/build flag permite desligar o novo Search sem afetar Workspace;
- nenhuma projection é fonte da verdade;
- desligar módulo retorna ao comportamento anterior;
- índices derivados podem ser descartados/reconstruídos;
- nenhum dado editorial é removido.

## 19. Fora de escopo

- telemetria detalhada — SPEC-006;
- queue/indexing operacional avançado — SPEC-007;
- semantic/vector/hybrid — SPEC-008;
- Foundry/RAG — SPEC-009+;
- AUTH-UX-001;
- remoção Elementor;
- migração editorial em massa.

## 20. Definition of Ready

Implementação de runtime começa somente quando R-500 e R-510 estiverem PASS e G-520 estiver fechado.

Até lá, trabalho autorizado é inventário, benchmark, contrato, dataset, testes/fixtures e diagnóstico read-only.


## 21. Relação com ASI

O ASI 4.6.8 é tratado como baseline funcional forte. A SPEC-005 deve preservar ou superar os comportamentos comprovados de query understanding, retrieval lexical, ranking explicável, fallback e Golden Queries.

Contrato: `asi-quality-parity-contract-v1.md`.

Simplificação arquitetural não é autorização para regressão funcional. “Melhor” exige evidência de qualidade, cobertura, performance, explicabilidade, segurança ou operação — não apenas menos código.


## 22. Independência total do ASI

ADR canônico: `adr-005-001-zero-runtime-dependency-asi.md`.

Regras:
- nenhuma dependência runtime do plugin ASI;
- nenhuma leitura `asi_*` após discovery;
- nenhuma option `asi4_*`;
- nenhuma classe/função/hook ASI;
- nenhuma tabela, índice, ranking, embedding ou vector store do ASI será reutilizado como dependência;
- fixtures históricas podem preservar provenance `legacy_asi`, mas são cópias versionadas do novo projeto;
- tabelas próprias são autorizáveis em G-520 quando justificadas por retrieval/performance/governança;
- futuras camadas de embeddings/vector/hybrid deverão ter storage/lifecycle próprios;
- antes do RC haverá G-585 ASI Independence / Decommission Readiness.

O ASI poderá ser removido materialmente sem afetar o novo plugin.


## 23. Golden/Challenge separation

ADR-005-002 é canônico para R-510:
- Golden humana/histórica protege relevância;
- Technical Challenge prova cobertura técnica de linguagem natural, Summary e Elementor/Content Extractor;
- typo/alias reais são `PENDING_TELEMETRY` até a futura camada de Telemetria;
- ambiguidade objetiva é quarentenada, não resolvida por ranking nem por escolha manual obrigatória.
