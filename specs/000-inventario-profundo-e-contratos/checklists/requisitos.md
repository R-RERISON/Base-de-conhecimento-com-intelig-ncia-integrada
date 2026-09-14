# Checklist de Requisitos — SPEC-000

## Preparação

- [x] Repositórios registrados.
- [x] Baselines SHA registrados.
- [x] Constituição ratificada.
- [x] Manifesto criado.
- [x] Agentes/skills preparados.
- [x] Nenhum runtime novo criado.

## Inventário KB2Ops

- [ ] Bootstrap/lifecycle.
- [ ] Persistência.
- [ ] Hooks/rotas/actions.
- [ ] UI/Design System.
- [ ] Elementor extractor.
- [ ] Search atual.
- [ ] Migração/installer.
- [ ] Testes/build.
- [ ] Classificação completa.

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

- [ ] Catálogo de persistência. _(parcial ASI+GRE materializado; aguarda KB2Ops)_
- [ ] Catálogo de integrações. _(parcial ASI+GRE materializado; aguarda KB2Ops)_
- [ ] Mapa de ownership.
- [ ] Matriz de sobreposição.
- [ ] Drifts/contratos quebrados. _(D-001/D-002 confirmados; demais aguardam KB2Ops)_
- [ ] Catálogo de regressão. _(parcial ASI+GRE materializado; aguarda KB2Ops)_
- [ ] Matriz WordPress-first. _(preliminar ASI+GRE materializada)_
- [ ] Candidatos IA/vetor. _(preliminar materializada)_
- [ ] Matriz de paridade futura. _(parcial ASI+GRE materializada; aguarda KB2Ops)_

## Gate final

- [ ] Revisão Orquestrador.
- [ ] Revisão WordPress.
- [ ] Revisão Simplicidade.
- [ ] Revisão Segurança.
- [ ] Revisão QA/Regressão.
- [ ] Nenhum desconhecido crítico sem decisão.
- [ ] SPEC-001 autorizada formalmente.

## Evidência de fechamento do bloco ASI

- `inventario-asi.md` contém a decomposição de runtime, comportamento e classificação.
- `catalogo-persistencia.md` registra stores/options/transients e ownership preliminar.
- `catalogo-integracoes.md` registra hooks, cron, capabilities, AJAX, shortcodes e integrações.
- `catalogo-testes-regressao.md` registra a suíte/gates e contratos a portar.
- `matriz-paridade-futura.md` registra a paridade mínima e os itens ainda desconhecidos.
- `riscos-e-drifts.md` registra riscos e integrações a cruzar.

## Evidência de fechamento do bloco Gerenciador de Resumo Executivo

- `inventario-resumo-executivo.md` fixa baseline 0.6.0/SHA e decompõe as seis classes de runtime.
- as oito metas canônicas, `post_title` e o Summary Store foram inventariados.
- Admin Page, Coverage Dashboard, shortcode, side panel e CSS foram classificados.
- ausência de tabela/REST/AJAX/cron/options/transients próprios foi comprovada no runtime.
- suíte unitária, integração WordPress real, package smoke e build determinístico foram catalogados.
- D-001 (`Objective_Provider`) e D-002 (`bdc_es_objective_updated`) foram confirmados como contratos quebrados entre ASI e GRE.
- riscos de workload do dashboard e atomicidade multi-campo foram formalizados.
- nenhum runtime do novo plugin foi criado.