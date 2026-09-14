# Pesquisa Inicial — SPEC-000

## Objetivo

Registrar fatos observados e sua evolução durante a leitura exaustiva e do cruzamento. Hipóteses deixam de ser hipóteses somente quando confirmadas contra runtime ou quando a decisão documental decorre explicitamente de evidência das três baselines.

## 1. KB2Ops 0.2.1 — bloco confirmado

Baseline fixada e confirmada no `main` da referência: `R-RERISON/KB2Ops-Operational-Knowledge-Engine@f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.

### Fatos confirmados por runtime

- bootstrap pequeno, activation hook e boot em `plugins_loaded`;
- `admin_init` cobre upgrade silencioso/replacement;
- runtime não cria tabelas, REST, AJAX ou cron novo;
- persistência de curadoria usa postmeta + Options API;
- `Content_Extractor` lê Elementor de forma read-only e produz HTML/texto/estrutura derivados;
- extractor tenta primeiro `_elementor_data` determinístico, depois renderização Elementor se resultado for vazio e finalmente `post_content`;
- apenas `table/tablepress` podem ser executados como shortcode dentro do extractor;
- erros de widget/shortcode são não fatais e expostos por hooks;
- Knowledge Studio implementa review states, tipos, metadata, checklist, scores, suggestions e histórico bounded;
- `AI READY` runtime exige publish + approved + Resumo 8/8 + `include_ai`;
- `Summary_Bridge` duplica e lê diretamente as oito `_bdc_es_*`, sem escrever;
- Knowledge Search é server-rendered, com escopo `published/approved/ai_ready` e revalidação do detail route;
- busca corrente combina native search + `meta_query LIKE` + `_elementor_data` + score simples de cobertura de tokens;
- Search atual é conscientemente provisória para corpus de centenas de posts;
- analytics leve usa option de queries bounded a 500 e postmeta para view count;
- Studio/Reports executam scans amplos do corpus em algumas métricas;
- Design System usa wp-admin como shell, PHP server-rendered, CSS namespaced, tokens, Dashicons e JS mínimo;
- não há SPA/framework CSS/webfont externa;
- activation desarma legado sem apagar dados;
- purge definitivo é ação administrativa separada com nonce/capability/confirmação;
- uninstall é não destrutivo por default;
- builder gera ZIP determinístico, allowlisted e SHA-256;
- release gate 0.2.1 registra ampla auditoria, porém a baseline não contém suíte `tests/` executável capaz de reproduzir os totais documentados.

### Hipóteses KB2Ops resolvidas

- **“O Content Extractor é Elementor-aware?”** Sim. Ele lê `_elementor_data` read-only, prioriza parsing determinístico e possui fallbacks controlados.
- **“A bridge GRE escreve ou governa os oito campos?”** Não. É estritamente read-only e duplica as chaves para consumo local.
- **“KB2Ops depende do plugin GRE ativo?”** Não para leitura; as metas são consumidas diretamente se existirem.
- **“A busca KB2Ops deve ser o motor futuro?”** Não. É UX/protótipo funcional; retrieval/ranking precisa ser substituído pelos contratos maduros do ASI.
- **“O Design System merece sobreviver?”** Sim como principal referência de shell/tokens/componentes/guardrails, não como CSS copiado literalmente.
- **“O lifecycle hardened deve ser copiado?”** O princípio reversível deve sobreviver; listas de legado não.
- **“Há evidência de necessidade de tabela no KB2Ops atual?”** Não. O baseline funciona sem tabelas novas.
- **“Há candidatos claros a taxonomia?”** Sim: tipo, tecnologia, serviço e audiência são usados como classificação/filtro/agrupamento. A primitive final segue para T056.

### Decisões preliminares KB2Ops

- MANTER posts/Elementor como fonte editorial.
- MANTER/REDESENHAR um Content Extractor único e versionado.
- MANTER workflow de curadoria, review states e decisão humana `include_ai`.
- MANTER pré-análise determinística de custo local; IA acrescenta, não substitui.
- DESCARTAR `Summary_Bridge` como bridge interna futura; usar store canônico único.
- REDESENHAR busca `WP_Query/meta LIKE` com engine lexical/item inspirada no ASI.
- REDESENHAR analytics option/view count com privacy/retention/outcomes.
- MANTER Design System como referência principal de UX.
- MANTER activation/uninstall não destrutivos por default.
- DESCARTAR hardcodes de cleanup do runtime antigo.
- MANTER deterministic package/build.
- EXIGIR testes executáveis versionados para contratos atualmente documentados apenas em release audit.

### Dívidas KB2Ops confirmadas

1. `Content_Extractor` pode produzir resultado parcialmente incompleto para custom widgets sem acionar fallback renderizado, pois fallback ocorre apenas quando a saída determinística fica vazia.
2. `Knowledge::save_review()` não checa resultado de cada `update_post_meta()` antes de emitir `kb2ops_post_approved`.
3. `_kb2ops_review_history` e `_kb2ops_view_count` ficam fora do registro explícito do Meta Contract.
4. Search analytics persiste query text normalizada sem política temporal de retenção/minimização.
5. scans `numberposts=-1` e meta LIKE não devem virar contrato de escala.
6. `docs/ARCHITECTURE.md` omite `include_ai` na definição de AI READY, enquanto o runtime exige o gate.
7. release audit não é equivalente a suíte executável versionada.

## 2. ASI 4.6.8 — bloco confirmado

Baseline: `R-RERISON/Advanced-search-Intelligence@c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.

Fatos confirmados:

- 12 stores/tabelas, parte domínio e parte complexidade histórica;
- lexical/FULLTEXT + fallback;
- QueryContext e relevance explicáveis;
- post/item index, identity e anchors;
- vocabulary/bindings/rules + curadoria/simulation;
- Queue durável;
- Events/Interactions/Outcomes e privacy modes;
- Golden Queries e Quality Diagnostics;
- Word Cloud com pipeline duplicado;
- GAC/legacy acoplados em partes do runtime;
- package/release local com regressão extensa.

Decisões:

- MANTER comportamento forte de retrieval/ranking/Golden/security;
- REDESENHAR código/storage;
- DESCARTAR complexidade histórica automática;
- usar Content Extractor único em vez de pipelines `post_content`;
- IA/vetor somente como camada opcional/degradável.

## 3. Gerenciador de Resumo Executivo 0.6.0 — bloco confirmado

Baseline: `R-RERISON/Gerenciador-de-Resumo-Executivo-da-Base-de-Conhecimento@1120a534d8eb2288460c2c675730deef0d67c365`.

Fatos confirmados:

- seis classes de runtime;
- oito metas privadas via Metadata API;
- `post_title` canônico;
- Summary Store side-effect free/read-after-write;
- admin-post autenticado e nonce por post;
- Coverage Dashboard read-only, porém scan amplo;
- shortcode current-post-only e CSS-only;
- nenhuma tabela/REST/AJAX/cron/options/transients de domínio;
- testes unitários + WP real + package smoke;
- deterministic build;
- ausência de `Objective_Provider` e `bdc_es_objective_updated` esperados pelo ASI.

Decisões:

- MANTER WordPress-first e os oito valores;
- MANTER `edit_post`, allowlist, sanitização, empty-delete;
- REDESENHAR store/evento internos do bounded context unificado;
- workload e histórico permanecem temas de cruzamento.

## 4. Drifts/overlaps comprovados pelas três referências

### D-001 — Objective Provider

ASI espera classe inexistente no GRE. **QUEBRADO**.

### D-002 — Objective changed event

ASI espera evento que GRE não emite. **QUEBRADO**.

### D-003 — conteúdo ASI versus Elementor

ASI usa `post_content` em vários pipelines; KB2Ops oferece extractor Elementor-aware read-only. **DIREÇÃO FUNCIONAL RESOLVIDA EM T053:** extractor único.

### D-004 — múltiplos extractors

Word Cloud/Item Knowledge/auditoria ASI e Search KB2Ops não podem evoluir com parsers independentes. **DIREÇÃO FUNCIONAL RESOLVIDA EM T053:** downstream consome uma representação canônica derivada.

### D-005 — GAC

Dependência ambiental ASI; não aparece como requisito do KB2Ops/GRE. **DIREÇÃO:** fora do core, adapter opcional se necessário.

### D-006 — classificação duplicada GRE/KB2Ops

- audiência duplicada;
- serviço sobreposto;
- sistemas/tecnologias parcialmente sobrepostos;
- KB2Ops adiciona knowledge type/keywords/versions.

**OWNERSHIP RESOLVIDO EM T052:** Classificação de Conhecimento é owner lógico dos conceitos reutilizáveis. Audiência é um único conceito; serviço/serviço afetado e tecnologias/sistemas permanecem atributos distintos até profiling. Storage final segue aberto para T056.

### D-007 — UI fragmentada

ASI e GRE têm estilos próprios; KB2Ops possui Design System consistente. **OWNERSHIP VISUAL RESOLVIDO EM T053:** um Design System próprio do novo plugin, derivado dos princípios KB2Ops. Layout final ainda pertence às Specs de runtime.

### D-008 — AI READY documentação/runtime

Documentação KB2Ops omite `include_ai`; runtime exige. **DRIFT INTERNO CONFIRMADO**. Runtime fixado é baseline histórica para regressão.

## 5. T052 — Ownership de dados: conclusões

O mapa canônico está em `mapa-ownership-dados.md`.

Decisões de domínio:

1. `WP_Post`/Elementor continuam owner editorial absoluto.
2. Objetivo/escalonamento/importante pertencem ao Resumo Executivo.
3. Equipe, item de catálogo, audiência, serviços, sistemas, tecnologias, tipo, keywords e versões pertencem ao domínio de Classificação de Conhecimento.
4. Review state/notas/revisor/data/include AI/histórico pertencem a Revisão e Governança.
5. Content Extractor possui somente projections read-only.
6. Vocabulary/bindings/rules pertencem a Search Knowledge, não à classificação do post.
7. Post/item/vector indexes são projections de Search Indexing.
8. Golden Queries pertencem a Search Quality.
9. Events/interactions/outcomes pertencem a Analytics/Search Intelligence.
10. Migration/compat não pode virar owner permanente.
11. GAC permanece owner externo de seus próprios dados e só pode entrar via adapter opcional.
12. IA possui sugestões, nunca o dado editorial/classificatório aprovado.

### O que T052 não decidiu

- taxonomy versus postmeta;
- nomes/chaves finais;
- cardinalidade;
- histórico/revisions;
- schemas/tabelas;
- retenção;
- plano de migração.

## 6. T053 — Sobreposição funcional: conclusões

A matriz canônica está em `matriz-sobreposicoes.md`.

### Fusões funcionais aprovadas

- um único Resumo Executivo/Store;
- uma única experiência de Search;
- um único domínio de Classificação;
- um único Analytics/Search Intelligence;
- um único Design System/shell;
- uma única navegação de produto para Reports/Insights/Settings.

### Combinação de referências

- **Search motor/qualidade:** contratos ASI;
- **Content Extraction/UX/Design System:** contratos KB2Ops;
- **Summary Store/WordPress-first:** contratos GRE;
- **release futuro:** WP integration GRE + regressão/Golden ASI + package determinístico KB2Ops/GRE.

### Responsabilidades explicitamente não fundidas

- aprovação do artigo != Apply de Search Knowledge;
- qualidade de conteúdo != qualidade de Search;
- serviço != serviço afetado sem prova;
- tecnologias != sistemas envolvidos sem prova;
- categorias/tags editoriais != taxonomias sistêmicas futuras.

## 7. Riscos novos do cruzamento

- **X-007:** unificar aprovação editorial e curadoria de Search reduziria auditabilidade.
- **X-008:** um dashboard unificado pode virar workload ilimitado se cada card fizer scan integral.
- **X-009:** compartilhar Design System não autoriza acoplamento lateral entre domains.
- **X-010:** bridges/aliases temporários podem recriar arquitetura multi-plugin se não tiverem gate de remoção.

## 8. Perguntas que seguem abertas para T050–T059

1. Qual primitive WordPress para cada classificação: taxonomy ou postmeta?
2. Quais dados antigos precisam coexistir/dual-read no cutover?
3. Quais tabelas ASI realmente precisam nascer?
4. Qual schema mínimo de post/item index?
5. Queue própria é necessária no primeiro slice ou WP-Cron/invalidation simples basta?
6. Qual fato mínimo de Analytics justifica tabela relacional?
7. Como versionar/history metadata sem infraestrutura antecipada?
8. Quais shortcodes têm consumidores reais?
9. Qual estratégia final de deep-link/anchor?
10. Quais capacidades opcionais de IA/vetor entram e em qual ordem?

## 9. Decisões ainda proibidas neste ponto

- criar runtime;
- criar taxonomias definitivas antes de T056;
- criar tabela de índice/telemetria/queue antes de T057;
- chunks/MariaDB Vector/embeddings;
- Foundry/provider contract;
- migração de dados;
- UI runtime final.

## Próximo passo

Com T052/T053 concluídas, a próxima etapa permitida é **T050 + T051**: consolidar os catálogos de persistência e integrações usando o ownership e a sobreposição agora definidos. Depois: T054 -> T056 -> T057 -> T055 -> T058 -> T059.
