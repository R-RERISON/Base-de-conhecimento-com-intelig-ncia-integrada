# Riscos, Drifts e Dívidas — SPEC-000

> Estado após inventário completo das três referências e conclusão do cruzamento inicial T052/T053. Este documento registra risco/contrato; decisões de primitive/storage pertencem a T050/T056/T057.

## 1. Riscos confirmados no ASI

### R-ASI-001 — Extração de conteúdo não é Elementor-aware

ASI usa direta/indiretamente `post_content` em PostIndex, Item Knowledge, Structural Audit e Word Cloud.

**Tratamento:** REDESENHAR todos os consumidores sobre um Content Extractor canônico. T053 confirmou essa convergência como direção funcional obrigatória.

### R-ASI-002 — Drift do Objective Provider

ASI exige `BDC\ExecutiveSummary\Objective_Provider::read_objective()`; GRE 0.6.0 não possui classe/método.

**Tratamento:** store interno único no plugin unificado.

### R-ASI-003 — Telemetria minimal ainda armazena query text

Modo minimal suprime identity/session/IP/UA, mas persiste termo de busca.

**Tratamento:** política explícita de minimização/acesso/retenção antes de implementar telemetria futura.

### R-ASI-004 — Rate limit anônimo por IP + User-Agent

NAT/proxy pode agrupar usuários e headers podem não representar origem real.

**Tratamento:** identity bucket conforme topologia real; sem IP cru persistido.

### R-ASI-005 — Complexidade histórica pode contaminar greenfield

12 tabelas + migrations/reconciler/orchestrator/legacy não ganham direito automático de existir.

**Tratamento:** importar contratos, não estruturas.

### R-ASI-006 — Word Cloud duplica pipeline lexical

Pode divergir da busca e duplicar custo.

**Tratamento:** se sobreviver, consumir extractor/index/telemetria canônicos.

### R-ASI-007 — Acoplamento GAC

Roles/tabelas GAC aparecem em Search Intelligence.

**Tratamento:** fora do core; adapter opcional se requisito real existir.

### R-ASI-008 — Source-string tests podem dar falsa confiança

Guardrails estruturais são úteis, mas não provam runtime.

**Tratamento:** comportamento em unit/integration/E2E; source checks apenas complementares.

### R-ASI-009 — `quality_daily` antecipa otimização

Tabela agregada duplica fatos existentes.

**Tratamento:** não nascer sem benchmark.

### R-ASI-010 — Hardcodes de vocabulário/corpus

Conhecimento específico no código envelhece.

**Tratamento:** equivalências administráveis/versionadas.

---

## 2. Riscos confirmados no Gerenciador de Resumo Executivo

### R-GRE-001 — Ausência de evento pós-persistência de Objective

GRE confirma writes, mas não emite evento de mudança. ASI escuta hook inexistente.

**Tratamento:** evento de domínio somente após persistência confirmada.

### R-GRE-002 — Multi-campo pode ficar parcialmente aplicado em falha tardia

Validação é prévia, porém writes são sequenciais e não há rollback compensatório.

**Tratamento:** especificar semântica; não criar tabela própria apenas para simular transação.

### R-GRE-003 — Coverage Dashboard sem bound

`posts_per_page=-1` + preload de metas.

**Tratamento:** workload limitado/paginado/benchmark; rollup só se medição justificar.

### R-GRE-004 — Revisions metadata off

As oito metas não possuem revisions no baseline.

**Tratamento:** decidir após requisitos de histórico/curadoria.

### R-GRE-005 — Side panel automático embute decisão antiga de UX

**Tratamento:** preservar capacidade read-only, decidir apresentação no DS único.

### R-GRE-006 — Política de uninstall implícita

Não há `uninstall.php`.

**Tratamento:** uninstall não destrutivo por default + purge deliberado se necessário.

---

## 3. Riscos confirmados no KB2Ops

### R-KB-001 — Extração Elementor pode ficar parcialmente incompleta

O parser de `_elementor_data` usa allowlist. Renderização Elementor só ocorre se a saída determinística for vazia. Um documento com widget reconhecido + custom widget relevante pode produzir texto não vazio, porém incompleto, impedindo fallback.

**Impacto:** alto para Search/RAG — conteúdo pode desaparecer silenciosamente.

**Tratamento:** corpus de fixtures Elementor, cobertura por widget e política explícita para detectar extração insuficiente. Nunca assumir que “não vazio” equivale a “completo”.

### R-KB-002 — Evento `kb2ops_post_approved` pode ocorrer sem confirmação completa de persistência

`Knowledge::save_review()` executa vários `update_post_meta()` sem verificar seus retornos e depois emite evento em transição para `approved`.

**Impacto:** projections/IA futura podem reagir a estado parcialmente aplicado.

**Tratamento:** validate -> persist -> read/confirm final -> emit. Falhar fechado.

### R-KB-003 — Meta Contract incompleto

`_kb2ops_review_history` e `_kb2ops_view_count` são persistidos mas não registrados junto das metas principais.

**Impacto:** schema implícito, sanitização/auth/revisions não ficam centralizados.

**Tratamento:** todo dado persistido precisa de contrato explícito ou justificativa documentada.

### R-KB-004 — Busca provisória não escala como arquitetura

`numberposts=-1`, `meta_query LIKE`, `_elementor_data` e scans completos aparecem em Search/Studio/Reports.

**Impacto:** latência/memória/query cost crescem com corpus.

**Tratamento:** substituir retrieval pelo motor lexical/item derivado do ASI; dashboards ganham bounds/benchmark.

### R-KB-005 — Search analytics em option armazena query text

Até 500 termos normalizados são persistidos com count/last; retenção é cardinalidade, não tempo.

**Impacto:** risco de dado sensível e concorrência ao reescrever mapa inteiro.

**Tratamento:** telemetria mínima com privacy/retention/outcomes; não portar store atual literalmente.

### R-KB-006 — View count é contador aproximado

Read + increment + update de postmeta não é atomicamente confiável sob concorrência.

**Tratamento:** tratar como métrica aproximada ou migrar para facts de interação se analytics robusto for requisito.

### R-KB-007 — Bridge GRE duplica contrato de oito chaves

`Summary_Bridge` mantém mapa próprio independente do GRE.

**Impacto:** mudanças futuras podem divergir silenciosamente.

**Tratamento:** no plugin unificado, um único Meta Contract/Summary Store. Bridge apenas como adapter de coexistência se necessário.

### R-KB-008 — Release audit não substitui suíte executável versionada

O relatório 0.2.1 documenta dezenas de checks, mas a baseline não contém `tests/`/scripts equivalentes para reproduzi-los.

**Impacto:** rastreabilidade/repetibilidade reduzidas.

**Tratamento:** contratos KB2Ops portados precisam de testes executáveis no novo repo.

### R-KB-009 — AI READY tem drift entre documentação e runtime

Docs citam publish + approved + Resumo 8/8; runtime também exige `_kb2ops_include_ai`.

**Tratamento:** contrato único, documentado e testado; runtime baseline é autoridade histórica.

### R-KB-010 — Cleanup hardened carrega conhecimento histórico

Installer/Migration conhecem crons, capabilities, options e tabelas do runtime aposentado.

**Tratamento:** MANTER princípio reversível; DESCARTAR listas históricas do greenfield.

### R-KB-011 — Classificações duplicadas em string meta

Audiência/serviço/tecnologias existem em KB2Ops e parcialmente no GRE, e são usadas para filtro/relatório.

**Estado após T052:** ownership lógico resolvido para **Classificação de Conhecimento**. O risco remanescente é storage/cardinalidade/migração inadequados.

**Tratamento:** T056 decide primitive; não migrar por nome de campo.

---

## 4. Drifts/contratos quebrados e estado atual

| ID | Contrato | Estado | Direção |
|---|---|---|---|
| D-001 | ASI -> `Objective_Provider` | **QUEBRADO CONFIRMADO** | store interno único |
| D-002 | ASI -> `bdc_es_objective_updated` | **QUEBRADO CONFIRMADO** | evento pós-write confirmado |
| D-003 | ASI raw `post_content` vs Elementor | **DIREÇÃO FUNCIONAL RESOLVIDA** | Content Extractor único |
| D-004 | Word Cloud/Item/Search parsers separados | **DUPLICAÇÃO RESOLVIDA EM DIREÇÃO** | downstream não reparseia fonte |
| D-005 | GAC no core ASI | **DEPENDÊNCIA AMBIENTAL** | adapter opcional |
| D-006 | GRE fields vs KB2Ops classifications | **OWNERSHIP RESOLVIDO; STORAGE ABERTO** | Classificação de Conhecimento; T056 define primitive |
| D-007 | CSS/UI ASI+GRE+KB2Ops | **OWNERSHIP VISUAL RESOLVIDO** | DS único KB2Ops-derived; runtime ainda não existe |
| D-008 | KB2Ops AI READY docs vs runtime | **DRIFT INTERNO CONFIRMADO** | contrato runtime único/testado |

### D-006 em detalhe após T052

- GRE `target_audience` e KB2Ops `_kb2ops_target_audience` representam um único conceito de **audiência**;
- GRE `affected_service` e KB2Ops `_kb2ops_service` ficam no mesmo domínio, mas **não são declarados equivalentes**;
- GRE `systems_involved` e KB2Ops `_kb2ops_technologies` ficam no mesmo domínio, mas **não são declarados equivalentes**;
- `responsible_team`, `catalog_item`, `knowledge_type`, keywords e versions passam a ter owner lógico Classificação de Conhecimento;
- o Resumo Executivo pode compor/editar classificações na UI, sem ser uma segunda fonte da verdade.

A decisão de taxonomy/postmeta e mapeamento de valores continua aberta.

---

## 5. Riscos cross-module prioritários

### X-001 — Duas fontes de verdade classificatórias

**Estado:** risco estrutural reduzido por T052; ownership lógico único foi definido.

**Risco remanescente:** uma migração mal desenhada pode continuar dual-write/dual-read indefinidamente.

**Tratamento:** compatibilidade com prazo/gate de remoção; um único writer canônico no estado final.

### X-002 — Evento antes de consistência

GRE não emite; KB2Ops emite aprovação sem confirmação completa; ASI depende de events para projections.

**Contrato futuro:** persistência confirmada precede qualquer evento derivado.

### X-003 — Índice correto sobre conteúdo incompleto

Mesmo um ranker perfeito falha se extractor omitir widget Elementor. Extraction quality é gate anterior a Search quality.

### X-004 — Analytics superdimensionado ou subdimensionado

ASI é robusto/complexo; KB2Ops é leve/frágil. O produto precisa do conjunto mínimo que responda perguntas reais sem coletar mais dados que o necessário.

### X-005 — Migração virar arquitetura permanente

ASI e KB2Ops carregam histórias de cutover. O greenfield não deve nascer com bridges/reconcilers permanentes por medo do legado.

### X-006 — Design System virar coleção de cópias

**Estado:** T053 definiu DS único como owner visual.

**Risco remanescente:** copiar CSS/markup antigo em módulos específicos pode recriar dialetos.

### X-007 — Confundir aprovação de conteúdo com curadoria de Search

`review_state=approved` e Apply de vocabulary/binding/rule têm semânticas, riscos e capabilities diferentes.

**Impacto:** alteração de ranking poderia ocorrer como efeito colateral de uma aprovação editorial.

**Tratamento:** workflows/state machines/capabilities separados, embora apresentados no mesmo shell.

### X-008 — Dashboard único virar mega agregador caro

Consolidar Coverage, Reports e Search Intelligence em uma navegação única pode incentivar cada card a executar scans integrais.

**Tratamento:** cada métrica possui owner e budget; queries bounded; agregação/materialização apenas por benchmark.

### X-009 — Design System único gerar acoplamento lateral

Componentes compartilhados podem tentar acessar stores de domínio diretamente.

**Tratamento:** DS é apresentação; módulos expõem dados/view models mínimos. Compartilhar componente não transfere ownership.

### X-010 — Compatibilidade virar duplicação permanente

Aliases de shortcode, bridges de meta e dual-read podem ser úteis no cutover.

**Impacto:** um único plugin poderia reproduzir internamente três arquiteturas antigas.

**Tratamento:** cada adapter precisa de consumidor comprovado, condição de entrada, owner canônico, telemetria/preflight e gate de remoção.

---

## 6. Dívidas que continuam proibidas de virar decisão isolada

- schema definitivo do índice;
- taxonomy versus postmeta campo a campo;
- tabela de chunks;
- MariaDB Vector/embeddings;
- Foundry/provider contract;
- retention final;
- histórico/revisions final;
- coexistência/migração detalhada;
- fila própria;
- anchors finais;
- UI runtime final por feature.

---

## 7. Contratos que reduzem risco e devem sobreviver

- Golden Queries e simulation proof do ASI;
- HMAC/idempotência/rate-limit do tracking ASI;
- Queue lease/retry/dead se fila for necessária;
- fail-empty Objective;
- fail-closed anchors;
- Metadata API/capability `edit_post` do GRE;
- validação de payload antes de write;
- integração WordPress real;
- Content Extractor read-only do KB2Ops;
- allowlist de shortcodes;
- review/AI opt-in humano;
- Design System sem IA/SPA obrigatória;
- activation/uninstall não destrutivos;
- build determinístico + SHA;
- **um conceito canônico = um owner lógico**;
- **persistência confirmada precede evento**;
- **Search/Analytics/projections nunca assumem ownership editorial/classificatório**.

## Status

T052 e T053 podem ser fechadas. Os riscos remanescentes não autorizam runtime; eles orientam a próxima etapa: **T050/T051 consolidar persistência e integrações**, seguida por T054/T056/T057.
