# Pesquisa Inicial — SPEC-000

## Objetivo

Registrar fatos já observados antes da leitura exaustiva. Tudo aqui deve ser confirmado contra runtime durante a execução da SPEC.

## KB2Ops 0.2.1

Fatos iniciais observados:

- shell e Design System recentes;
- Knowledge Studio;
- Knowledge Search;
- Content Extractor com tratamento Elementor;
- metadados `_kb2ops_*` para revisão/classificação;
- leitura direta dos oito `_bdc_es_*` via bridge;
- ativação hardened/reversível;
- busca atual conscientemente provisória.

Hipóteses a validar:

- quais metas devem virar taxonomia;
- quais analytics leves possuem valor futuro;
- quais componentes visuais devem virar contrato novo;
- quais rotas/handlers merecem regressão.

## ASI 4.6.8

Fatos iniciais observados:

- 12 stores/tabelas canônicas;
- busca lexical/FULLTEXT;
- índice de posts e itens;
- vocabulário, bindings e regras;
- fila durável;
- migrações/reconciler/orchestrator;
- Search Events/Interactions/Outcomes;
- Golden Queries;
- Quality Diagnostics;
- Word Cloud;
- modos de privacidade;
- extensa suíte de testes;
- camada legacy/compat ainda presente para migração/cutover.

Hipóteses a validar:

- quais tabelas são realmente necessárias no produto de segunda geração;
- quais telas administrativas são apenas consequência da arquitetura antiga;
- quais contratos de ranking são indispensáveis;
- quais módulos opcionais ainda têm consumidores reais;
- quais testes devem ser portados como comportamento e quais são acoplados à implementação.

## Gerenciador de Resumo Executivo 0.6.0

Fatos iniciais observados:

- seis classes principais no runtime;
- oito post metas canônicos;
- Metadata API;
- Summary Store;
- Coverage Dashboard;
- Admin Page;
- Frontend Renderer;
- sem tabela própria para domínio central.

Gap inicial:

ASI referencia `BDC\\ExecutiveSummary\\Objective_Provider`, mas esse provider não aparece no bootstrap `0.6.0` lido. Confirmar se há drift de versão/documentação ou contrato ausente.

## Decisões ainda não tomadas

- taxonomias definitivas do novo produto;
- schema lexical;
- necessidade de tabela de chunks;
- estratégia vetorial;
- provider contract de IA;
- retenção de telemetria;
- mecanismo de histórico de revisão;
- estratégia de compatibilidade com meta `_kb2ops_*`;
- estratégia de coexistência do índice `asi_*`.

Nenhuma dessas decisões deve ser fechada antes do inventário correspondente.