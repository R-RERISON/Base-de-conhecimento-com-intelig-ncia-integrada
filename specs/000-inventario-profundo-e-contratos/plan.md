# Plano — SPEC-000 Inventário Profundo e Contratos

## Objetivo

Ler, decompor e cruzar os três repositórios de referência em contratos verificáveis antes de qualquer runtime novo.

## Agentes convocados

- Orquestrador Principal;
- Arquiteto WordPress;
- Arquiteto de Conhecimento;
- Especialista em Busca e Retrieval;
- Especialista em MariaDB e Dados;
- Especialista em IA/Foundry;
- Especialista em Segurança WordPress;
- Especialista em UI/UX WordPress;
- Especialista em Qualidade e Regressão;
- Especialista em Performance/Observabilidade;
- Crítico de Simplicidade.

## Estratégia executada

### Fases A–E — inventário

Concluídas para KB2Ops, ASI e GRE:

- topologia/runtime;
- persistência;
- integrações WordPress;
- fluxos de produto;
- testes/regressão/build.

### Fase F — classificação

Cada item foi classificado como:

`MANTER | REDESENHAR | SUBSTITUIR POR WORDPRESS | EVOLUIR COM IA/VETOR | DESCARTAR | AINDA NÃO SABEMOS`.

### Fase G — cruzamento

Estado:

1. [x] T052 — ownership lógico;
2. [x] T053 — sobreposição funcional;
3. [x] T050 — persistência consolidada por conceito;
4. [x] T051 — integrações consolidadas por contrato;
5. [x] T054 — drifts, compatibilidade, preflight e blockers;
6. [ ] T056 — WordPress-first;
7. [ ] T057 — infraestrutura própria mínima;
8. [ ] T055 — regressões/Golden finais;
9. [ ] T058 — IA/vetor priorizados;
10. [ ] T059 — paridade futura final.

## Artefatos centrais produzidos

- `inventario-kb2ops.md`;
- `inventario-asi.md`;
- `inventario-resumo-executivo.md`;
- `mapa-ownership-dados.md`;
- `matriz-sobreposicoes.md`;
- `catalogo-persistencia.md`;
- `catalogo-integracoes.md`;
- `mapa-contratos-quebrados.md`;
- `catalogo-testes-regressao.md`;
- `matriz-paridade-futura.md`;
- `riscos-e-drifts.md`.

## Regra específica após T054

Nenhuma primitive física deve ser escolhida por medo de compatibilidade.

T056 deve perguntar primeiro:

- WordPress Metadata resolve?
- Taxonomy API resolve?
- Options/Settings resolve?
- Revisions resolvem?
- admin-post resolve?
- Site Health resolve?
- WP-Cron resolve como trigger?
- transient/object cache resolve?

Somente o que falhar de forma comprovada segue para T057.

Compatibilidade não altera essa ordem: adapter/alias é temporário e não define arquitetura futura.

## Gate de conclusão

Nenhuma SPEC de runtime começa enquanto existir item crítico sem decisão explícita de resolver/postergar, risco documentado e gate aplicável. T097 é a única autorização formal para SPEC-001.