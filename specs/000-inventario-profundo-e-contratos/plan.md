# Plano — SPEC-000 Inventário Profundo e Contratos

## Objetivo

Ler, decompor, cruzar e revisar os três projetos de referência em contratos verificáveis antes de qualquer runtime novo.

## Inventário/cruzamento — concluído

- [x] inventários KB2Ops/ASI/GRE;
- [x] T050–T059 consolidação arquitetural/documental.

## Revisões finais

- [x] **T090 — Arquiteto WordPress**.
- [ ] **T091 — Crítico de Simplicidade**.
- [ ] **T092 — Segurança**.
- [ ] **T093 — QA/Regressão**.
- [ ] **T094 — Produto/Conhecimento**.
- [ ] **T095 — unknowns/blockers por slice**.
- [ ] **T096 — relatório final**.
- [ ] **T097 — GO/NO-GO SPEC-001**.

## Arquitetura consolidada

`matriz-paridade-futura.md` é a visão executiva final T059. Ela preserva WordPress-first, vertical slices, owner único, Search lexical independente de IA, uma única Search Retrieval Projection própria e capacidades avançadas postergadas até evidência.

## Resultado T090 — WordPress-first

Artefato: `revisao-wordpress-t090.md`.

Status: **PASS com simplificações/investigações não bloqueantes**.

A revisão confirmou:

- WordPress/Elementor como fonte editorial;
- Metadata API para Summary/Review;
- Taxonomy/Metadata para Classificação;
- Options/Settings para configuração;
- Site Health para diagnóstico;
- admin-post como baseline de mutação administrativa;
- AJAX somente quando live UX exigir;
- REST negado sem consumidor;
- WP-Cron como scheduler/trigger, não durable queue;
- HTTP API como primeira opção para provider externo;
- Search Retrieval Projection própria ainda justificada.

### Simplificações T090

1. Não manter histórico bounded e meta revisions concorrentes sem requisito real.
2. Search Knowledge/Golden devem começar como entidades internas WordPress-first; não criar tabela/admin CRUD próprio.

### Investigações T090

1. Fixar versão mínima WordPress antes de depender de `revisions_enabled` (introduzido no Core 6.4).
2. Taxonomias sistêmicas não devem ganhar archive/rewrite público por default.
3. Endpoint de provider configurável deve passar revisão SSRF/host allowlist em T092/SPEC de IA.

Nenhum finding bloqueante foi encontrado.

## Dados/cutover

Continuam vigentes as decisões T059 de preservação de editorial, oito valores GRE, Review/Classificação KB2Ops usados e Search Knowledge/Golden ASI reais. Projections/telemetria/queue/cache não são migração canônica automática.

## Próximo passo — T091

O Crítico de Simplicidade deve assumir que toda capacidade é removível até provar necessidade e tentar reduzir:

- stores;
- entidades;
- telas;
- endpoints;
- compatibilidade;
- schedulers;
- Search avançada;
- IA/vetor;
- abstrações antecipadas.

Classificação de finding: `MANTER | SIMPLIFICAR | POSTERGAR | DESCARTAR | BLOQUEAR`.

## Gate

Nenhuma SPEC de runtime começa antes de T097. T090 não autorizou código nem congelou detalhes físicos de implementação.
