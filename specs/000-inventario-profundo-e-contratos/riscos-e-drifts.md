# Riscos, Drifts e Dívidas — SPEC-000

> Documento incremental. Estado após conclusão do bloco ASI.

## Riscos confirmados no ASI

### R-ASI-001 — Extração de conteúdo não é Elementor-aware

**Evidência:** PostIndex, ItemKnowledge/Coordinator, StructuralAudit e Word Cloud trabalham direta ou indiretamente com `post_content`.

**Impacto no novo produto:** alto. Um runtime Elementor-first não pode manter múltiplas representações divergentes do mesmo conteúdo.

**Tratamento:** REDESENHAR todos os consumidores para um Content Extractor canônico, a ser cruzado com KB2Ops. Índice, trechos, auditoria, Word Cloud e futuro RAG devem receber a mesma representação derivada.

### R-ASI-002 — Drift do Objective Provider

**Evidência:** ASI exige `BDC\ExecutiveSummary\Objective_Provider::read_objective()` e possui teste específico para esse contrato. Ausência resulta em Objective vazio.

**Estado:** metade ASI confirmada. Falta confirmar T046 contra o runtime fixado do Gerenciador de Resumo Executivo.

**Risco:** integração silenciosamente degradada: cards/índice deixam de receber Objective sem erro fatal.

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

## Drifts/contratos quebrados em investigação

| ID | Contrato | Estado | Próxima evidência |
|---|---|---|---|
| D-001 | ASI → `BDC\ExecutiveSummary\Objective_Provider` | esperado e testado no ASI | confirmar existência/ausência no GRE 0.6.0 |
| D-002 | ASI → `bdc_es_objective_updated` | Queue espera hook | confirmar emissão no GRE |
| D-003 | ASI raw `post_content` versus produto Elementor-first | incompatibilidade conceitual confirmada | mapear KB2Ops Content Extractor |
| D-004 | ASI Word Cloud extractor versus extractor futuro único | duplicação confirmada | cruzar KB2Ops/arquitetura futura |
| D-005 | roles/tabelas GAC | dependência ambiental | decidir se há requisito de produto após inventário cruzado |

## Dívidas que não devem virar decisão agora

- schema definitivo do índice;
- taxonomias novas;
- tabela de chunks;
- MariaDB Vector;
- embeddings;
- Foundry/provider contract;
- política definitiva de coexistência/migração ASI;
- histórico de revisão;
- retenção final de telemetria.

## Riscos reduzidos por contratos que devem ser preservados

- Golden Queries reduzem regressão silenciosa de relevância.
- Simulation proof + expected-state reduzem Apply sobre estado stale.
- HMAC de interação reduz falsificação de telemetria.
- Queue lease/retry/dead reduz perda silenciosa de reindexação.
- fail-empty do Objective evita inventar contexto editorial.
- fail-closed de anchors evita links para destinos não comprovados.
- Site Health/export redigido melhora diagnóstico sem expor dados desnecessários.
- uninstall não destrutivo reduz risco de perda durante replacement/rollback.

## Status

Nenhum risco do bloco ASI bloqueia a continuação da SPEC-000. Eles **bloqueiam**, porém, decisões prematuras de runtime/schema. O próximo bloco deve confirmar o Gerenciador de Resumo Executivo, especialmente D-001/D-002, antes do cruzamento final.