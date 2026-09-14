# Checklist de Requisitos — SPEC-000

## Preparação

- [x] Repositórios registrados.
- [x] Baselines SHA registrados.
- [x] Constituição ratificada.
- [x] Manifesto criado.
- [x] Agentes/skills preparados.
- [x] Nenhum runtime novo criado.

## Inventário KB2Ops

- [x] Bootstrap/lifecycle.
- [x] Persistência.
- [x] Hooks/rotas/actions.
- [x] UI/Design System.
- [x] Elementor extractor.
- [x] Search atual.
- [x] Migração/installer/uninstall.
- [x] Testes/build/release.
- [x] Classificação completa.

## Inventário ASI

- [x] Bootstrap/lifecycle.
- [x] Schema completo.
- [x] Search/ranking.
- [x] Item knowledge/anchors.
- [x] Vocabulary/bindings/rules.
- [x] Telemetry/outcomes/privacy.
- [x] Queue/migrations/operations.
- [x] Golden Queries/quality.
- [x] Word Cloud.
- [x] Legacy/compat.
- [x] Testes/build/release.
- [x] Classificação completa.

## Inventário Resumo Executivo

- [x] Bootstrap/lifecycle.
- [x] Meta contract.
- [x] Store.
- [x] Admin/coverage.
- [x] Renderer/shortcode.
- [x] Assets.
- [x] Testes/build.
- [x] Provider/event drift confirmado.
- [x] Classificação completa.

## Cruzamento

- [ ] Catálogo de persistência consolidado em decisão final. _(T050; próximo bloco)_
- [ ] Catálogo de integrações consolidado em decisão final. _(T051; próximo bloco)_
- [x] Mapa de ownership. _(`mapa-ownership-dados.md`)_
- [x] Matriz de sobreposição. _(`matriz-sobreposicoes.md`)_
- [ ] Drifts/contratos quebrados consolidados.
- [ ] Catálogo de regressão/Golden futuro consolidado.
- [ ] Matriz WordPress-first final.
- [ ] Infraestrutura própria mínima justificada.
- [ ] Candidatos IA/vetor priorizados.
- [ ] Matriz de paridade futura final.

## Gate final

- [ ] Revisão Orquestrador.
- [ ] Revisão WordPress.
- [ ] Revisão Simplicidade.
- [ ] Revisão Segurança.
- [ ] Revisão QA/Regressão.
- [ ] Revisão Produto/Conhecimento.
- [ ] Nenhum desconhecido crítico sem decisão.
- [ ] Relatório final SPEC-000.
- [ ] SPEC-001 autorizada formalmente.

## Evidência de fechamento do bloco ASI

- `inventario-asi.md` decompõe runtime, schema, busca, item knowledge, curadoria, telemetria, fila, Golden, Word Cloud, legacy e gates.
- contratos fortes e complexidade histórica foram separados.

## Evidência de fechamento do bloco GRE

- `inventario-resumo-executivo.md` fixa baseline e seis classes.
- oito metas, Summary Store, Admin/Coverage, Renderer/assets e testes foram classificados.
- D-001/D-002 foram confirmados como contratos quebrados.

## Evidência de fechamento do bloco KB2Ops

- `inventario-kb2ops.md` fixa `0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94` e decompõe runtime completo.
- Content Extractor Elementor-aware foi documentado como contrato read-only, com gap de extração parcial/custom widgets formalizado.
- todas as `_kb2ops_*`, view count, options, ausência de tables/cron/REST/AJAX e uso de categorias nativas foram mapeados.
- Summary Bridge foi comparada ao GRE e classificada como compat read-only a desaparecer no bounded context unificado.
- Knowledge Studio, Search, Analytics, Reports e AI READY foram classificados.
- drift `AI READY` docs/runtime foi registrado.
- Design System foi decomposto em princípios, tokens, componentes, responsive e assets.
- Installer/Migration/Uninstall foram classificados, preservando apenas o princípio reversível.
- release gate e build determinístico foram inventariados; ausência de suíte executável versionada foi registrada como dívida.

## Evidência de fechamento T052 — Ownership

- `mapa-ownership-dados.md` separa editorial, resumo, classificação, revisão, extraction, Search Knowledge, Search Indexing, Search Quality, Analytics, Configuração, Operações e AI Assist.
- audiência GRE/KB2Ops passou a ter um único owner lógico: Classificação de Conhecimento.
- serviço/serviço afetado e tecnologias/sistemas foram mantidos distintos até profiling, evitando merge por nome.
- Search/projections/Analytics não recebem ownership sobre metadata editorial/classificatória.
- migrations/adapters foram explicitamente proibidos de virar owner permanente.
- storage físico permanece deliberadamente aberto para T056/T057.

## Evidência de fechamento T053 — Sobreposição

- `matriz-sobreposicoes.md` classifica duplicação real, complementaridade, compat/transição e referências de implementação.
- Resumo, Search, Classificação, Analytics e Design System tiveram convergência funcional definida.
- aprovação de artigo e Apply de Search Knowledge permanecem workflows separados.
- qualidade de conteúdo e Search Quality permanecem métricas/domínios separados.
- área única de Insights foi definida como navegação, sem autorizar agregação cara ou score global.
- nenhuma tela/runtime novo foi criado.

## Estado

Inventário individual e cruzamento inicial T052/T053 concluídos. Próximo gate: **T050/T051**, consolidando persistência e integrações com base nos owners e nas sobreposições agora resolvidas.
