# Mapa de Contratos Quebrados, Drifts e Compatibilidade — SPEC-000 — T054

> Estado: **T054 concluída documentalmente**.  
> Baselines cruzadas: ASI `4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1`, GRE `0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365`, KB2Ops `0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.
>
> Este documento classifica drifts históricos, compatibilidade de cutover e blockers. Não cria runtime, migration, taxonomy, tabela, aliases ou hooks finais.

## 1. Classes de decisão

- **CORRIGIDO PELA ARQUITETURA FUTURA** — o contrato histórico não continua como arquitetura permanente; existe direção de substituição já definida.
- **COMPAT TEMPORÁRIO** — pode existir somente durante coexistência/cutover e precisa de consumidor comprovado + gate de remoção.
- **DESCARTADO** — não existe requisito que justifique transportar o contrato.
- **DEPENDE DE PREFLIGHT/PROFILING** — decisão depende de uso real, dados reais ou consumidor real.
- **BLOCKER** — precisa estar resolvido antes de liberar o slice/runtime que depende dele.

Uma mesma divergência pode ter uma direção arquitetural definitiva e, simultaneamente, uma necessidade de compatibilidade temporária. Isso não autoriza dual-write permanente.

## 2. D-001 — ASI espera `Objective_Provider`, GRE não expõe

**Evidência histórica:** ASI chama `BDC\ExecutiveSummary\Objective_Provider::read_objective()`; GRE 0.6.0 não contém essa classe/método.

**Produtor histórico esperado:** GRE.  
**Consumidor histórico:** ASI Search/cards/indexação.  
**Estado:** contrato quebrado confirmado.

### Classificação

- Arquitetura final: **CORRIGIDO PELA ARQUITETURA FUTURA**.
- Cutover: **COMPAT TEMPORÁRIO CONDICIONAL** somente se ASI legado precisar permanecer ativo enquanto o novo plugin já for owner do Resumo.

### Direção

- um Summary Store interno canônico substitui provider externo;
- Search consome contrato interno do owner, não classe de outro plugin;
- nenhum `Objective_Provider` entra no core futuro por compatibilidade histórica.

### Coexistência possível

Se preflight provar necessidade de coexistência com ASI legado, um adapter temporário pode expor exatamente a leitura necessária, sem virar owner e sem criar segundo storage.

### Gate de remoção

Adapter removível quando:

1. ASI legado não participa mais do fluxo homologado/produtivo; e
2. nenhuma chamada externa comprovada ao provider existir; e
3. Search novo ler Summary Store interno com regressão equivalente.

### Regressão futura

- Summary ausente -> vazio seguro;
- leitura nunca escreve;
- Search recebe Objective do owner canônico;
- remoção do adapter não altera resultado após cutover.

---

## 3. D-002 — ASI escuta `bdc_es_objective_updated`, GRE nunca emite

**Evidência histórica:** Queue/Indexing ASI espera action de mudança; GRE confirma writes mas não emite esse hook.

**Estado:** contrato quebrado confirmado.

### Classificação

- Arquitetura final: **CORRIGIDO PELA ARQUITETURA FUTURA** por EVT-001/EVT-003 documentais.
- Cutover: **COMPAT TEMPORÁRIO CONDICIONAL** somente se ASI legado precisar receber invalidação durante coexistência.

### Direção

Contrato futuro: `validar -> persistir -> confirmar -> emitir evento mínimo -> consumidor invalida/reconstrói projection`.

Nunca emitir evento apenas por submit válido.

### Compatibilidade

Não há motivo para recriar permanentemente o nome histórico. Se coexistência exigir, um bridge pode traduzir evento canônico confirmado para o action legado durante período controlado.

### Gate de remoção

- ASI legado desligado;
- consumidores do hook antigo inexistentes por preflight;
- novo Search prova invalidação/rebuild idempotente.

### Regressão futura

- falha de write não emite evento;
- replay do mesmo evento não duplica efeito;
- falha de reindexação não desfaz Summary canônico;
- projection stale fica observável.

---

## 4. D-003 — ASI usa `post_content`; conteúdo real pode estar no Elementor

**Evidência:** vários pipelines ASI interpretam `post_content`; KB2Ops possui extractor Elementor-aware read-only.

### Classificação

- Arquitetura: **CORRIGIDO PELA ARQUITETURA FUTURA** — Content Extraction único.
- Qualidade de implementação: **BLOCKER** para Search/RAG final enquanto R-KB-001 não for resolvido.

### Direção

`WP_Post/Elementor -> Content Extraction -> Search/Review/IA`.

Nenhum downstream pode reabrir `_elementor_data` ou `post_content` com parser próprio.

### Blocker específico

O extractor KB2Ops pode retornar saída parcialmente não vazia e omitir custom widget relevante sem acionar fallback renderizado. “Não vazio” não prova completude.

### Gate para deixar de ser blocker

- corpus representativo de fixtures Elementor reais;
- widgets relevantes mapeados;
- casos reconhecido + custom widget testados;
- omissão detectável/diagnosticável;
- Search regression prova que texto relevante está presente.

---

## 5. D-004 — múltiplos parsers/extractors e pipelines divergentes

**Origem:** ASI PostIndex/Item Knowledge/Structural Audit/Word Cloud + Search KB2Ops possuíam interpretações próprias.

### Classificação

- Parser duplicado: **DESCARTADO**.
- Direção futura: **CORRIGIDO PELA ARQUITETURA FUTURA**.
- Word Cloud/anchors históricos: **DEPENDE DE PREFLIGHT/PRODUTO** quanto à sobrevivência da feature, não quanto ao parser.

### Direção

- um único contrato de Content Extraction;
- Word Cloud, se existir, consome índice/analytics canônicos;
- deep-link/anchors consomem estrutura extraída e contrato de identidade, não parser paralelo.

### Gate de remoção

Não existe compatibilidade permanente para parser antigo. Qualquer código antigo só permanece fora do novo plugin até o cutover do consumidor correspondente.

### Regressão futura

- mesma fonte produz mesma representação versionada;
- Search/items/auditoria compartilham conteúdo-base;
- nenhum módulo cria parser lateral.

---

## 6. D-005 — GAC acoplado ao Search Intelligence ASI

**Evidência:** ASI conhece roles/tabelas GAC; GRE/KB2Ops não demonstram GAC como requisito intrínseco do produto.

### Classificação

**DEPENDE DE PREFLIGHT/REQUISITO EXTERNO**.

### Direção

- GAC fica fora do core;
- se requisito institucional real existir, integração ocorre por adapter opcional/degradável;
- indisponibilidade do GAC não derruba Search/core.

### Gate de decisão

Antes de criar adapter, comprovar:

1. qual informação GAC é realmente necessária;
2. quem consome;
3. contrato de acesso permitido;
4. comportamento sem GAC;
5. owner externo do dado.

### Se não houver consumidor/requisito

Classificar integração como **DESCARTADA** no T095.

---

## 7. D-006 — classificações GRE/KB2Ops duplicadas ou próximas

**Evidência:** audiência duplicada; serviço/serviço afetado; tecnologias/sistemas envolvidos; demais classificações distribuídas.

### Classificação

- Ownership: **CORRIGIDO PELA ARQUITETURA FUTURA** — Classificação de Conhecimento é owner único.
- Forma física/cutover: **DEPENDE DE PROFILING**.
- Migração inadequada/dual-write permanente: **BLOCKER de cutover**, não de documentação T054.

### Decisões já fechadas

- audiência é um conceito lógico único;
- `service` != `affected_service` até prova;
- `technologies` != `systems_involved` até prova;
- Resumo pode compor UI, mas não duplicar ownership;
- Search é leitor/indexador, não owner.

### Profiling obrigatório antes de migração

Para cada campo:

- cardinalidade por post;
- vocabulário real;
- valores vazios/sujos;
- equivalências e colisões;
- frequência de filtro/agrupamento;
- consumo por templates/shortcodes/relatórios;
- impacto de mudar key/primitive.

### Compatibilidade

Durante cutover, leitura de chaves antigas pode ser necessária. Preferir adapter/dual-read temporário; novo writer deve ser único quando o owner canônico entrar em produção.

### Gate de remoção

- dados migrados/validados;
- divergência monitorada = zero no período definido;
- consumidores antigos removidos;
- rollback conhecido.

---

## 8. D-007 — UI/CSS fragmentados entre ASI, GRE e KB2Ops

### Classificação

- Arquitetura: **CORRIGIDO PELA ARQUITETURA FUTURA** — um Design System/shell próprio, derivado dos princípios KB2Ops.
- CSS/markup antigos: **DESCARTADOS como contrato permanente**.
- Shortcodes/frontend históricos: **DEPENDEM DE PREFLIGHT** separadamente.

### Direção

- wp-admin continua shell;
- tokens/componentes próprios;
- sem segunda sidebar;
- server rendering baseline;
- CSS público namespaced;
- não preservar pixel legado quando contradiz o DS futuro, exceto compatibilidade de consumidor comprovada.

### Regressão futura

- acessibilidade/foco/responsividade;
- estados vazios/erro/permissão;
- mesma navegação visual entre domínios;
- aliases antigos, se existirem, renderizam componente canônico, não CSS antigo inteiro.

---

## 9. D-008 — AI READY: documentação KB2Ops diverge do runtime

**Runtime baseline:** `publish + approved + Resumo 8/8 + include_ai`.

### Classificação

**CORRIGIDO PELA ARQUITETURA FUTURA** por contrato único e teste explícito.

Não exige adapter complexo.

### Direção

- regra canônica única pertence a Revisão/Governança;
- `include_ai` é decisão humana e não pode ser inferido automaticamente;
- AI READY é derivado, não nova fonte canônica;
- mudança futura nos gates exige SPEC/regressão explícita.

### Gate

Antes de runtime:

- documentação e teste usam a mesma regra;
- cada condição possui caso positivo/negativo;
- Search scope `ai_ready` não pode expor artigo que falhe em um gate.

---

## 10. Compatibilidade de hooks/actions antigos

| Contrato histórico | Origem | Status T054 | Política |
|---|---|---|---|
| `bdc_es_loaded` | GRE | **DEPENDE DE PREFLIGHT** | não portar sem consumidor externo comprovado |
| `kb2ops_loaded` | KB2Ops | **DEPENDE DE PREFLIGHT** | idem |
| `bdc_es_objective_updated` | esperado pelo ASI, ausente no GRE | **COMPAT TEMPORÁRIO CONDICIONAL** | bridge apenas se ASI legado coexistir |
| `kb2ops_post_approved` | KB2Ops | **NÃO PORTAR LITERALMENTE** | futuro EVT-002 após confirmação; alias só por consumidor comprovado |
| extractor error hooks KB2Ops | KB2Ops | **MANTER INTENÇÃO / REDESENHAR** | observabilidade interna; nome final não congelado |
| `save_post` para indexar | ASI | **MANTER TRIGGER NATIVO, REDESENHAR PROCESSAMENTO** | invalidar barato; trabalho pesado bounded/async se necessário |
| `the_content` anchor injection | ASI | **DEPENDE DE PRODUTO/PREFLIGHT** | deep-link final ainda aberto |
| `site_status_tests` | ASI | **MANTER PRIMITIVE WP** | diagnostics futuros via Site Health quando adequado |

Nenhum hook interno futuro é considerado API pública estável sem decisão explícita.

## 11. Compatibilidade de shortcodes/superfícies

| Superfície histórica | Status | Regra |
|---|---|---|
| `[asi_search_form]` | **DEPENDE DE PREFLIGHT** | alias temporário somente se conteúdo real usar |
| `[bdc_word_cloud]` | **DEPENDE DE PREFLIGHT + decisão de produto** | não transportar pipeline antigo |
| `[bdc_resumo_executivo]` | **DEPENDE DE PREFLIGHT** | se alias existir, render current-post-only sobre store canônico |
| `[kb2ops_search]` | **DEPENDE DE PREFLIGHT** | apontar para Search canônica |
| `[kb2ops_portal]` | **DEPENDE DE PREFLIGHT** | idem |
| side panel automático GRE | **DESCARTADO COMO CANÔNICO** | eventual superfície futura será decisão de produto |
| menus/páginas antigas | **DESCARTADOS COMO NAVEGAÇÃO** | funções convergem no shell único |

### Preflight mínimo

Antes de criar alias:

- buscar uso em posts/pages/templates/widgets;
- identificar volume e criticidade;
- testar saída/atributos usados;
- definir owner canônico de render;
- definir telemetria ou inventário que permita confirmar desuso;
- registrar data/gate de remoção.

## 12. Compatibilidade de dados antigos

### Preservar obrigatoriamente até decisão/migração

- `WP_Post`, Elementor e taxonomias editoriais;
- oito valores históricos `_bdc_es_*`;
- review/include_ai/notas/revisor/histórico KB2Ops quando houver dados válidos;
- classificações KB2Ops utilizadas;
- vocabulary/bindings/relevance rules/Golden ASI quando houver registros manuais reais.

### Não transportar automaticamente

- `quality_daily`;
- migrations/reconciler/orchestrator históricos;
- analytics option/view count KB2Ops como segunda telemetria;
- caches/transients/snapshots reconstruíveis;
- listas hardcoded de cleanup legado.

### Telemetria histórica

Preservação não é automática. Depende de política de retenção, necessidade operacional/legal e sensibilidade. Não migrar query text apenas “porque existe”.

## 13. Blockers reais para SPEC-001 / slices futuros

### B-001 — Content Extractor representativo

**Severidade:** BLOCKER para Search/RAG/Indexing final.  
Resolver com corpus Elementor real, custom widgets e diagnóstico de omissão.

### B-002 — Profiling das classificações duplicadas/próximas

**Severidade:** BLOCKER para migração/cutover classificatório; não impede desenhar primitive em T056 com hipóteses marcadas.  
Resolver antes de dual-read/migração definitiva.

### B-003 — Preflight de consumidores externos/shortcodes

**Severidade:** BLOCKER para remover plugins/aliases antigos com segurança.  
Não exige portar tudo agora; exige saber o que está em uso antes do cutover.

### B-004 — Política de telemetria/query text

**Severidade:** BLOCKER antes de habilitar Analytics detalhado em produção.  
T057/T095 deve decidir minimização/retenção/acesso.

### B-005 — Estratégia de deep-link/anchors

**Severidade:** BLOCKER antes de declarar paridade completa de Item Knowledge/deep-link; não bloqueia Resumo/Classificação iniciais.

### B-006 — Semântica de falha multi-campo

**Severidade:** BLOCKER antes do write path definitivo de Summary/Classificação composto.  
Precisa de comportamento explícito para falha tardia e teste de read-after-write.

### B-007 — Estado stale/observabilidade de projections

**Severidade:** BLOCKER antes de Search index assíncrono/queue em produção.  
Se primeiro slice não possuir projection assíncrona, permanece não aplicável até tal slice.

## 14. Dívidas postergáveis, não blockers da SPEC-001 por si só

- Word Cloud como feature;
- GAC sem requisito comprovado;
- side panel automático;
- SPA/REST inexistentes;
- `quality_daily`;
- vetor/embeddings/Foundry;
- queue durável se primeiro slice não exigir;
- rollups/materializações de dashboard sem benchmark.

## 15. Gates de compatibilidade

Todo adapter/alias temporário deve registrar:

1. **entrada:** qual consumidor/dado exige sua existência;
2. **owner canônico:** onde está a fonte correta;
3. **modo:** read-only, tradução de evento ou alias de render; nunca novo owner;
4. **observabilidade:** como saber se ainda é usado;
5. **rollback:** como retornar ao legado durante homologação;
6. **remoção:** condição objetiva para excluir o adapter;
7. **teste:** regressão que prova equivalência durante coexistência.

Sem esses sete itens, compatibilidade não é aprovada.

## 16. Resultado de T054

- D-001–D-008 possuem classificação final de arquitetura/compatibilidade;
- compatibilidade não foi confundida com arquitetura permanente;
- hooks/shortcodes históricos não ganharam estabilidade automática;
- blockers reais foram separados de dívidas postergáveis;
- T056 pode agora decidir primitives WordPress sabendo quais conceitos precisam coexistir e quais contratos podem morrer.

**Próximo passo autorizado:** T056 — matriz WordPress-first campo/capacidade por campo/capacidade. Nenhum runtime foi criado.