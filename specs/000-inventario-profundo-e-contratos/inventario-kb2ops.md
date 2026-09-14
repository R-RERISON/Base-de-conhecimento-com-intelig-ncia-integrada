# Inventário Profundo — KB2Ops Operational Knowledge Engine 0.2.1

> SPEC-000 — baseline fixada em `R-RERISON/KB2Ops-Operational-Knowledge-Engine@f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.
>
> Este documento registra comportamentos, contratos, riscos e classificação preliminar. Não autoriza copiar o runtime nem iniciar a SPEC-001.

## 1. Conclusão executiva

O KB2Ops 0.2.1 é a referência mais forte para **produto, UX, curadoria e extração Elementor-aware**. O runtime é WordPress-first: não cria tabela nova, não registra REST próprio, não usa AJAX e não mantém cron ativo. O domínio corrente usa posts, postmeta, options, capabilities, nonces, admin-post, shortcodes e server rendering.

O principal valor arquitetural confirmado é o `Content_Extractor`: ele trata WordPress/Elementor como fonte editorial read-only e constrói uma representação textual derivada sem escrever em `_elementor_data` ou `post_content`. Esse contrato deve substituir os múltiplos pipelines de leitura direta de `post_content` observados no ASI.

O Knowledge Studio também prova um fluxo útil para o produto futuro: revisão humana, classificação, checklist, pré-análise local, estado `AI READY`, histórico e decisão explícita antes de IA. O Design System fornece a melhor referência visual das três bases: wp-admin como shell, PHP server-rendered, CSS namespaced, tokens/componentes compartilhados, JS mínimo e funcionamento completo sem IA externa.

Ao mesmo tempo, o inventário encontrou dívidas que **não devem ser herdadas**:

1. a busca atual usa scans amplos, `meta_query LIKE`, inclusive `_elementor_data`, e score simplificado; é deliberadamente provisória;
2. o extractor determinístico pode retornar conteúdo parcial e, por não estar vazio, impedir o fallback renderizado — widgets customizados podem ficar silenciosamente ausentes;
3. `Knowledge::save_review()` não confirma retorno de todos os writes antes de emitir `kb2ops_post_approved`;
4. `_kb2ops_review_history` é persistido como array, mas não é registrado pelo mesmo Meta Contract dos demais campos;
5. Analytics guarda termos de busca normalizados em uma única option, até 500 termos, sem política explícita de retenção/minimização;
6. vários dashboards/relatórios escaneiam todos os posts, aceitável para o corpus atual, mas não um contrato de escala;
7. o relatório de release afirma ampla cobertura automatizada, porém a árvore `main` fixada não contém uma suíte `tests/` executável equivalente — a evidência precisa virar regressão versionada no novo projeto;
8. a documentação arquitetural de `AI READY` omite o gate `include_ai`, embora o runtime o exija.

**Direção:** MANTER os contratos de produto e WordPress-first, MANTER/REDESENHAR o Content Extractor como serviço canônico, REDESENHAR busca/analytics/workloads, DESCARTAR complexidade histórica de migração e EVOLUIR IA/vetor somente depois do baseline lexical e de curadoria estar comprovado.

## 2. Baseline e árvore de runtime

- Repositório: `R-RERISON/KB2Ops-Operational-Knowledge-Engine`.
- Versão: `0.2.1` hardened.
- SHA `main`: `f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.
- WordPress mínimo: 6.6.
- PHP mínimo: 8.1.
- Plugin fonte: `plugin/kb2ops/`.
- Bootstrap: `kb2ops.php`.
- Runtime PHP:
  - `class-installer.php`;
  - `class-settings.php`;
  - `class-analytics.php`;
  - `class-content-extractor.php`;
  - `class-knowledge.php`;
  - `class-ui.php`;
  - `class-studio.php`;
  - `class-search.php`;
  - `class-reports.php`;
  - `class-migration.php`;
  - `class-plugin.php`;
  - `functions-compat.php`.
- Assets:
  - `design-system.css`;
  - `admin.css`;
  - `public.css`;
  - `admin.js`.
- `uninstall.php` presente.
- Builder determinístico: `tools/build_plugin.py`.

Não há tabela própria do runtime 0.2.1, diretório `tests/` versionado, REST route, AJAX endpoint ou cron ativo identificado na baseline.

## 3. Bootstrap e lifecycle — T010

`kb2ops.php`:

1. valida `ABSPATH`;
2. fixa constantes de versão/path/url;
3. carrega compat + classes do runtime;
4. registra `Installer::activate()` em activation hook;
5. chama `Plugin::register()`.

`Plugin::register()` agenda `Plugin::boot()` em `plugins_loaded`. No boot:

- `Installer::maybe_upgrade()` em `admin_init`;
- `Knowledge::register_meta()` em `init`;
- registra Settings, Migration, Studio e Search;
- emite `kb2ops_loaded`.

### Activation/upgrade

A ativação é hardened para replacement/upgrade:

- suporta network activation em multisite;
- inicializa `kb2ops_options` apenas se ausente;
- grava `kb2ops_runtime_version`;
- executa uma única vez a retirada segura do runtime legado;
- `maybe_upgrade()` cobre cenários de replace/silent update em que activation hook pode não ocorrer.

### Classificação

- **MANTER:** activation/upgrade leves, idempotentes e reversíveis.
- **MANTER:** não fazer purge destrutivo na ativação.
- **REDESENHAR:** mecanismo de upgrade do plugin unificado conforme o schema real que vier a existir.
- **DESCARTAR:** listas hardcoded de crons/capabilities/tabelas do KB2Ops legado no greenfield.
- **AINDA NÃO SABEMOS:** necessidade futura de deactivation hook; o baseline KB2Ops não registra um.

## 4. Persistência e ownership — T011/T012

### 4.1 Metadados KB2Ops

O domínio de curadoria usa:

| Meta key | Papel | Tipo/runtime | Classificação preliminar |
|---|---|---|---|
| `_kb2ops_review_state` | estado de revisão | string | MANTER semântica |
| `_kb2ops_knowledge_type` | tipo de conhecimento | string | MANTER valor; candidato forte a taxonomia |
| `_kb2ops_technologies` | tecnologias/contexto | string livre | MANTER valor; REDESENHAR storage |
| `_kb2ops_review_notes` | notas humanas | texto | MANTER como metadata |
| `_kb2ops_reviewed_at` | timestamp de revisão | string UTC/mysql | MANTER semântica |
| `_kb2ops_reviewed_by` | usuário revisor | integer | MANTER semântica |
| `_kb2ops_target_audience` | público | string | sobrepõe GRE; cruzar ownership |
| `_kb2ops_service` | serviço | string | sobrepõe GRE; cruzar ownership |
| `_kb2ops_keywords` | palavras-chave | string | MANTER valor; storage a decidir |
| `_kb2ops_versions` | versões | string | MANTER valor; storage a decidir |
| `_kb2ops_include_ai` | decisão explícita de inclusão | boolean | MANTER intenção |
| `_kb2ops_review_history` | histórico bounded | array, máx. 50 | REDESENHAR/registrar contrato |
| `_kb2ops_view_count` | contagem de visualizações | integer implícito | REDESENHAR analytics |

`register_meta()` registra nove strings + `reviewed_by` integer + `include_ai` boolean. `_kb2ops_review_history` e `_kb2ops_view_count` são usados/persistidos, mas não passam pelo mesmo registro explícito.

**Risco:** o contrato de metadata não descreve integralmente tudo que o runtime persiste.

### 4.2 Resumo Executivo — bridge read-only

`Summary_Bridge` duplica o mapa exato das oito `_bdc_es_*` e faz `get_post_meta()` diretamente. Ele:

- não depende da classe/plugin GRE estar carregado;
- não escreve as metas;
- calcula `filled/total/complete/missing`;
- alimenta `AI READY`, busca, cards e checklist.

Comparado ao GRE 0.6.0:

- as oito chaves coincidem;
- não usa `Summary_Store`;
- não conhece Meta Contract runtime;
- não recebe/gera evento de alteração;
- replica labels/chaves em outro bounded context.

**Direção futura:** DESCARTAR a bridge entre módulos no plugin unificado. Usar um único Summary Store/Meta Contract interno e projections consumidoras. Preservar compatibilidade de leitura das oito keys na migração/coexistência.

### 4.3 Options ativas

- `kb2ops_options`: settings compactos do produto;
- `kb2ops_search_analytics`: mapa bounded de termos de busca;
- `kb2ops_runtime_version`: versão instalada;
- `kb2ops_legacy_cleanup_v1`: evidência de retirada do runtime antigo;
- `kb2ops_migration_verification`: última verificação manual;
- `kb2ops_legacy_file_cleanup_warning`: aviso de cópia antiga;
- `kb2ops_legacy_purge_report`: evidência de purge explícito.

Options antigas preservadas apenas para migração: `kb2ops_db_version`, `kb2ops_settings`, `kb2ops_secure_secrets`.

### 4.4 Transients, cron e tabelas

- nenhum transient ativo do runtime novo foi identificado;
- nenhum cron novo é agendado;
- activation limpa dois hooks **legados**;
- não há tabela nova;
- migration apenas inventaria/preserva ou purga explicitamente tabelas antigas `kb2ops_contracts` e `kb2ops_jobs`.

### 4.5 Taxonomias

O KB2Ops não registra taxonomia própria. O checklist consulta categoria nativa de post. Tipo, tecnologia, serviço, audiência, keywords e versões permanecem texto/postmeta no baseline.

**Leitura para T050–T059:** uso recorrente em filtro, agrupamento e analytics transforma `knowledge_type`, tecnologias, serviço e audiência em candidatos fortes a taxonomias; isso ainda não é decisão final.

## 5. Content Extractor Elementor-aware — T015

Este é o contrato técnico mais valioso do KB2Ops.

### `html(post_id)`

1. cache estático por request;
2. valida `post`;
3. detecta Elementor pela existência de `_elementor_data`;
4. **primeiro** tenta interpretar o JSON serializado com allowlist de campos sem executar widgets arbitrários;
5. se a extração determinística resultar vazia, tenta `Elementor\Plugin::$instance->frontend->get_builder_content_for_display()`;
6. captura `Throwable` e emite `kb2ops_content_extractor_error` sem derrubar admin/busca;
7. se ainda vazio, usa `post_content`;
8. expande apenas shortcodes de tabela allowlisted;
9. não persiste alteração alguma.

### Allowlist Elementor

Campos textuais conhecidos incluem `editor`, `title`, `description`, `text`, `content`, `html`, `caption`, alertas, tabs, accordion/toggle, `button_text` e `shortcode`.

### `text(post_id)`

- preserva boundaries de `br`, parágrafos, listas, headings, rows/cells e divs antes de remover markup;
- `wp_strip_all_tags` + decode de entidades;
- normaliza whitespace/linhas;
- mantém parágrafos básicos;
- cache estático por request.

### `structure(post_id)`

Conta imagens, tabelas, headings e shortcodes em `post_content` e `_elementor_data`, usando o maior valor seguro em vez de somar duplicações. Reconhece tipos de widget (`heading`, image/gallery/media-carousel, table) e referências a shortcodes.

### Shortcodes seguros

Somente `table` e `tablepress` são executados. Se indisponíveis/falharem, a referência vira placeholder textual e o erro é sinalizado por hook.

### Contratos a preservar

- **MANTER:** read-only editorial.
- **MANTER:** representação única para Search/curadoria/IA.
- **MANTER:** serializado determinístico antes de renderização pesada.
- **MANTER:** fallback seguro e falha não fatal de widget.
- **MANTER:** allowlist de shortcode, nunca `do_shortcode()` irrestrito em todo o documento.
- **REDESENHAR:** extractor como serviço interno versionado, com corpus de regressão Elementor.

### Gap crítico — extração parcial

O fallback renderizado só ocorre se a extração do JSON for **vazia**. Se o documento tiver um widget reconhecido e outro customizado não reconhecido, a extração pode ser não vazia porém incompleta; nesse caso o renderizado não é consultado.

**Gate futuro obrigatório:** fixtures Elementor reais/custom widgets comparando texto esperado, com detecção de cobertura insuficiente. Não aceitar omissão silenciosa de conteúdo relevante.

## 6. Knowledge domain e curadoria — T011

### Estados

- `unreviewed`;
- `in_review`;
- `approved`;
- `excluded`.

### Tipos

`procedure`, `troubleshooting`, `reference`, `policy`, `faq`, `guide`, `mixed`, além de não classificado.

### AI READY

Runtime considera AI Ready quando simultaneamente:

1. post publicado;
2. `review_state = approved`;
3. Resumo Executivo 8/8;
4. `_kb2ops_include_ai = true`.

A documentação `docs/ARCHITECTURE.md` lista apenas os três primeiros gates. Logo existe **drift interno de documentação**, e o runtime deve ser considerado autoridade da baseline.

### `save_review()`

Persiste estado/tipo/classificações/notas/include_ai/reviewer/data, acrescenta histórico bounded a 50 e emite `kb2ops_post_approved` quando ocorre transição para aprovado.

**Gap:** os retornos de `update_post_meta()` não são verificados. O evento pode ser emitido mesmo sem evidência de que todos os valores foram persistidos como esperado.

**Direção:** no plugin unificado, validar payload integralmente, persistir com semântica explicitada e emitir eventos somente depois de confirmação do estado final.

### Pré-análise local

`facts()`, `suggestions()`, `checklist()` e `scores()` produzem sinais determinísticos sem IA externa. Detectam volume, headings, tabelas/shortcodes, imagens, erros conhecidos, Resumo 8/8, classificação e revisão.

**MANTER:** baseline local de baixo custo. IA futura acrescenta sugestões/evidência; não substitui regras determinísticas nem aprovação humana.

## 7. Knowledge Studio/admin — T013

### Menus/superfícies

- `kb2ops-dashboard` — Visão Geral;
- `kb2ops-posts` — inventário/revisão;
- `kb2ops-reports` — métricas;
- `kb2ops-search` — Knowledge Search;
- `kb2ops-settings` — configurações;
- `kb2ops-migration` — migração/limpeza.

O root capability varia conforme superfícies habilitadas, mas cada página aplica sua capability real:

- `read`: busca;
- `edit_posts`: Studio/relatórios;
- `edit_post(post_id)`: revisão individual;
- `manage_options`: settings/migração/purge.

### Mutações admin

- `admin_post_kb2ops_save_review`;
- `admin_post_kb2ops_save_settings`;
- `admin_post_kb2ops_verify_migration`;
- `admin_post_kb2ops_purge_legacy_data`.

Os handlers observados exigem POST, capability e nonce. Purge exige confirmação adicional explícita.

**MANTER:** trust boundaries. **REDESENHAR:** composição das telas no bounded context unificado, sem duplicar menus por módulo.

## 8. Search/frontend — T014

### Superfícies

- shortcode `[kb2ops_search]`;
- alias `[kb2ops_portal]`;
- página admin Search;
- detalhes/resultados server-rendered por GET.

`require_login` pode bloquear portal para anônimos. O `search_scope` pode ser `published`, `approved` ou `ai_ready`, e o detalhe revalida visibilidade para impedir bypass direto por `post_id`.

### Busca corrente

`Knowledge::matching_post_ids()`:

- WordPress native search em todos os candidatos do status;
- tokeniza até 6 tokens;
- executa `meta_query LIKE` em metas KB2Ops, oito `_bdc_es_*` e `_elementor_data`;
- aceita busca direta `#ID`;
- retorna IDs únicos.

`Search::relevance()`:

- concatena título + `Content_Extractor::text()` + metadata + Resumo Executivo;
- normaliza accents/lowercase;
- calcula percentual simples pela cobertura de tokens.

**Classificação:** REDESENHAR integralmente o retrieval/ranking usando os contratos ASI. MANTER apenas UX, escopo, filtros, detail visibility, uso do extractor e fallback lexical funcional.

### Workloads problemáticos

- `numberposts = -1` na coleta de candidatos/meta;
- filtro de tecnologias varre todos os posts publicados;
- `Studio::stats()` varre todos os posts relevantes;
- relatórios por tipo/semana também varrem o corpus;
- meta LIKE em `_elementor_data` não é estratégia de escala.

O corpus documentado (~700 posts) explica a opção atual, mas não a torna arquitetura futura.

## 9. Analytics e relatórios

### Search analytics

`kb2ops_search_analytics` armazena até 500 termos normalizados, cada um com count/last. A option inteira é reordenada/reescrita a cada nova busca.

**Valor:** popular searches/gaps de conhecimento.

**Riscos:**

- query text pode conter dado sensível;
- retenção é por cardinalidade, não por tempo;
- update do mapa completo não oferece semântica robusta de concorrência;
- não existe journey/outcome/click integrity como no ASI.

**Direção:** REDESENHAR sobre telemetria mínima inspirada no ASI; não portar a option como store definitivo.

### View count

`_kb2ops_view_count` usa read + increment + update de postmeta. É suficiente como indicador aproximado, não como contador transacional.

### Relatórios

Exibem cobertura, revisões, tipos, top viewed e top searches. Os comportamentos são úteis; a implementação por scans deve ganhar bounds/benchmark.

## 10. Design System e UI — T018

### Princípios confirmados

- wp-admin permanece shell administrativo;
- server-rendered PHP é padrão;
- CSS sob `.kb2ops-ui`;
- JS somente quando navegação/formulários nativos não bastarem;
- sem framework CSS externo, webfont externa ou SPA;
- Dashicons para iconografia administrativa;
- foco visível e texto/ícone além de cor;
- progressive disclosure com tabs/details;
- Knowledge Search não imita chat;
- análise automática não altera conteúdo;
- todas as telas funcionam sem IA externa.

### Tokens

`design-system.css` centraliza:

- cores semânticas;
- typography stack nativa;
- spacing baseado em 4px;
- radius 10/16/22;
- sombras leves;
- surfaces/borders/canvas;
- breakpoints 1100/720 e específicos de Search.

### Componentes

`hero`, `metric`, `card`, `badge`, `progress`, `ring`, `searchfield`, `segmented`, `table`, `tabs`, `fieldgrid`, `scoregrid`, `checklist`, `formgrid`, `toggle`, `notice`, `empty`, `results`, `resolution`, `accordion`, `bar-chart`, `donut`, `timeline`.

### Assets específicos

- `admin.css`: Search/results, detail, reporting/chart layouts;
- `public.css`: mesma linguagem visual namespaced para shortcode;
- `admin.js`: apenas confirmação de ações via `data-kb2ops-confirm`.

**Direção:** MANTER o sistema de tokens/componentes e guardrails; REDESENHAR implementação/nomes finais sob o Design System do plugin unificado. Não copiar CSS tela a tela.

## 11. Installer, migration e uninstall — T016

### Retirada do runtime legado

Activation:

- limpa apenas crons antigos conhecidos;
- remove capabilities antigas;
- preserva options/tabelas históricas;
- remove transients antigos por prefixo;
- desativa uma segunda cópia executável do plugin, mas não apaga arquivos;
- grava relatório da operação.

### Migração/Limpeza

A tela permite:

- verificar resíduos de forma não destrutiva;
- inventariar tabelas/options preservadas;
- executar purge apenas com `manage_options` + POST + nonce + checkbox explícito;
- emitir relatório do purge.

### Uninstall

Por default retorna sem apagar conhecimento. Purge só ocorre se `KB2OPS_PURGE_ON_UNINSTALL === true`, removendo `_kb2ops_*`/view count e options KB2Ops.

**MANTER:** não destrutivo por default e purge deliberado.

**DESCARTAR:** conhecimento específico do antigo `kb2ops_contracts/jobs`, crons/capabilities/options históricas no greenfield.

## 12. Testes, build e release — T017

### Evidência de gate

`docs/audit/RELEASE-GATE-0.2.1.md` declara:

- lint PHP;
- `node --check`;
- CSS balanceado/namespaced;
- smoke 23/23;
- migration/upgrade/purge 24/24;
- static/security 79/79;
- UI contract 11/11;
- mutable actions aprovadas;
- 19/19 blobs auditados;
- gate estático final 16/16;
- ZIP 19 arquivos;
- lint pós-extração.

### Limitação de rastreabilidade

A baseline `main` não contém uma pasta `tests/` nem scripts de gate equivalentes que permitam reproduzir esses totais diretamente. O relatório é evidência histórica, não uma suíte executável versionada.

**Direção obrigatória no novo produto:** todo contrato crítico do KB2Ops deverá virar teste versionado e executável.

### Build

`tools/build_plugin.py`:

- valida 19 runtime files;
- valida versão no header/constant;
- executa PHP lint quando disponível;
- empacota em raiz única instalável;
- renomeia main plugin file no pacote;
- usa timestamp fixo e ordenação determinística;
- rejeita docs/tests/specs/tools/old no ZIP;
- valida file count;
- calcula SHA-256.

**MANTER:** build determinístico, package allowlist e SHA.

## 13. Classificação geral — T019

| Componente | Direção | Motivo |
|---|---|---|
| Posts/Elementor como fonte editorial | MANTER | ownership correto |
| Content Extractor read-only | MANTER/REDESENHAR | serviço canônico futuro |
| fallback Elementor renderizado | MANTER com limites | robustez para widgets incomuns |
| allowlist de shortcodes de tabela | MANTER | reduz side effects |
| Knowledge Studio workflow | MANTER/REDESENHAR | forte contrato de produto |
| review states | MANTER | governança simples |
| knowledge type | MANTER; storage a decidir | classificação compartilhada |
| technologies/service/audience/keywords/versions em string meta | REDESENHAR | filtros/relacionamento sugerem normalização |
| review notes/reviewer/reviewed_at | MANTER via WP | metadata natural |
| review history array em postmeta | REDESENHAR | contrato/histórico precisam formalização |
| AI include flag | MANTER intenção | decisão humana explícita |
| AI READY | MANTER/REDESENHAR | gate útil, contrato deve ser único |
| pré-análise local | MANTER | custo zero e explicável |
| Summary_Bridge duplicada | DESCARTAR | bounded context unificado |
| Search `WP_Query` + meta LIKE | REDESENHAR | provisória/performance/relevância |
| score de cobertura de tokens | DESCARTAR como ranker final | insuficiente; substituir ASI-inspired |
| filtros/escopo/detail visibility | MANTER | bom contrato UX/segurança |
| search analytics option | REDESENHAR | privacidade/concurrency/retention |
| view count postmeta | REDESENHAR | métrica aproximada |
| reports/metrics | MANTER comportamento | queries devem ganhar bounds |
| Design System/tokens/componentes | MANTER/REDESENHAR | principal referência visual |
| SPA/framework externo | DESCARTAR | sem necessidade comprovada |
| admin.js mínimo | MANTER princípio | progressive enhancement |
| activation reversível | MANTER princípio | segurança operacional |
| legacy cleanup específico | DESCARTAR | dívida histórica |
| uninstall não destrutivo | MANTER | rollback/retention |
| build determinístico | MANTER | supply/release traceability |
| relatório de gate sem suíte versionada | REDESENHAR | precisa regressão reproduzível |
| Foundry/semantic index | EVOLUIR COM IA/VETOR | opcional, posterior ao baseline |

## 14. Drifts e sobreposições reveladas

### D-003 — ASI `post_content` versus Elementor

**Direção de resolução confirmada:** usar Content Extractor canônico Elementor-aware. O problema está arquiteturalmente entendido; a implementação final ainda será definida em SPEC futura.

### D-004 — múltiplos extractors/pipelines

KB2Ops prova que Search e curadoria podem compartilhar o mesmo extractor. Word Cloud, Item Knowledge, index, RAG e auditoria futura devem consumir o mesmo serviço, não reparsear conteúdo independentemente.

### D-006 — campos classificatórios duplicados

Há sobreposição clara:

- GRE `target_audience` ↔ KB2Ops `_kb2ops_target_audience`;
- GRE `affected_service` ↔ KB2Ops `_kb2ops_service`;
- GRE `systems_involved` ↔ KB2Ops `_kb2ops_technologies` parcialmente;
- KB2Ops `knowledge_type/keywords/versions` não possuem equivalente GRE direto.

O runtime KB2Ops usa esses valores em filtros/agrupamentos. Isso fortalece candidatos a taxonomia, mas ownership/storage final será fechado em T052/T056.

### D-007 — UI fragmentada

KB2Ops oferece o Design System mais coerente. A direção está confirmada: ASI/GRE não devem manter shells visuais independentes no produto unificado. A matriz de sobreposição T053 fechará quais superfícies permanecem.

### D-008 — AI READY docs versus runtime

Documentação KB2Ops descreve publish + approved + Resumo 8/8; runtime também exige `_kb2ops_include_ai`. Runtime é autoridade da baseline. O novo contrato deve ser único e testado.

## 15. Itens que permanecem abertos para T050–T059

- ownership final de audiência/serviço/sistemas/tecnologias;
- taxonomias versus postmeta campo a campo;
- estratégia de histórico de revisão;
- schema lexical e item index;
- necessidade concreta de fila durável;
- retention/minimização de query text;
- analytics gerencial necessário ao produto;
- coexistência/migração dos `_kb2ops_*`, `_bdc_es_*` e `asi_*`;
- shortcodes de compatibilidade no cutover;
- estratégia final de anchors/deep links;
- requisitos de performance para corpus real;
- MariaDB Vector/chunks/embeddings;
- contrato Foundry/IA.

## 16. Gate do bloco KB2Ops

T010–T019 podem ser encerradas porque:

- baseline/HEAD foram confirmados;
- bootstrap e runtime foram lidos;
- persistência/hooks/superfícies foram mapeados;
- Content Extractor foi decomposto tecnicamente;
- bridge GRE foi comparada à baseline GRE 0.6.0;
- Knowledge Studio/Search/Analytics/Reports foram classificados;
- Design System foi decomposto em princípios/tokens/componentes/assets;
- installer/migration/uninstall foram lidos;
- build/release e a lacuna de testes versionados foram registrados;
- riscos e drifts novos foram formalizados;
- nenhum runtime do novo produto foi criado.

O próximo bloco autorizado é **T050–T059 — cruzamento das três referências**, ainda dentro da SPEC-000.