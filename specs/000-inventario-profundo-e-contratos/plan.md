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
6. [x] T056 — WordPress-first por conceito/capacidade;
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
- `matriz-wordpress-first.md`;
- `catalogo-testes-regressao.md`;
- `matriz-paridade-futura.md`;
- `riscos-e-drifts.md`.

## Resultado de T056

T056 aplicou a ordem WordPress-first antes de admitir qualquer infraestrutura própria.

### Resolvido com WordPress Core

- Editorial: `WP_Post` + Elementor read-only.
- Resumo narrativo: Metadata API.
- Revisão/Governança: Metadata API + WP Users + Revisions quando aplicável.
- Classificação: Metadata/Taxonomy conforme semântica e padrão de consulta; nenhum campo justificou tabela própria.
- Search Knowledge/Golden: `WP_Post` interno + Metadata/Revisions como baseline enquanto volume permanecer governado/baixo.
- Configuração: Settings/Options.
- Segurança: Users/Roles/Capabilities + Nonces.
- Mutação administrativa: `admin-post`.
- Live UX: AJAX somente se necessário.
- REST: nenhum endpoint sem consumidor formal.
- Health: Site Health.
- Cache: Transients/Object Cache, sempre reconstruível.
- Scheduling: WP-Cron como trigger.
- Coverage/read models simples: `WP_Query` bounded + cache quando medido.

### Classificações

Taxonomy foi preferida apenas onde a baseline demonstra reutilização/filtro/faceta:

- audiência;
- tipo de conhecimento;
- serviço;
- tecnologias.

Permanecem entre Metadata/Taxonomy, sob B-002:

- equipe responsável;
- item de catálogo;
- serviço afetado;
- sistemas envolvidos.

`keywords` e `versions` permanecem Metadata no baseline T056 por ausência de requisito comprovado de faceta global.

### Candidatos que restaram para T057

1. `F-057-01` — Search Retrieval Projection: documento lexical + itens/identidade pesquisável, sem schema pré-escolhido.
2. `F-057-02` — Analytics Facts: **condicional** a B-004 e requisito real de eventos/interações/outcomes.
3. `F-057-03` — Durable Job State: **condicional** a workload assíncrono que realmente exija lease/retry/dead/recovery.

“Candidato” não significa “aprovado”. T057 deve tentar matar/reduzir cada família antes de desenhar storage.

## Regra específica após T056

T057 deve começar pelo workload e pela limitação, nunca pelo schema.

Para cada `F-057-*`:

1. qual comportamento não cabe no Core;
2. qual volumetria/latência/SLA existe;
3. qual consulta precisa ser eficiente;
4. qual durabilidade/concorrência é necessária;
5. o que é reconstruível;
6. qual é a menor extensão possível;
7. como a extensão degrada/recupera;
8. se a capacidade pode simplesmente não nascer no primeiro slice.

É proibido usar as 12 tabelas do ASI como checklist de implementação.

## Gate de conclusão

Nenhuma SPEC de runtime começa enquanto existir item crítico sem decisão explícita de resolver/postergar, risco documentado e gate aplicável. T097 é a única autorização formal para SPEC-001.
