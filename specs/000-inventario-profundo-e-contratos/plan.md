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
7. [x] T057 — infraestrutura própria mínima;
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
- `infraestrutura-propria-minima.md`;
- `catalogo-testes-regressao.md`;
- `matriz-paridade-futura.md`;
- `riscos-e-drifts.md`.

## Resultado de T056

T056 aplicou a ordem WordPress-first antes de admitir infraestrutura própria.

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

## Resultado de T057

T057 partiu das três famílias candidatas deixadas por T056 e tentou reduzi-las antes de qualquer storage.

### F-057-01 — Search Retrieval Projection

**APROVADA E REDUZIDA.**

A limitação comprovada do Core está no retrieval lexical/item sobre representação Elementor derivada e ranking explicável. Em vez de reproduzir `search_index` + `search_items`, o baseline futuro deve tentar **um único store lógico/tabela própria de documentos derivados**, capaz de representar documentos `post` e `item`.

Invariantes:

- projection reconstruível;
- WordPress continua autoridade de publicação, scope e capability;
- Content Extractor canônico é a única fonte textual derivada;
- FULLTEXT dedicado quando suportado, com fallback lexical bounded;
- hashes/version/generation para rebuild/NO_CHANGE;
- B-001 antes de Search final;
- benchmark e Golden antes de produção;
- nenhum DDL foi criado em T057.

### F-057-02 — Analytics Facts

**NÃO APROVADA NO BASELINE / POSTERGADA.**

O valor gerencial é comprovado pelo ASI, mas B-004 ainda impede decidir finalidade, query text, minimização, retenção, acesso, correlação e volume. Portanto:

- zero tabela de events agora;
- zero interactions/outcomes agora;
- query text não é coletado por default no novo baseline;
- telemetria histórica não é migrada automaticamente;
- se a capacidade voltar, deve nascer como uma família coerente de facts, não como cópia das tabelas ASI.

### F-057-03 — Durable Job State

**NÃO APROVADA NO BASELINE / POSTERGADA.**

ASI prova qual semântica uma fila durável precisa ter, mas não existe benchmark do futuro extractor/index que prove necessidade de lease/retry/dead no primeiro slice. Primeiro testar:

- indexação de um post síncrona/bounded quando segura;
- rebuild manual/batched;
- WP-Cron apenas como trigger opcional;
- freshness/hash na própria projection.

Não improvisar queue em Options/Transients. Se workload futuro comprovar fila, reabrir F-057-03 com B-007.

### Redução final de infraestrutura

A história ASI possuía 12 stores próprios. Após T056/T057:

- **1 família própria aprovada:** Search Retrieval Projection;
- **0 Analytics stores aprovados** no baseline;
- **0 queue stores aprovados** no baseline;
- Search Knowledge/Golden continuam em primitives WP;
- Summary/Classificação/Review/Settings/Health continuam no Core.

## Regra específica após T057

O próximo trabalho não é schema: é **T055 — regressão e Golden**.

T055 deve converter decisões arquiteturais em gates verificáveis, especialmente:

- extractor único e read-only;
- projection post/item unificada e determinística;
- rebuild/idempotência/freshness;
- FULLTEXT + fallback bounded;
- scope/detail recheck no WordPress;
- Golden Queries como gate de ranking;
- Search funcional sem Analytics;
- ausência de query logging silencioso no baseline;
- baseline sem durable queue;
- ativação sem rebuild massivo.

## Gate de conclusão

Nenhuma SPEC de runtime começa enquanto existir item crítico sem decisão explícita de resolver/postergar, risco documentado e gate aplicável. T097 é a única autorização formal para SPEC-001.