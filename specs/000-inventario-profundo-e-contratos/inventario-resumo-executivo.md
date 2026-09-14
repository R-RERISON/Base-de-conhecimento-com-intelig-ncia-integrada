# Inventário Profundo — Gerenciador de Resumo Executivo 0.6.0

> SPEC-000 — baseline fixada em `R-RERISON/Gerenciador-de-Resumo-Executivo-da-Base-de-Conhecimento@1120a534d8eb2288460c2c675730deef0d67c365`.
>
> Regra de leitura: este documento registra **comportamentos, contratos, lacunas e decisões preliminares**. Ele não autoriza copiar código nem iniciar runtime no novo plugin.

## 1. Conclusão executiva

O Gerenciador de Resumo Executivo (GRE) 0.6.0 é a referência mais WordPress-first das três bases analisadas até aqui. Seu domínio central usa exclusivamente primitives nativas: `register_post_meta`, `get_post_meta`, `update_post_meta`, `delete_post_meta`, posts, capabilities, nonces, `admin-post`, shortcode e hooks WordPress. O runtime não cria tabela própria, não registra REST próprio, não usa AJAX, não mantém cron, não possui options/transients de domínio e não possui pipeline paralelo de conteúdo.

Esse desenho é valioso para o novo produto porque demonstra que o Resumo Executivo não precisa de infraestrutura própria para existir. Ao mesmo tempo, o inventário confirmou um **drift real com o ASI**: o ASI 4.6.8 espera `BDC\ExecutiveSummary\Objective_Provider::read_objective()` e o evento `bdc_es_objective_updated`, mas o GRE 0.6.0 não expõe nenhum dos dois. O único evento de domínio/runtime emitido pelo bootstrap é `bdc_es_loaded`.

Principais conclusões:

1. **MANTER** o WordPress Metadata API como primeira escolha para o contrato de Resumo Executivo; nenhuma tabela própria é justificada pelo runtime observado.
2. **MANTER** título como `post_title` nativo, sem `_bdc_es_title`.
3. **MANTER** leitura side-effect free, allowlist estrita de campos, sanitização centralizada e capability `edit_post` por objeto.
4. **MANTER** o princípio de que vazio canônico remove a linha de meta em vez de persistir string vazia desnecessária.
5. **REDESENHAR** a integração por evento/provider: o plugin unificado precisa de uma API interna canônica para leitura de Objective e de evento pós-persistência confirmado para invalidação/reindexação.
6. **REDESENHAR** o Coverage Dashboard para workload limitado quando a volumetria exigir; não criar tabela agregada antes de benchmark.
7. **REDESENHAR** a apresentação visual no Design System unificado derivado do KB2Ops; o comportamento read-only e seguro deve permanecer.
8. **INVESTIGAR/ENDURECER** atomicidade de updates multi-campo: a validação é toda anterior à mutação, porém as gravações posteriores são sequenciais e não há rollback compensatório caso uma persistência posterior falhe.
9. **MANTER** gate local reproduzível, integração com WordPress real e build determinístico.

## 2. Baseline e árvore de runtime

- Repositório: `R-RERISON/Gerenciador-de-Resumo-Executivo-da-Base-de-Conhecimento`.
- Versão: `0.6.0`.
- SHA: `1120a534d8eb2288460c2c675730deef0d67c365`.
- `main` do repositório de referência aponta para esse mesmo SHA no momento do inventário.
- Plugin distribuível: `plugin/gerenciador-resumo-executivo/`.
- Bootstrap: `gerenciador-resumo-executivo.php`.
- Runtime PHP: seis classes em `includes/`:
  - `class-plugin.php`;
  - `class-meta-contract.php`;
  - `class-summary-store.php`;
  - `class-admin-page.php`;
  - `class-coverage-dashboard.php`;
  - `class-frontend-renderer.php`.
- Assets runtime: apenas dois CSS (`admin-summary.css` e `executive-summary.css`).
- Não há JavaScript de runtime no pacote observado.

## 3. Bootstrap e lifecycle

### Bootstrap

O arquivo principal:

1. declara versão e caminhos;
2. carrega exatamente as seis classes do runtime;
3. chama `Plugin::register()`.

`Plugin::register()` registra um único hook em `plugins_loaded`. No `boot()`:

- registra `Meta_Contract::register()` em `init`;
- registra superfícies administrativas;
- registra frontend/shortcode;
- emite `bdc_es_loaded`.

### Activation/deactivation/migrations

Não foram encontrados:

- `register_activation_hook`;
- `register_deactivation_hook`;
- engine de migration;
- schema upgrade próprio;
- tabela própria;
- cron próprio.

Isso é coerente com um plugin que persiste seu domínio em post meta canônico e não precisa preparar infraestrutura própria.

### Uninstall/rollback

Não existe `uninstall.php` no baseline. Na prática, a remoção do plugin não contém rotina própria para apagar as oito metas. Isso evita destruição automática, mas **não equivale a uma política explícita de retenção**. O novo produto deverá documentar a política de uninstall/retention separadamente.

**Decisão:** MANTER bootstrap simples e ausência de infraestrutura sem necessidade. REDESENHAR somente o lifecycle que o plugin unificado realmente exigir.

## 4. Meta Contract — oito campos canônicos

O `Meta_Contract` registra exatamente oito metas privadas de post:

| Campo lógico | Meta key | Natureza observada |
|---|---|---|
| `objective` | `_bdc_es_objective` | objetivo/contexto executivo |
| `responsible_team` | `_bdc_es_responsible_team` | classificação/ownership |
| `catalog_item` | `_bdc_es_catalog_item` | classificação/catalogação |
| `affected_service` | `_bdc_es_affected_service` | classificação de serviço |
| `systems_involved` | `_bdc_es_systems_involved` | sistemas relacionados |
| `target_audience` | `_bdc_es_target_audience` | público-alvo |
| `escalation` | `_bdc_es_escalation` | orientação de escalonamento |
| `important` | `_bdc_es_important` | aviso/informação crítica |

Todas são registradas para `post` como:

- `type = string`;
- `single = true`;
- `default = ''`;
- `show_in_rest = false`;
- `revisions_enabled = false`;
- sanitizer centralizado;
- autorização delegada a `user_can( user_id, 'edit_post', object_id )`.

O contrato **não define `_bdc_es_title`**. O título é sempre o `post_title` atual do WordPress.

### Classificação preliminar

- O **contrato de oito valores** é MANTER como compatibilidade/semântica conhecida.
- A decisão final campo a campo entre post meta versus taxonomia permanece aberta até cruzar KB2Ops, especialmente para campos classificatórios reutilizáveis.
- Não existe evidência para uma tabela própria de Resumo Executivo.
- `objective` e `important` têm forte aderência a metadata por post; demais campos serão reavaliados na matriz de ownership/taxonomia.

## 5. Summary Store

### Leitura

`Summary_Store::read()`:

- normaliza/valida o ID;
- exige post existente e `post_type = post`;
- lê `post_title` nativo;
- lê as oito metas;
- projeta meta inexistente como string vazia;
- não cria linhas vazias e não possui side effect.

Esse comportamento é um contrato forte a preservar.

### Escrita

`Summary_Store::update()`:

1. valida post e tipo;
2. exige `current_user_can( 'edit_post', post_id )`;
3. valida **todo** o payload antes da primeira gravação;
4. rejeita qualquer campo fora da allowlist;
5. rejeita valores não-string;
6. sanitiza todos os valores antes da fase mutante;
7. atualiza apenas campos fornecidos;
8. vazio canônico remove fisicamente a meta;
9. valor não vazio usa `update_post_meta` com slashing compatível;
10. confirma persistência por read-after-write;
11. retorna snapshot canônico.

### Propriedades positivas

- payload desconhecido falha fechado antes de write;
- tipo inválido falha fechado antes de write;
- update parcial não altera campos omitidos;
- escrever o mesmo valor é no-op bem-sucedido;
- falha de update/delete é detectada por leitura posterior;
- HTML/script é removido na sanitização;
- multiline e backslash são preservados corretamente.

### Lacuna: atomicidade multi-campo

A fase de validação é atômica do ponto de vista lógico: todos os campos são validados antes de qualquer mutação. Entretanto, persistências posteriores são sequenciais. Se o primeiro campo for confirmado e um segundo falhar, o método retorna erro sem rollback compensatório do primeiro.

**Classificação:** REDESENHAR/ENDURECER no plugin unificado. O comportamento desejado deve ser especificado antes da implementação: ou garantir compensação transacional lógica para múltiplas metas, ou assumir/documentar explicitamente semântica de best-effort por campo. Não adicionar banco próprio apenas para resolver isso.

## 6. Admin Page

Superfícies observadas:

- menu `bdc-executive-summary`;
- capability de entrada: `edit_posts`;
- mutação: `admin_post_bdc_es_save_summary` autenticado;
- não existe `admin_post_nopriv_*` equivalente;
- formulário possui nonce específico por post;
- `Summary_Store` reafirma `edit_post` no ponto de persistência.

O editor administrativo:

- edita somente as oito metas;
- deixa explícito que o título continua sob ownership do WordPress;
- não abre nem grava Elementor;
- usa textarea simples, server-side render e WordPress buttons;
- mantém erros em allowlist estável.

A listagem de gerenciamento é limitada: consulta no máximo 50 candidatos e retorna no máximo 20 editáveis, aplicando `edit_post` por objeto.

**Decisão:** MANTER trust boundary, forms server-side e ownership do título. REDESENHAR shell/UI no Design System único do novo plugin.

## 7. Coverage Dashboard

O dashboard é read-only e classifica cada post publicado como:

- `empty` — 0/8 campos;
- `partial` — 1–7/8;
- `complete` — 8/8.

Calcula:

- total publicado;
- completos/parciais/vazios;
- cobertura (algum resumo);
- conclusão completa;
- até seis prioridades recentes incompletas.

A UI respeita `edit_post` para exibir ação de edição e mantém link público de visualização quando aplicável.

### Risco de escala

`collect()` usa `get_posts` com `posts_per_page = -1` para todos os posts publicados e depois pré-carrega o cache de metadata para todos os IDs. Em bases de centenas/milhares de posts, custo de memória/latência deve ser medido.

**Decisão:** MANTER a métrica e o read-model conceitual; REDESENHAR consulta para bounded workload/paginação/agregação nativa conforme benchmark. Pelo princípio de negação, **não criar tabela de rollup agora**.

## 8. Frontend Renderer e shortcode

Contrato público:

- shortcode `[bdc_resumo_executivo]`;
- side panel automático em `wp_footer` para singular `post`;
- CSS carregado apenas em singular post;
- zero campos preenchidos = nenhuma saída;
- render read-only;
- sem JavaScript;
- todo conteúdo dinâmico escapado.

### Segurança de contexto

O shortcode deliberadamente ignora atributos. Assim, um chamador público não consegue selecionar `post_id` arbitrário. Ele usa o post da requisição corrente (`get_queried_object_id`, com fallback seguro em `get_the_ID`).

Quando o shortcode inline já renderizou, o painel lateral automático é suprimido para evitar duplicação.

### Semântica visual

O markup usa `<aside>` e `<dl>`, priorizando:

1. título nativo;
2. Objective;
3. demais fatos;
4. `important` em destaque.

**Decisão:** MANTER read-only, current-post-only, fail-silent quando vazio e semântica acessível. REDESENHAR a apresentação exata e decidir, no produto unificado, se side panel automático ainda é requisito. O shortcode existente deve ser tratado como contrato de compatibilidade até o cruzamento de consumidores.

## 9. CSS e Design System

Há somente:

- `assets/css/admin-summary.css`;
- `assets/css/executive-summary.css`.

O frontend usa namespace `.bdc-executive-summary` e aceita tokens/fallbacks `--bdc-post-*`. O painel lateral é fixed desktop, adapta em telas menores e fica estático em impressão. O admin possui namespace `.bdc-es-*`, editor responsivo, status chips, KPI cards e dashboard.

**Decisão:** não copiar o CSS como Design System final. MANTER os comportamentos de responsividade/acessibilidade relevantes e REDESENHAR com tokens/componentes do KB2Ops, evitando um segundo sistema visual.

## 10. Persistência auxiliar, cache e APIs

No runtime do GRE 0.6.0 não foram encontrados:

- tabelas próprias / `$wpdb`;
- options próprias de domínio;
- transients;
- WP-Cron;
- AJAX;
- REST próprio;
- exposição das metas via REST;
- fila;
- audit table;
- embeddings/vetores;
- integração de IA.

O único `get_option` de runtime identificado é a leitura do `date_format` nativo do WordPress para exibição de datas no dashboard.

**Aplicação do princípio de negação:** esse bloco é evidência concreta de que metadata editorial/curadoria simples deve permanecer em WordPress Core enquanto o workload permitir.

## 11. Hooks e contrato de integração

### Hooks emitidos

- `bdc_es_loaded` após o boot.

### Hooks administrativos/frontend

- `plugins_loaded`;
- `init` para Meta Contract;
- `admin_menu`;
- `admin_enqueue_scripts`;
- `admin_post_bdc_es_save_summary`;
- `wp_enqueue_scripts`;
- `wp_footer`;
- shortcode `bdc_resumo_executivo`.

### Contratos esperados pelo ASI — DRIFT CONFIRMADO

O ASI 4.6.8 espera:

- classe `BDC\ExecutiveSummary\Objective_Provider`;
- método estático `read_objective( post_id )`;
- evento `bdc_es_objective_updated`.

No GRE 0.6.0 baseline:

- não existe arquivo/classe `Objective_Provider`;
- não existe `read_objective()`;
- não existe emissão de `bdc_es_objective_updated`;
- a busca global de `do_action` no runtime encontra apenas `bdc_es_loaded`.

Consequência no ASI observado:

- adapter de Objective falha vazio quando provider não existe;
- a fila não recebe o evento específico esperado para reindexar Objective alterado.

**Direção futura:** no plugin unificado não deve existir integração frágil entre plugins. O store canônico deve expor leitura de Objective por contrato interno estável e emitir evento pós-persistência confirmada com payload mínimo necessário à invalidação/reindexação. O nome final do contrato será definido na SPEC correspondente; não copiar automaticamente o nome legado.

## 12. Testes e regressão

### Unitários

Suíte observada:

- `AdminMenuTest.php`;
- `AdminPageTest.php`;
- `ArchitectureGuardrailTest.php`;
- `CoverageDashboardTest.php`;
- `FrontendRendererTest.php`;
- `MetaContractTest.php`;
- `PluginBootstrapTest.php`;
- `SummaryStoreTest.php`.

Contratos importantes:

- exatamente oito metas e nenhuma `_bdc_es_title`;
- nenhuma escrita/referência a `_elementor_data`;
- nenhum `CREATE TABLE`/`dbDelta`;
- nenhuma rota REST própria e nenhuma meta aberta via REST;
- ausência de antigos control planes;
- leitura sem write;
- capability `edit_post` por objeto;
- preflight de payload antes de writes;
- sanitização/escaping;
- shortcode current-post-only;
- side panel sem JS e sem duplicação;
- dashboard read-only.

### Integração WordPress real

- `tests/Integration/spec001-wp-cli.php`: registry real de metas, Summary Store, capacidades, sanitização, unicode/backslash, partial update, no-op, empty-delete e post type.
- `tests/Integration/spec002-wp-cli.php`: admin hooks reais, ausência de nopriv, nonce por post, editor, progress, falhas de nonce/payload/capability.

Esses testes são particularmente valiosos porque provam behavior contra WordPress real em vez de apenas mocks.

### Gap de regressão identificado

Não foi encontrada evidência de teste que force falha de persistência no **segundo** campo de um update multi-campo depois de um primeiro write bem-sucedido. Esse teste deverá existir se o futuro contrato exigir atomicidade lógica.

## 13. Build e release

O gate canônico é local e reproduzível:

- `composer validate`;
- `composer install`;
- PHP syntax;
- WordPress Coding Standards;
- PHPUnit;
- smoke tests de package;
- build determinístico;
- relatório `dist/local-verification.json`;
- SHA-256 do ZIP.

`build_plugin.py`:

- ordena arquivos;
- usa timestamp ZIP fixo;
- cria exatamente uma raiz de plugin;
- exclui `.github`, `.specify`, `specs`, `tests`, `tools`, `vendor` e `dist` do pacote;
- valida arquivo principal;
- produz checksum SHA-256.

O smoke test confirma que builds consecutivos são byte-reprodutíveis pelo SHA.

Não há workflow de GitHub Actions no baseline fixado; o repositório declara deliberadamente o gate local para evitar dependência de runner pago.

**Decisão:** MANTER build determinístico e gate local como referência. O novo projeto pode evoluir automação quando houver infraestrutura, sem tornar CI pago pré-requisito.

## 14. Classificação consolidada

| Componente/contrato | Classificação | Justificativa |
|---|---|---|
| 8 campos de Resumo Executivo | MANTER semântica | contrato de produto existente |
| `_bdc_es_*` atuais | MANTER compatibilidade; storage final a cruzar | WordPress-first e consumidores existentes |
| título via `post_title` | MANTER | ownership correto do WordPress |
| `_bdc_es_title` | DESCARTAR/proibir | duplicaria título nativo |
| Metadata API | MANTER | primitive nativa suficiente |
| Summary Store read-only read | MANTER | API interna simples e segura |
| update allowlist/preflight | MANTER | fail-closed |
| empty => delete meta | MANTER | evita lixo persistente |
| read-after-write | MANTER | confirma persistência |
| atomicidade multi-campo atual | REDESENHAR/ENDURECER | sem rollback compensatório comprovado |
| capability `edit_post` | MANTER | autorização por objeto |
| nonce por post | MANTER | CSRF boundary adequada |
| admin-post autenticado | MANTER intenção | primitive final pode permanecer WP nativa |
| dashboard de cobertura | MANTER comportamento / REDESENHAR query | scan ilimitado não deve escalar sem medição |
| `[bdc_resumo_executivo]` | MANTER como compatibilidade inicialmente | consumidor público conhecido |
| shortcode aceitar post arbitrário | DESCARTAR/proibir | evita data exposure/context confusion |
| side panel automático | AINDA NÃO SABEMOS | decisão de produto/UI |
| frontend sem JS | MANTER simplicidade enquanto possível | nenhum comportamento exige JS |
| CSS GRE | REDESENHAR | unificar com DS KB2Ops |
| `Objective_Provider` legado | REDESENHAR contrato interno | inexistente no GRE atual; ASI depende dele |
| `bdc_es_objective_updated` | MANTER intenção / REDESENHAR evento | invalidação precisa de evento estável |
| tabela própria de resumo | DESCARTAR | nenhuma necessidade observada |
| REST próprio | DESCARTAR no baseline | nenhum consumidor/benefício comprovado |
| AJAX próprio | DESCARTAR no baseline | admin-post/server render atende |
| cron próprio | DESCARTAR no baseline | sem workload periódico |
| options/transients próprios | DESCARTAR no baseline | sem estado global necessário |
| integration tests WP real | MANTER | alta confiança de contrato |
| guardrails arquiteturais por source scan | MANTER como secundário | úteis, mas não substituem behavior tests |
| build determinístico + SHA | MANTER | release reproduzível |

## 15. Implicações para o plugin unificado

O GRE fornece a melhor evidência atual para o princípio WordPress-first do novo projeto:

- use post/meta/capability/nonces antes de criar infraestrutura própria;
- mantenha ownership editorial do WordPress/Elementor;
- exponha serviços internos claros em vez de dependências frágeis entre plugins;
- emita eventos após persistência confirmada para projections derivadas;
- só materialize agregados/tabelas se medição provar que primitives nativas não atendem.

A próxima referência a inventariar é o KB2Ops, especialmente Content Extractor, Design System, Knowledge Studio/Search e bridge `_bdc_es_*`. Só depois disso T050–T059 podem consolidar ownership, taxonomias, persistência e paridade.

## 16. Gate do bloco GRE

T040–T047 podem ser considerados concluídos porque:

- baseline/versionamento foram fixados e confirmados contra `main`;
- bootstrap/lifecycle e árvore de runtime foram lidos;
- oito metas e Summary Store foram inventariados;
- Admin/Coverage foram lidos e classificados;
- Frontend/shortcode/assets foram lidos e classificados;
- testes, integração real, build e release foram mapeados;
- ausência de Objective Provider/evento foi comprovada no runtime/testes;
- riscos, regressões e classificação preliminar foram identificados.

Isso **não autoriza SPEC-001** e não fecha decisões cross-reference ainda dependentes de KB2Ops.