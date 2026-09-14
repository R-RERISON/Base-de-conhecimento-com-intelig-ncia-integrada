# Riscos, Drifts e Dívidas — SPEC-000

> Documento incremental. Estado após conclusão dos blocos ASI e Gerenciador de Resumo Executivo (GRE).

## 1. Riscos confirmados no ASI

### R-ASI-001 — Extração de conteúdo não é Elementor-aware

**Evidência:** PostIndex, ItemKnowledge/Coordinator, StructuralAudit e Word Cloud trabalham direta ou indiretamente com `post_content`.

**Impacto no novo produto:** alto. Um runtime Elementor-first não pode manter múltiplas representações divergentes do mesmo conteúdo.

**Tratamento:** REDESENHAR todos os consumidores para um Content Extractor canônico, a ser cruzado com KB2Ops. Índice, trechos, auditoria, Word Cloud e futuro RAG devem receber a mesma representação derivada.

### R-ASI-002 — Drift do Objective Provider

**Evidência:** ASI exige `BDC\ExecutiveSummary\Objective_Provider::read_objective()` e possui teste específico para esse contrato. Ausência resulta em Objective vazio.

**Estado após T046:** **CONFIRMADO**. O GRE 0.6.0 não contém a classe/método esperados.

**Risco:** integração silenciosamente degradada: cards/índice podem deixar de receber Objective sem erro fatal.

**Tratamento futuro:** eliminar dependência entre plugins no bounded context unificado; usar store interno canônico e contrato explícito/testado.

### R-ASI-003 — Telemetria minimal ainda armazena query text

**Evidência:** identidade/session/IP/UA são suprimidos no modo minimal, mas termo bruto/normalizado continua sendo fato da busca.

**Risco:** usuários podem digitar informação sensível no campo de busca; “sem identidade” não equivale a “sem dado sensível”.

**Tratamento:** política explícita de data minimization, acesso, retenção e eventual redução de query antes de implementar telemetria nova.

### R-ASI-004 — Rate limit anônimo dependente de IP + User-Agent

**Risco:** NAT/proxy pode agrupar usuários; cabeçalhos podem não representar o cliente real; política de proxy não está abstraída.

**Tratamento:** REDESENHAR identity bucket conforme topologia real, mantendo hash e sem persistência de IP cru.

### R-ASI-005 — Complexidade histórica pode contaminar greenfield

**Evidência:** 12 tabelas, MigrationRunner 4.x, BaseReconciler, PostInstallOrchestrator e legacy/compat.

**Risco:** copiar arquitetura por familiaridade e criar custo operacional antes da necessidade.

**Tratamento:** princípio de negação; importar contratos, não estruturas. Toda tabela/cron/state machine futura precisa de justificativa própria.

### R-ASI-006 — Word Cloud duplica pipeline lexical

**Evidência:** reprocessa posts/pages, headings/body, taxonomy, eventos e vocabulary para produzir snapshot próprio.

**Risco:** divergência entre o que a busca entende e o que a Word Cloud entende; custo e bugs duplicados.

**Tratamento:** se a feature sobreviver, consumir projections/telemetria canônicas.

### R-ASI-007 — Acoplamento do Search Intelligence ao GAC

**Evidência:** roles `gac_*` e tabelas `gac_v15_*` aparecem na resolução organizacional.

**Risco:** transformar uma integração ambiental em requisito do core.

**Tratamento:** remover do núcleo; adapter opcional/configurável se o produto final realmente precisar de equipe/perfil.

### R-ASI-008 — Testes estruturais por string podem dar falsa confiança

**Evidência:** `test-source-contracts.php` e `test-performance-bounds-46.php` protegem vários contratos via leitura/`strpos` de código.

**Valor:** bons guardrails de arquitetura.

**Risco:** passar sem provar comportamento runtime; quebrar por refactor sem regressão real.

**Tratamento:** portar intenção para unit/integration/E2E; manter source contract somente para invariantes onde faça sentido.

### R-ASI-009 — `quality_daily` antecipa otimização

**Risco:** materialização agregada adiciona tabela, retenção e sincronização sobre fatos que já existem em events/interactions.

**Tratamento:** não nascer no baseline; criar apenas após benchmark provar necessidade.

### R-ASI-010 — Hardcodes de vocabulário/corpus em QueryContext

**Risco:** conhecimento específico envelhece no código e cria comportamento opaco.

**Tratamento:** equivalências administráveis/versionadas; hardcode apenas para regras linguísticas estáveis.

---

## 2. Riscos confirmados no Gerenciador de Resumo Executivo

### R-GRE-001 — Ausência de evento pós-persistência de Objective

**Evidência:** `Summary_Store::update()` confirma writes, mas não emite action específica após alteração. O bootstrap GRE emite somente `bdc_es_loaded`.

**Impacto:** projections consumidoras não possuem contrato nativo para invalidação/reindexação. O ASI tenta compensar esperando `bdc_es_objective_updated`, hook inexistente no GRE baseline.

**Tratamento:** no plugin unificado, emitir evento de domínio **somente após persistência confirmada**, com payload mínimo e teste de integração para reindexação/invalidação.

### R-GRE-002 — Persistência multi-campo pode produzir estado parcialmente aplicado em falha tardia

**Evidência:** payload completo é validado antes do primeiro write, mas writes são executados sequencialmente. Não há transação nem rollback compensatório se um campo posterior falhar após outro já ter sido persistido.

**Impacto:** baixo em fluxo normal, mas relevante para contrato de confiabilidade/consistência.

**Tratamento:** definir semântica futura explicitamente. Preferência: compensação lógica ou estratégia que evite afirmar atomicidade que WordPress post meta não oferece nativamente. Não criar banco próprio apenas por isso.

### R-GRE-003 — Coverage Dashboard possui scan não limitado

**Evidência:** `get_posts` com `posts_per_page = -1` para todos os posts publicados e preload de meta cache para todos os IDs.

**Impacto:** memória/latência crescem com o corpus.

**Tratamento:** MANTER métricas, REDESENHAR consulta com workload limitado/paginado/agregação medida. Tabela de rollup só se benchmark provar necessidade.

### R-GRE-004 — Histórico/revisions de metadata desabilitado no baseline

**Evidência:** as oito metas são registradas com `revisions_enabled = false`.

**Risco:** o estado atual não oferece histórico nativo dessas alterações; isso pode conflitar com requisitos futuros de auditoria/curadoria.

**Tratamento:** não decidir nesta SPEC. Cruzar com requisitos de histórico/revisão e WordPress-first antes de adicionar infraestrutura.

### R-GRE-005 — Side panel automático é decisão de produto embutida no runtime

**Evidência:** `wp_footer` renderiza painel fixo em post singular quando shortcode inline não foi usado.

**Risco:** transformar uma escolha visual antiga em invariante arquitetural do novo produto.

**Tratamento:** MANTER capacidade de render read-only, mas decidir superfície no Design System/UX do plugin unificado após KB2Ops.

### R-GRE-006 — Ausência de política formal de uninstall

**Evidência:** não existe `uninstall.php` no baseline.

**Valor:** não há deleção automática das metas.

**Risco:** retenção fica implícita, sem procedimento documentado para remoção consciente.

**Tratamento:** definir política explícita no plugin unificado: uninstall não destrutivo por default; purge separado e deliberado se necessário.

---

## 3. Drifts/contratos quebrados

| ID | Contrato | Estado | Evidência/ação |
|---|---|---|---|
| D-001 | ASI → `BDC\ExecutiveSummary\Objective_Provider` | **CONFIRMADO QUEBRADO** | ASI espera/testa; GRE 0.6.0 não possui classe/método. Redesenhar como serviço interno no plugin unificado. |
| D-002 | ASI → `bdc_es_objective_updated` | **CONFIRMADO QUEBRADO** | Queue ASI espera; GRE não emite. Criar evento pós-persistência interno futuro. |
| D-003 | ASI raw `post_content` versus produto Elementor-first | incompatibilidade conceitual confirmada | mapear KB2Ops Content Extractor em T015 |
| D-004 | ASI Word Cloud extractor versus extractor futuro único | duplicação confirmada | cruzar KB2Ops/arquitetura futura |
| D-005 | roles/tabelas GAC | dependência ambiental | decidir se há requisito de produto após inventário cruzado |
| D-006 | campos GRE classificatórios em post meta versus potencial taxonomia | **ABERTO** | só decidir após inventariar `_kb2ops_*`, taxonomias e filtros/queries do KB2Ops |
| D-007 | CSS GRE/ASI versus Design System único | **ABERTO** | mapear tokens/componentes KB2Ops em T018 |

### Consequência de D-001/D-002

O problema não deve ser “corrigido” nos plugins de referência durante a SPEC-000. Eles são evidência histórica. A solução pertence à arquitetura do plugin unificado:

- store canônico único para Resumo Executivo;
- leitura direta interna do Objective;
- evento pós-write confirmado;
- indexação derivada reagindo ao evento;
- nenhum fallback que invente Objective a partir de `post_content`.

---

## 4. Dívidas que não devem virar decisão agora

- schema definitivo do índice;
- taxonomias novas;
- conversão dos campos GRE classificatórios para taxonomias;
- tabela de chunks;
- MariaDB Vector;
- embeddings;
- Foundry/provider contract;
- política definitiva de coexistência/migração ASI;
- histórico de revisão;
- retenção final de telemetria;
- estratégia final de shortcode/painel de Resumo Executivo;
- estratégia definitiva de anchors.

---

## 5. Riscos reduzidos por contratos que devem ser preservados

- Golden Queries reduzem regressão silenciosa de relevância.
- Simulation proof + expected-state reduzem Apply sobre estado stale.
- HMAC de interação reduz falsificação de telemetria.
- Queue lease/retry/dead reduz perda silenciosa de reindexação.
- fail-empty do Objective evita inventar contexto editorial.
- fail-closed de anchors evita links para destinos não comprovados.
- Site Health/export redigido melhora diagnóstico sem expor dados desnecessários.
- GRE Meta Contract reduz drift de chaves e proíbe `_bdc_es_title`.
- GRE capability `edit_post` reduz bypass de autorização por objeto.
- GRE validação completa do payload antes do write reduz partial write por erro de entrada.
- integração WordPress real do GRE reduz falso positivo de mocks.
- build determinístico + SHA melhora rastreabilidade de release.

## Status

Nenhum risco do bloco GRE bloqueia a continuação da SPEC-000. D-001 e D-002 estão agora **fechados como incompatibilidades confirmadas**, não como incógnitas. O próximo bloco deve ser KB2Ops, pois T015/T018 são necessários para resolver D-003, D-006 e D-007 antes do cruzamento T050–T059.