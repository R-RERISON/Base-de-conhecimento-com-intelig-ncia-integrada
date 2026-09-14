# Riscos, Drifts e Dívidas — SPEC-000

> Estado após inventário das três referências e conclusão de T050–T053. Este documento mantém riscos ativos e registra quais foram reduzidos por ownership, sobreposição e contratos unificados. T054 fará a classificação final dos drifts/compatibilidade/blockers.

## 1. Riscos ASI que permanecem relevantes

### R-ASI-001 — Extração não Elementor-aware

**Estado:** direção arquitetural resolvida; risco de implementação ainda ativo.  
**Tratamento:** Content Extraction único; downstream não reparseia `post_content`/`_elementor_data`.

### R-ASI-002 — `Objective_Provider` quebrado

**Estado:** drift histórico confirmado.  
**Tratamento:** Summary Store interno; T054 classifica compatibilidade necessária ou descarte do adapter.

### R-ASI-003 — Query text em telemetria minimal

**Estado:** aberto.  
**Tratamento:** T057/T095 define minimização, retenção, acesso e necessidade real.

### R-ASI-004 — Rate limit IP + User-Agent

**Estado:** aberto para topologia real.  
**Tratamento:** bucket/redesign sem IP cru persistido.

### R-ASI-005 — Complexidade histórica contaminar greenfield

**Estado:** reduzido por T050/T051: stores/hook antigos agora são origem, não design futuro.  
**Risco remanescente:** T057 ainda pode superdimensionar índice/queue/analytics se não aplicar negação.

### R-ASI-006 — Word Cloud com pipeline duplicado

**Estado:** direção resolvida.  
**Tratamento:** se sobreviver por produto/preflight, consumir projections/Analytics canônicos.

### R-ASI-007 — GAC no core

**Estado:** fora do core; compatibilidade/requisito ainda a provar.  
**Tratamento:** adapter opcional/degradável.

### R-ASI-008 — testes por string

**Estado:** aberto para T055.  
**Tratamento:** portar intenção para unit/integration/E2E; source guards só complementares.

### R-ASI-009 — `quality_daily`

**Estado:** rejeitado inicialmente por T050.  
**Tratamento:** só reintroduzir com benchmark.

### R-ASI-010 — hardcodes de vocabulário

**Estado:** Search Knowledge terá owner próprio.  
**Tratamento:** conhecimento administrável/versionado; hardcode só para regra linguística estável.

## 2. Riscos GRE

### R-GRE-001 — ausência de evento de Objective

**Estado:** contrato futuro definido em T051; runtime histórico permanece quebrado.  
**Tratamento:** write confirmado -> evento mínimo -> invalidation idempotente.

### R-GRE-002 — falha tardia multi-campo

**Estado:** aberto.  
**Tratamento:** especificar semântica na SPEC de runtime; não criar tabela apenas para simular transação.

### R-GRE-003 — Coverage sem bound

**Estado:** aberto.  
**Tratamento:** queries bounded/cache/benchmark; rollup só com evidência.

### R-GRE-004 — revisions metadata off

**Estado:** aberto T056/T095.

### R-GRE-005 — side panel automático

**Estado:** classificado em T051 como UX histórica não canônica; preflight/produto decide eventual compatibilidade.

### R-GRE-006 — uninstall implícito

**Estado:** política futura consolidada: não destrutivo por default, purge explícito.

## 3. Riscos KB2Ops

### R-KB-001 — extração Elementor parcialmente incompleta

**Impacto:** alto; continua blocker técnico para Search/RAG de produção.  
**Tratamento:** fixtures reais, cobertura de widgets e diagnóstico de completude antes de considerar extractor final.

### R-KB-002 — `kb2ops_post_approved` antes de comprovar writes

**Estado:** comportamento rejeitado em T051.  
**Tratamento:** validar -> persistir -> confirmar -> emitir.

### R-KB-003 — Meta Contract incompleto

**Estado:** T050 tornou todo dado persistido sujeito a contrato explícito; primitive final ainda T056.

### R-KB-004 — Search provisória/scan/meta LIKE

**Estado:** implementação futura descartada; contrato de UX/scope preservado.

### R-KB-005 — analytics option com query text

**Estado:** store paralelo rejeitado em T050; facts/retention T057.

### R-KB-006 — view count aproximado

**Estado:** não será fonte principal de uso futura.

### R-KB-007 — Summary Bridge duplica chaves

**Estado:** arquitetura permanente rejeitada; adapter somente se cutover provar necessidade.

### R-KB-008 — release audit sem suíte versionada

**Estado:** aberto T055; contratos úteis precisam de testes executáveis novos.

### R-KB-009 — AI READY docs/runtime

**Estado:** drift confirmado; runtime baseline = publish + approved + Resumo 8/8 + include_ai.

### R-KB-010 — cleanup hardened histórico

**Estado:** listas históricas descartadas; princípio reversível preservado.

### R-KB-011 — classificações duplicadas

**Estado:** ownership resolvido; dual-write permanente proibido.  
**Risco remanescente:** primitive/cardinalidade/migração incorretas em T056.

## 4. Drifts D-001–D-008 — estado antes de T054

| ID | Drift | Estado após T050/T051 | Pendência T054 |
|---|---|---|---|
| D-001 | ASI `Objective_Provider` inexistente no GRE | solução futura = Summary Store interno | definir se adapter histórico é necessário no cutover |
| D-002 | ASI escuta evento inexistente | contrato pós-write futuro definido | classificar compat/remoção |
| D-003 | ASI lê `post_content` vs Elementor-first | direção resolvida por extractor único | manter como regressão/blocker de implementação |
| D-004 | múltiplos parsers | direção resolvida | decidir destino Word Cloud/anchors históricos |
| D-005 | GAC acoplado | fora do core | preflight/requisito externo |
| D-006 | GRE/KB2Ops classifications duplicadas | owner único + dual-write proibido | profiling/compat/migração |
| D-007 | UI/CSS fragmentados | DS único | compat visual/shortcodes ainda a provar |
| D-008 | AI READY docs != runtime | baseline runtime fixada | atualizar contrato futuro/teste; sem compat técnica complexa |

## 5. Riscos cross-module

### X-001 — dual-read/dual-write virar estado permanente

**T050:** dual-write permanente foi proibido.  
**Risco remanescente:** adapter temporário sem métrica/gate de remoção.

### X-002 — evento antes de consistência

**T051:** contrato definido como write confirmado antes de evento.  
**Risco remanescente:** implementação futura precisa de testes de falha tardia/idempotência.

### X-003 — índice correto sobre extração incompleta

Continua crítico. Extraction Quality precede Search Quality.

### X-004 — Analytics super ou subdimensionado

Continua aberto para T057. Não copiar ASI inteiro nem KB2Ops leve demais.

### X-005 — migração virar arquitetura permanente

T050/T051 classificam migration/adapters como transitórios. T054 deve exigir gate de remoção.

### X-006 — Design System virar cópia de CSS

Owner visual já resolvido. Runtime futuro deve reconstruir tokens/components, não colar dialetos antigos.

### X-007 — approval de conteúdo confundido com Search Apply

T051 formalizou eventos/workflows separados. Regressão futura deve impedir efeito lateral.

### X-008 — dashboard único virar workload ilimitado

Continua aberto; cada métrica precisa de owner, budget e query bounded.

### X-009 — DS gerar acoplamento lateral

Continua ativo: UI recebe view model/contrato; não lê stores de outros domains diretamente.

### X-010 — shortcodes/aliases históricos portados sem consumidor

T051 classifica todos como **compat a provar**. T054 deve transformar isso em política de preflight/blocker.

### X-011 — tempestade de invalidação/eventos

Novo risco explicitado por T051: múltiplos writes no mesmo post podem produzir reindexações redundantes.

**Tratamento:** evento mínimo/versionado, coalescing/debounce operacional quando necessário e consumidores idempotentes. Não resolver antecipadamente com queue sem T057.

### X-012 — hook interno virar API pública acidental

Nomear action WordPress pode induzir consumidores externos não documentados.

**Tratamento:** distinguir contrato interno, compat e API pública; só prometer estabilidade quando houver consumidor/requisito explícito.

### X-013 — projection stale sem observabilidade

Falha non-fatal é correta, mas pode ocultar índice desatualizado.

**Tratamento:** estado saudável/degradado/stale visível via Site Health/diagnóstico mínimo; sem editar dado canônico para “corrigir”.

## 6. Decisões de risco consolidadas por T050/T051

- um conceito = um owner;
- dual-write permanente proibido;
- adapter = temporário + gate de remoção;
- projection não é canônico;
- evento pós-write confirmado;
- consumers idempotentes;
- Analytics non-fatal/privacy-first;
- REST negado sem consumidor;
- AJAX só por UX live;
- queue não aprovada antes de T057;
- migration/reconciler não são core permanente;
- shortcodes históricos exigem preflight.

## 7. Blockers que permanecem para autorizar SPEC-001

Ainda não são necessariamente blockers finais, mas precisam de decisão explícita até T095:

1. primitive/cardinalidade de classificações reutilizáveis;
2. profiling/migração de audiência e campos relacionados;
3. storage mínimo de Search Knowledge/Golden;
4. schema mínimo de Search Index/Items;
5. necessidade ou não de queue durável no primeiro slice;
6. política de Analytics/query text/retention;
7. estratégia de histórico/revisions;
8. preflight de shortcodes/consumidores externos;
9. qualidade/completude do Content Extractor para widgets reais;
10. política de deep-link/anchors.

## Status

T050/T051 podem ser fechadas. Próximo passo: **T054** classificar formalmente drifts, compatibilidade temporária, descartes, dependências de preflight/profiling e blockers. Nenhum risco justifica iniciar runtime antes disso.