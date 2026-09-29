# G-590 — Security & Performance Addendum v1

**Status:** FROZEN PARA HOMOLOGAÇÃO  
**Relaciona:** G-570 + G-590

## Objetivo

G-570 continua sendo o contrato post-level. Este addendum mede somente o overhead introduzido por:
- leitura bounded de `sections_json`;
- decode da Section Projection;
- ranking section-level;
- composição de deep-link.

## Segurança

Obrigatório:
- Search parent continua owner de capability/status;
- Section Service recebe apenas parents já autorizados;
- nenhuma query raw vinda do request;
- nenhuma chamada externa;
- nenhuma escrita editorial;
- nenhuma dependência ASI;
- candidate retrieval post-level não carrega `sections_json`;
- máximo 20 parents;
- máximo 64 sections/post;
- máximo 5 sections retornadas/post.

## Benchmark ambiental

Dataset:
- até 6 queries derivadas de headings únicos publicados do corpus;
- 1 warmup/query;
- 5 medições/query;
- mesma engine/projection usada no runtime.

Budgets G-590 v1:
- p95 <= 900 ms;
- max <= 1500 ms;
- zero technical error;
- cada query de benchmark deve produzir ao menos uma Section result.

Os budgets são deliberadamente mais conservadores que o baseline G-570 (~208 ms p95 observado no ambiente anterior), pois incluem uma segunda leitura bounded e ranking adicional.

## Lifecycle 1.0 → 1.1

Se o estado anterior não estiver nas versões correntes:
1. `prepare_schema()` pode alterar somente schema/Option de estado;
2. não pode reconstruir corpus;
3. estado deve ficar `degraded`;
4. Search post-level usa fallback;
5. rebuild só ocorre por ação explícita;
6. após rebuild, estado deve ficar `ready`;
7. pass2 deve ser NO_CHANGE/determinístico.

Se a instalação já estiver 1.1/ready, rerun não precisa forçar degraded artificialmente.

## NO-GO

- budget excedido;
- technical error;
- implicit rebuild;
- migration com coluna ausente;
- versão antiga mantida como ready;
- mutation editorial;
- query path de 200 candidates carregando Section JSON.
