# Matriz de IA, Vetores e Provedores — SPEC-000 — T058

> Estado: **T058 concluída documentalmente**.  
> Baseline de entrada: `main @ 6802d7bb75297dc8f3e403b57113c73170c78061`.  
> Baselines de referência: ASI `4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1`, GRE `0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365`, KB2Ops `0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.
>
> Este documento decide **onde IA/vetor compra valor suficiente para merecer existir**. Não integra Microsoft Foundry, não cria prompt registry de runtime, chunks, embeddings, vector store, tabela, endpoint, agente ou chamada externa. T097 continua sendo o único gate que pode autorizar SPEC-001.

## 1. Princípio de decisão

A pergunta obrigatória de T058 é:

> **Qual resultado de produto ficaria materialmente pior sem IA/vetor, e qual é a menor capacidade probabilística que melhora esse resultado sem virar owner do domínio?**

A ordem obrigatória é:

1. regra determinística / dado canônico;
2. retrieval lexical e Search Knowledge governado;
3. IA assistiva sob demanda;
4. síntese sobre retrieval confiável;
5. embeddings / semantic retrieval / reranking, somente após evidência de ganho;
6. agentes/ferramentas, somente se houver jornada multi-etapa que realmente exija autonomia controlada.

Complexidade não é considerada benefício por si só.

---

## 2. Invariantes de IA

1. WordPress/Elementor continuam fonte editorial.
2. IA nunca é owner de Summary, Classificação, Review, Editorial ou Search Knowledge.
3. `IA sugere -> humano revisa -> humano decide -> owner canônico persiste`.
4. Retrieval precede síntese.
5. Search lexical funciona sem IA, embeddings ou vetor.
6. Falha, timeout, quota ou indisponibilidade do provider não derrubam gestão de conhecimento nem Search lexical.
7. Conteúdo bruto Elementor, JSON estrutural e shortcodes de apresentação nunca são enviados diretamente para chunking/embedding como corpus canônico; passam pelo Content Extractor.
8. Toda operação de IA possui propósito, limite, rastreabilidade e custo observável.
9. Processamento em massa sem estimativa, budget e limite explícitos é NO-GO.
10. `content_hash`/`chunk_hash`, versão de extractor, prompt/modelo/configuração e equivalentes evitam retrabalho por `NO_CHANGE`.
11. Provider externo é adapter; o domínio não depende do SDK de um provider.
12. Nenhum failover silencioso pode trocar provider, região/destino de dados ou perfil de custo sem política explícita.

---

## 3. Prioridades

### P0 — Baseline determinístico

Obrigatório antes de IA:

- Content Extractor canônico;
- Summary/Classificação/Review canônicos;
- pré-análise local determinística;
- Search lexical + projection + ranking explicável;
- Golden Queries;
- scope/capability revalidado no WordPress.

**Decisão:** MANTER. IA não substitui P0.

### P1 — IA assistiva sob demanda

Primeira faixa aprovada para evolução futura:

- **Assistente de Classificação**;
- **Assistente de Resumo Executivo**.

Somente uma dessas jornadas deve nascer no primeiro slice de IA. Não implementar ambas simultaneamente sem razão de produto.

### P2 — RAG/síntese opcional sobre retrieval confiável

Aprovado como evolução futura, mas não requisito do primeiro runtime nem do primeiro slice de IA.

Pode inicialmente usar retrieval lexical; vetor não é pré-requisito.

### P3 — Embeddings, semantic/hybrid retrieval e reranking

**POSTERGADO COM GATE.** Só reabrir após baseline lexical medido e Golden demonstrarem lacuna que a capacidade semântica pretende resolver.

### P4 — Agentes/ferramentas

**POSTERGADO/NEGADO NO BASELINE.** Só reabrir quando existir jornada multi-etapa concreta que não seja resolvida com handlers determinísticos e IA assistiva simples.

---

# 4. Candidato AI-001 — Pré-análise local determinística

**Decisão:** `MANTER_DETERMINISTICO` — P0.

A baseline KB2Ops já prova valor em fatos, checklist, sinais e scores locais sem dependência externa.

Contratos:

- baixo custo;
- execução previsível;
- explicável;
- disponível sem rede/provider;
- alimenta contexto da IA quando útil;
- não é substituída por LLM.

**Regressão:** introduzir LLM para recalcular regra determinística já suficiente é aumento de custo/superfície de falha sem benefício comprovado.

---

# 5. Candidato AI-010 — Assistente de Classificação

**Decisão:** `APROVADO_OPCIONAL` — P1.

## 5.1 Valor

Pode reduzir trabalho manual para sugerir:

- audiência;
- tipo de conhecimento;
- serviço/serviço afetado sem colapsar conceitos distintos;
- tecnologias/sistemas sem colapsar conceitos distintos;
- keywords/versions;
- outros campos somente após owner/primitive estarem definidos.

## 5.2 Princípio de negação

Antes da IA:

- regras determinísticas e valores já presentes devem ser aproveitados;
- vocabulário/taxonomias governados limitam o espaço de sugestão;
- valores óbvios não devem consumir LLM.

## 5.3 Contrato futuro

A chamada deve ser explícita pelo usuário autorizado e receber somente contexto necessário:

- texto do Content Extractor canônico;
- dados canônicos necessários;
- vocabulário/termos governados aplicáveis;
- fatos locais determinísticos úteis.

A resposta deve ser estruturada e validada antes da UI. Cada sugestão deve permitir representar:

- campo/conceito alvo;
- valor ou valores propostos;
- evidência textual/fonte;
- confiança/incerteza;
- observação/racional curto;
- eventual `candidate_new_term`, sem persistência automática.

## 5.4 Autoridade humana

- IA não chama diretamente writers canônicos;
- `Gerar sugestões` e `Aplicar` são ações distintas;
- Apply exige capability/nonce e passa pelo owner canônico;
- read-after-write confirma persistência;
- erro/timeout da IA não muda estado do post.

## 5.5 `AI_READY` versus assistência editorial

`AI_READY = publish + approved + 8/8 + include_ai` continua sendo regra de elegibilidade do conteúdo para consumo downstream de IA quando esse gate estiver ativo.

Não reutilizar `include_ai` para autorizar ajuda de autoria.

Se a assistência em draft/in_review for necessária, usar conceito separado, por exemplo **AI Assist Allowed**, definido pela futura SPEC com capability e política de envio de dados. Isso não altera elegibilidade do corpus produtivo.

---

# 6. Candidato AI-020 — Assistente de Resumo Executivo

**Decisão:** `APROVADO_OPCIONAL` — P1.

## 6.1 Escopo

Pode sugerir exclusivamente campos pertencentes ao owner Summary:

- `objective`;
- `escalation`;
- `important`.

`post_title` continua canônico no WordPress e não é recriado em meta.

## 6.2 Contratos

- usa Content Extractor/fatos canônicos;
- proposta precisa trazer evidência rastreável;
- saída pode declarar `evidencia_insuficiente`/abstenção;
- não completa lacunas inventando fatos;
- sugestão não é persistência;
- Apply humano usa o Summary Store/handler canônico e G-020/B-006;
- provider indisponível não impede edição manual.

## 6.3 Não aprovado

- geração automática na publicação;
- sobrescrever Summary existente sem revisão humana;
- preencher 8/8 em lote para “aumentar AI READY”;
- tratar confiança do modelo como aprovação editorial.

---

# 7. Candidato AI-030 — LLM em toda consulta de Search

**Decisão:** `DESCARTAR_BASELINE`.

Não há evidência de que cada consulta precise de LLM para intenção, expansão ou reescrita.

Baseline:

- QueryContext determinístico;
- vocabulário/equivalências governadas;
- retrieval lexical;
- fallback WordPress.

Razões da rejeição:

- latência;
- custo por query;
- quota/rate-limit;
- dependência externa no caminho crítico;
- perda de determinismo/explicabilidade.

Reabrir apenas se dataset/Golden demonstrarem classe de consultas que não pode ser resolvida adequadamente pelo baseline e um experimento controlado comprovar ganho.

---

# 8. Candidato AI-040 — RAG e síntese de resposta

**Decisão:** `APROVADO_OPCIONAL_POSTERIOR` — P2.

## 8.1 Ordem obrigatória

`query -> retrieval confiável -> evidências -> síntese opcional -> fontes`

A síntese nunca substitui a lista/evidência recuperada como fonte verificável.

## 8.2 Retrieval

A primeira versão de RAG pode usar apenas retrieval lexical. Embeddings/vector não são dependência arquitetural de RAG.

Antes de produção:

- B-001 fechado;
- G-010/G-050/G-060/G-070 aprovados;
- Golden corrente;
- corpus de IA explicitamente elegível.

## 8.3 Corpus produtivo

Quando o gate de confiança estiver ativo, o corpus de síntese produtiva deve respeitar a regra AI READY vigente:

`publish + approved + Summary 8/8 + include_ai`.

A exposição final ainda revalida scope/capability/status no WordPress.

## 8.4 Grounding

Resposta futura deve:

- carregar IDs/refs das evidências usadas;
- permitir mostrar fontes ao usuário;
- não afirmar suporte inexistente;
- abster-se quando retrieval for insuficiente;
- distinguir “não encontrei evidência suficiente” de erro técnico.

Conteúdo recuperado é **dados**, não instrução confiável. Prompt injection contida no artigo não autoriza alterar system prompt, chamar ferramentas ou executar ações.

## 8.5 Degradação

Timeout/quota/provider indisponível -> devolver retrieval/resultados/fontes sem síntese, quando o fluxo permitir. O core não falha.

---

# 9. Candidato AI-050 — Chunking

**Decisão:** `POSTERGADO_CONDICIONAL` — componente de P3/P2, não domínio próprio.

Chunking não cria um segundo parser.

Prioridade:

1. usar documentos estruturais `post|item` já derivados pelo Content Extractor/Item Knowledge como unidades de evidência;
2. só criar subchunks se limite de contexto/embedding ou avaliação provar necessidade.

Cada chunk futuro deve possuir linhagem suficiente:

- origem `document_key`/post/item;
- `source_hash`;
- `chunk_hash`;
- versão do extractor;
- versão do contrato de chunking;
- ordem/range estrutural quando aplicável.

Chunks são projection reconstruível, nunca fonte da verdade.

Não adotar defaults de chunking de provider como contrato do produto.

---

# 10. Candidato AI-060 — Embeddings

**Decisão:** `POSTERGADO_COM_GATE` — P3.

## 10.1 Condição de reabertura

Embeddings só ganham direito de existir quando:

1. baseline lexical estiver operacional/medido;
2. existirem Golden/casos semânticos representativos onde o lexical tenha lacuna conhecida;
3. houver hipótese mensurável de ganho;
4. provider/model/dimensão/storage/custo/fallback estiverem definidos;
5. corpus e data egress estiverem aprovados.

## 10.2 Fonte

Somente Content Extractor/documentos canônicos derivados. Nunca `_elementor_data` bruto, JSON de apresentação ou HTML irrestrito.

Por padrão, embeddings produtivos para IA devem respeitar elegibilidade do corpus de IA configurada.

## 10.3 Fingerprint/NO_CHANGE

Um embedding deve ser incompatibilizado quando mudar qualquer parte material. Fingerprint conceitual:

`document_key + source_hash + extractor_version + chunk_contract_version + provider + embedding_model/deployment + dimensions/config`

Mudança de modelo/dimensão/configuração não reutiliza embedding incompatível.

Re-embed total sem diff, estimativa e budget é NO-GO.

## 10.4 Storage

T058 **não aprova tabela vetorial nem serviço vetorial**.

A SPEC que reabrir deverá comparar, no ambiente real:

- MariaDB VECTOR se suportado;
- serviço/search externo se houver requisito;
- vector store gerenciado de provider para caso isolado.

A opção de menor acoplamento/custo que atingir os critérios vence.

---

# 11. Candidato AI-070 — Semantic/Hybrid Retrieval

**Decisão:** `POSTERGADO_COM_GATE` — P3.

Se reaberto, o baseline de produção é **hybrid**, não vector-only, salvo prova explícita em contrário.

Procedimento obrigatório:

1. congelar baseline lexical e Golden;
2. executar semantic retrieval isolado no mesmo corpus;
3. comparar acertos/erros;
4. testar estratégia de fusão explícita — weighted/RRF/outra justificada;
5. medir latência/custo;
6. executar Golden com ranker híbrido versionado;
7. manter fallback lexical.

T058 não inventa percentual mínimo de uplift. A SPEC experimental futura deve fixar o critério de sucesso **antes** de rodar o experimento/GO.

Semantic search que piora casos `blocking` sem decisão explícita é NO-GO.

---

# 12. Candidato AI-080 — Reranking por modelo

**Decisão:** `POSTERGADO_COM_GATE` — P3.

Se aprovado futuramente:

- somente sobre top-K bounded recuperado antes;
- nunca LLM contra o corpus inteiro;
- timeout estrito;
- fail-open para ordering determinístico anterior;
- versão/modelo/configuração registrados;
- Golden antes/depois;
- custo/latência medidos;
- nenhuma mudança silenciosa de ranking.

---

# 13. Candidato AI-090 — Microsoft Foundry como provider

**Decisão:** `PROVIDER_PREFERENCIAL_CANDIDATO`, não arquitetura de domínio.

Microsoft Foundry pode ser o primeiro provider experimentado para o primeiro slice concreto de IA porque o projeto já prevê integração corporativa, mas:

- não criar SDK/service classes genéricas em T058;
- não acoplar domínio ao SDK Foundry;
- provider contract mínimo nasce junto com o primeiro caso de uso real;
- WordPress HTTP API é primeira opção quando tecnicamente adequada;
- prompt/model/deployment/configuration são versionados;
- timeout, retry bounded, quota e erros são explícitos;
- secrets nunca entram em logs/exports;
- troca/failover de provider não pode mudar silenciosamente destino de dados/custo;
- ausência de Foundry não derruba P0.

Preços/modelos/limites do cloud mudam; não hard-code preço em arquitetura. Estimativas devem registrar fonte/data/configuração utilizada.

---

# 14. Candidato AI-100 — Foundry Agent File Search / vector store gerenciado

**Decisão:** `DESCARTAR_COMO_CORE`; `CONDICIONAL_PARA_AGENTE_ESPECIFICO`.

A documentação atual do Microsoft Foundry Agent Service demonstra que File Search cria vector store e executa ingestão/chunking/embedding gerenciados. Isso é uma capacidade útil para um agente isolado, porém, usada como Search/RAG canônico do plugin, criaria uma **segunda pipeline de conhecimento** com identidade/chunking/freshness próprios.

Portanto:

- não é Search store canônico;
- não substitui Search Retrieval Projection;
- não recebe WordPress/Elementor bruto;
- não define o contrato de chunking do produto;
- só pode existir como projection de caso específico, derivada do corpus canônico, com lineage/hash, custo, freshness, cleanup e qualidade explícitos.

Snapshot de documentação analisada em 2026-09-14; defaults/limites do provider não são contrato do projeto.

Referências públicas:

- Microsoft Foundry Agent Service — File Search/vector stores;
- Microsoft Foundry documentation;
- Azure AI Search vector/hybrid search.

---

# 15. Candidato AI-110 — Agentes e ferramentas

**Decisão:** `POSTERGADO_NEGADO_NO_BASELINE` — P4.

Não há requisito atual que exija autonomia multi-step para o core da Base de Conhecimento.

Um agente futuro deve provar por que handlers determinísticos + IA assistiva não atendem.

Regras mínimas se reaberto:

- ferramentas read-only por default;
- allowlist explícita de tools;
- menor privilégio;
- argumentos validados server-side;
- conteúdo recuperado não é comando;
- qualquer mutação requer confirmação humana explícita e usa handler canônico com capability+nonce/autorização equivalente;
- orçamento de passos/tokens/tempo;
- auditabilidade da sequência;
- kill/timeout;
- sem credenciais expostas ao modelo;
- teste de prompt injection/tool abuse.

“Agente que atualiza conteúdo automaticamente” é proibido pelo baseline.

---

# 16. Provider contract mínimo futuro

Não criar abstração genérica antecipada. Quando o primeiro slice IA for implementado, o seam mínimo deverá expressar apenas o necessário para o caso real.

Contrato conceitual comum:

- operação/tipo;
- payload estruturado e bounded;
- timeout;
- resultado estruturado;
- usage quando disponível;
- identificador/provider/model/deployment/configuração;
- erro tipado: timeout, quota/rate-limit, auth, invalid response, provider unavailable, safety/policy, unknown;
- nenhuma persistência de domínio dentro do adapter.

Retry só para falhas classificadas como recuperáveis e quando idempotência/custo forem aceitáveis.

---

# 17. AI Operation Receipt — rastreabilidade e custo

Toda chamada externa futura deve gerar evidência operacional, mesmo quando não houver Analytics detalhado de Search.

Campos conceituais mínimos:

- `operation_type`;
- provider;
- modelo/deployment;
- `prompt_version`;
- fingerprint de parâmetros/configuração;
- objeto/post/documento relacionado quando aplicável;
- `source_hash`/input fingerprint sem copiar dado sensível desnecessário;
- volume processado;
- tokens/unidades de entrada/saída quando disponíveis;
- custo estimado e/ou real quando disponível;
- duração;
- status/error class;
- usuário ou automação responsável quando aplicável;
- timestamp.

**T058 não escolhe storage** para receipts. A futura SPEC deve tentar primitives WordPress/bounded evidence antes de propor stream/tabela. Não confundir receipt de custo com Search Analytics B-004.

Secrets, payloads completos e dados sensíveis não são requisito de log.

---

# 18. Custo, budget e operações em massa

## 18.1 Primeiros slices

Preferir chamada explícita, unitária ou pequeno lote controlado.

Nenhum AI batch inicia em activation, publicação ou page load por default.

## 18.2 Batch futuro

Antes de executar:

1. selecionar corpus/scope;
2. calcular quantos objetos realmente mudaram;
3. estimar tokens/unidades;
4. estimar custo conforme provider/configuração vigente;
5. informar budget máximo;
6. confirmar explicitamente;
7. possuir limite de itens e custo;
8. interromper ao atingir limite;
9. registrar sucesso/falhas/custo;
10. permitir reexecução idempotente/NO_CHANGE.

Se o batch necessitar worker durável, T058 não autoriza improvisação: reabrir F-057-03 e B-007.

---

# 19. NO_CHANGE e cache de sugestão

IA não deve recalcular a mesma sugestão sem razão.

Fingerprint conceitual de sugestão:

`operation_type + source_hash + relevant_domain_state_hash + prompt_version + provider/model/deployment + config_fingerprint`

Mesmo fingerprint pode permitir reutilização/NO_CHANGE conforme política da futura SPEC.

Mudança material invalida a evidência anterior.

Cache/sugestão derivada nunca substitui dados canônicos.

---

# 20. Segurança, privacidade e data egress

## 20.1 Configuração

Integração externa de IA permanece desabilitada enquanto não houver configuração/credencial/política de envio de dados válidas.

A futura SPEC deve explicitar:

- quais campos/conteúdo saem do WordPress;
- provider/região/deployment aplicável;
- finalidade;
- retenção/contrato quando relevante;
- roles/capabilities que podem disparar a operação;
- minimização do contexto.

## 20.2 Consultas de usuário

Uma query pode precisar ser enviada ao provider para síntese/rerank futuro, mas isso **não autoriza persistência local de query text**. B-004/G-080 continuam independentes.

## 20.3 Prompt injection

Conteúdo editorial/retrieved é tratado como dados. Não pode:

- redefinir instruções do sistema;
- solicitar secrets;
- liberar tools;
- alterar scope/capability;
- autorizar mutação.

## 20.4 Segredos

- nunca em prompt de diagnóstico/export;
- nunca em logs/receipts;
- armazenamento final depende da estratégia institucional da futura SPEC;
- erro não imprime token/key.

---

# 21. Gate G-140 — IA/Vetor

T058 substitui o placeholder de G-140 por subgates específicos.

## G-140A — Independência e degradação

**Classe:** MUST quando qualquer IA for ativada.

Provar:

- core/lexical funciona com provider desligado;
- timeout/quota/auth/provider unavailable têm estado explícito;
- falha de IA não persiste mudança canônica;
- RAG pode degradar para retrieval sem síntese;
- semantic/hybrid pode degradar para lexical.

## G-140B — Provider, rastreabilidade e data egress

**Classe:** MUST quando provider externo for ativado.

Provar:

- domínio não depende do SDK/provider;
- provider/model/deployment/prompt/config versionados;
- payload enviado é minimizado/documentado;
- secrets não aparecem em logs/export;
- timeout/retry policy bounded;
- troca/failover não é silenciosa;
- receipt de operação suficiente para diagnóstico/custo.

## G-140C — Human-in-the-loop

**Classe:** MUST para IA de curadoria.

Provar:

- gerar sugestão não persiste owner;
- sugestão estruturada é validada;
- UI mostra evidência/incerteza;
- Apply é ação humana separada;
- Apply usa handler canônico com capability/nonce/read-after-write;
- modelo não cria termo/taxonomia/meta silenciosamente.

## G-140D — Custo, budget e NO_CHANGE

**Classe:** MUST para qualquer chamada externa; reforçado em batch.

Provar:

- usage/custo observável quando disponível/estimável;
- fingerprint detecta NO_CHANGE;
- prompt/model/config change invalida cache quando material;
- batch possui preview, estimativa, budget, limite e stop condition;
- nenhuma operação massiva ocorre na activation.

## G-140E — Embedding/Semantic/Hybrid

**Classe:** POSTERGADO até reabertura formal; depois CONDICIONAL/MUST para a feature.

Antes do GO:

- baseline lexical medido;
- B-001 fechado;
- corpus/chunking derivados do extractor;
- modelo/dimensão/fingerprint explícitos;
- Golden antes/depois;
- critério de sucesso fixado antes do experimento;
- latência/custo medidos;
- fallback lexical;
- model/dimension drift tratado;
- vector store reconstruível e não-canônico.

## G-140F — RAG/Síntese

**Classe:** CONDICIONAL quando síntese existir.

Provar:

- retrieval precede geração;
- evidências/fontes retornadas;
- corpus/scope revalidado;
- resposta abstém quando insuficiente;
- prompt injection de conteúdo não ganha autoridade;
- timeout/falha devolve experiência degradada segura;
- avaliação cobre groundedness/factual support no corpus real.

## G-140G — Agentes/Tools

**Classe:** POSTERGADO até reabertura formal.

Se ativado:

- tool allowlist;
- least privilege;
- inputs server-validated;
- read-only por default;
- mutação humana confirmada e canônica;
- budget de passos/tempo/custo;
- prompt-injection/tool-abuse tests;
- kill/timeout;
- auditabilidade.

## G-140H — Provider-managed knowledge/File Search

**Classe:** POSTERGADO/CONDICIONAL.

Se um agente específico usar store gerenciado:

- não é owner nem Search canônico;
- ingestão vem do extractor/projection canônicos;
- lineage/hash/version/freshness comprovados;
- cleanup/rebuild definidos;
- custo/quota medidos;
- não cria segunda política editorial/classificatória.

---

# 22. Matriz executiva de decisão

| Capacidade | Decisão T058 | Prioridade | Primeiro runtime? |
|---|---|---:|---:|
| pré-análise determinística | MANTER | P0 | SIM |
| classificação assistida | APROVADO OPCIONAL | P1 | NÃO obrigatório |
| Summary assistido | APROVADO OPCIONAL | P1 | NÃO obrigatório |
| LLM em toda query | DESCARTAR baseline | — | NÃO |
| RAG/síntese | APROVADO OPCIONAL POSTERIOR | P2 | NÃO |
| chunking adicional | POSTERGADO condicional | P2/P3 | NÃO |
| embeddings | POSTERGADO COM GATE | P3 | NÃO |
| semantic/hybrid retrieval | POSTERGADO COM GATE | P3 | NÃO |
| model reranking | POSTERGADO COM GATE | P3 | NÃO |
| Microsoft Foundry | provider preferencial candidato, desacoplado | depende do slice | NÃO obrigatório |
| Foundry File Search | DESCARTAR como core; condicional por agente | P4 | NÃO |
| agentes/tools | POSTERGADO/NEGADO baseline | P4 | NÃO |

---

# 23. Primeiro slice de IA recomendado

Quando uma SPEC futura autorizar IA externa, escolher **uma única jornada**:

### Opção A — Assistente de Classificação

`abrir revisão -> pré-análise determinística -> solicitar sugestão IA -> revisar evidências -> selecionar/editar -> aplicar via owner -> reler estado`

### Opção B — Assistente de Summary

`abrir post -> ler Summary atual -> solicitar sugestão IA -> revisar evidências -> editar/aceitar -> aplicar via Summary owner -> reler estado`

Critério para escolher A versus B pertence à SPEC de produto/implementação; T058 não executa as duas.

A primeira implementação pode também optar por **zero IA externa** até P0 estar homologado. Isso é arquitetura válida, não lacuna.

---

# 24. Riscos novos T058

- **X-023:** LLM virar caminho crítico da Search.
- **X-024:** `include_ai` ser reutilizado indevidamente como permissão de autoria assistida.
- **X-025:** sugestão de IA persistir diretamente em owner canônico.
- **X-026:** provider SDK contaminar domínio.
- **X-027:** batch de IA sem budget/NO_CHANGE.
- **X-028:** embedding/chunk drift misturar vetores incompatíveis.
- **X-029:** hybrid search piorar casos blocking e ainda ser liberada por média global.
- **X-030:** store gerenciado/File Search virar segunda fonte/pipeline de conhecimento.
- **X-031:** síntese responder sem evidência/abstenção.
- **X-032:** prompt injection em conteúdo recuperado disparar tool/action.
- **X-033:** logs de IA armazenarem secrets ou payload desnecessário.
- **X-034:** failover silencioso mudar custo/região/provider.
- **X-035:** preços/limites atuais do provider virarem contrato arquitetural rígido.

Tratamento: G-140A–H + gates anteriores.

---

# 25. O que T058 explicitamente não autoriza

- integração Microsoft Foundry;
- SDK de provider;
- endpoint de IA;
- prompt registry de runtime;
- credencial/secrets storage final;
- chamadas LLM;
- embeddings;
- tabela/vector store;
- MariaDB VECTOR;
- Azure AI Search;
- Foundry File Search;
- chunk pipeline;
- semantic/hybrid ranker;
- reranker;
- agente/tool runtime;
- batch de IA;
- durable queue;
- Analytics detalhado.

Tudo permanece documental até T097 + SPEC própria.

---

# 26. Critério de fechamento T058

- [x] cada candidato recebeu decisão explícita;
- [x] deterministic-first preservado;
- [x] IA assistiva P1 separada de owner/persistência;
- [x] RAG separado de vetor e pode nascer lexical-first;
- [x] embeddings/semantic/rerank postergados até evidência;
- [x] agentes postergados;
- [x] Foundry definido como provider candidato, não domínio;
- [x] File Search rejeitado como core canônico;
- [x] custo/quota/timeout/degradação tratados;
- [x] provider/model/prompt/config rastreáveis;
- [x] NO_CHANGE/hash obrigatório;
- [x] data egress/secrets/prompt injection tratados;
- [x] G-140A–H definidos;
- [x] nenhum runtime/schema/vector/chamada externa criado.

## Próximo passo autorizado

**T059 — consolidar a Matriz de Paridade Futura final**, incorporando T054–T058, prioridades `primeiro runtime | posterior | postergado`, blockers/gates e preparando T090–T097.
