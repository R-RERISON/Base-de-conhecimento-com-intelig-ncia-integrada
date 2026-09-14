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

- [x] Catálogo de persistência consolidado por owner/conceito. _(T050)_
- [x] Catálogo de integrações consolidado por contrato futuro. _(T051)_
- [x] Mapa de ownership. _(T052)_
- [x] Matriz de sobreposição. _(T053)_
- [ ] Drifts/contratos quebrados consolidados. _(T054 — próximo)_
- [ ] Catálogo de regressão/Golden futuro consolidado. _(T055)_
- [ ] Matriz WordPress-first final. _(T056)_
- [ ] Infraestrutura própria mínima justificada. _(T057)_
- [ ] Candidatos IA/vetor priorizados. _(T058)_
- [ ] Matriz de paridade futura final. _(T059)_

## Evidência T050 — Persistência

- catálogo deixou de ser organizado por plugin histórico e passou a ser organizado por owner/conceito;
- canônicos, projections, observacionais, operacionais, configuração e compatibilidade estão separados;
- chaves `_bdc_es_*`, `_kb2ops_*` e stores `asi_*` são rastreabilidade histórica, não owners futuros;
- audiência possui um único owner lógico;
- `service`/`affected_service` e `technologies`/`systems_involved` permanecem distintos até profiling;
- dual-write permanente foi proibido;
- adapters/dual-read só podem ser temporários e possuir gate de remoção;
- projections Search/AI não são fonte da verdade;
- não foi criada taxonomy, tabela, schema ou migration.

## Evidência T051 — Integrações

- contratos cross-module foram definidos com produtor, consumidor, pré-condição, falha e idempotência preliminar;
- evento de domínio só ocorre após persistência confirmada;
- Summary/Classificação/Review invalidam projections sem transferir ownership;
- Apply de Search Knowledge continua separado de aprovação do artigo;
- Content Extraction é contrato interno único para Search/Review/IA;
- server-rendered/admin-post ficou como baseline; AJAX somente para live UX comprovada; REST sem consumidor foi negado;
- shortcodes/hooks históricos foram classificados como compatibilidade a provar, descartados ou substituídos conceitualmente;
- Analytics foi definido como non-fatal para Search;
- queue continua não aprovada até T057;
- nenhuma rota/hook final de runtime foi implementada.

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

## Estado

T050–T053 concluídas documentalmente. Próximo passo autorizado: **T054 — mapa final de contratos quebrados/drifts/compatibilidade/blockers**. Nenhum runtime novo existe.