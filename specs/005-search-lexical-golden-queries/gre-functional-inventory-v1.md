# P-580B — Inventário Funcional GRE → BDC v1

**Status:** OPEN / INVENTORY  
**Data:** 2026-09-20  
**Baseline:** `R-RERISON/Gerenciador-de-Resumo-Executivo-da-Base-de-Conhecimento` main  
**Versão observada:** 0.6.0

## 1. Regra

O Gerenciador de Resumo Executivo é baseline funcional especializada.

A absorção pelo BDC exige:
- preservação ou melhoria de capacidades;
- owner canônico por dado;
- zero duplicação desnecessária;
- frontend público preservado;
- gestão administrativa integrada ao Knowledge Workspace;
- retirada do plugin somente após paridade comprovada.

## 2. Capacidades GRE verificadas

### Persistência
- oito campos estruturados via WordPress Metadata API;
- post_title permanece nativo;
- leitura side-effect free;
- atualização parcial;
- sanitização;
- capability por objeto;
- REST fechado;
- zero tabela customizada.

### Campos GRE
1. Objetivo;
2. Equipe responsável;
3. Item de Catálogo;
4. Serviço Afetado;
5. Sistemas envolvidos;
6. Público Alvo;
7. Escalonamento;
8. IMPORTANTE.

### Administração
- lista de posts;
- busca;
- estado Sem resumo / Parcial / Completo;
- editor focado;
- toolbar sticky;
- cobertura/KPIs;
- prioridades de curadoria.

### Frontend
- painel lateral automático;
- 0/8 => nenhuma saída;
- 1/8+ => painel;
- read-only;
- título do post nativo;
- painel permanece visível;
- scroll interno para resumo extenso;
- viewport menor adaptativa;
- shortcode explícito como fallback;
- anti-duplicação;
- zero dependência Elementor.

## 3. Mapping GRE → BDC atual

| Capacidade/campo | Owner BDC atual | Estado |
|---|---|---|
| título | WP_Post.post_title | PARITY |
| objetivo | Summary / `_bdc_es_objective` | PARITY |
| escalonamento | Summary / `_bdc_es_escalation` | PARITY |
| importante | Summary / `_bdc_es_important` | PARITY |
| equipe responsável | Classification / `bdc_kb_responsible_team` | IMPROVED owner |
| item de catálogo | Classification / `bdc_kb_catalog_item` | IMPROVED owner |
| público alvo | Classification / `bdc_kb_audience` | IMPROVED owner |
| serviço afetado | sem owner canônico | MISSING |
| sistemas envolvidos | sem owner canônico | MISSING |
| painel público | apenas addendum/heritage, sem runtime BDC | MISSING/BLOCKING |
| coverage dashboard | sem substituição consolidada | MISSING/PLANNED |
| editor focado | Knowledge Workspace cobre parte da jornada | PARTIAL |
| shortcode fallback | não requerido como arquitetura final; compatibilidade a decidir | INVENTORY |

## 4. Decisão sobre o rail público

O BDC não deve reconstruir um segundo Summary_Store de oito campos.

O **Executive Summary Rail** será um read model composto:

- título: WP_Post;
- objetivo/escalonamento/importante: Summary;
- equipe/item/audiência: Classification;
- serviço afetado/sistemas envolvidos: novos owners somente após contrato explícito;
- campos vazios omitidos;
- nenhuma duplicação persistente apenas para renderização.

## 5. Comportamento público obrigatório

- automático nos artigos BDC;
- read-only;
- acompanha a leitura em desktop largo;
- header permanece visível;
- corpo possui scroll próprio apenas quando necessário;
- não cobre conteúdo;
- reflow em viewport menor;
- 0 campos estruturados => rail ausente;
- sem JS obrigatório quando CSS resolve;
- print converte para fluxo estático;
- sem writer no frontend.

## 6. Coverage / gestão

Antes de aposentadoria do GRE, BDC precisa oferecer:
- cobertura de dados estruturados por post;
- estados sem/parcial/completo ou equivalente melhor;
- prioridades de curadoria;
- acesso à edição dentro do Workspace;
- métricas derivadas, read-only;
- sem necessidade de plugin paralelo.

## 7. Cutover

GRE só pode ser desativado quando:
- metadados relevantes estiverem mapeados/migrados ou consumidos pelo owner BDC;
- painel BDC estiver homologado;
- gestão/coverage equivalente estiver disponível;
- nenhum shortcode/template ativo depender do GRE;
- rollback/evidência estiverem definidos.
