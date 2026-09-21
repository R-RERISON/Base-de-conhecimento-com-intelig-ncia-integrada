# G-570 — Security & Performance Contract v1

**Status:** FROZEN para execução do gate  
**Data:** 2026-09-19  
**Escopo:** SPEC-005 Search lexical ADMIN-FIRST  
**Ranking:** congelado; nenhuma alteração de pesos/retrieval é autorizada neste gate.

## Objetivo

Comprovar que a Search lexical aprovada em G-550/G-560:

1. respeita capability/scope do WordPress;
2. mantém SQL e cardinalidades bounded;
3. falha de forma segura diante de entradas abusivas/malformadas;
4. possui latência ambiental compatível com uso administrativo interativo;
5. não introduz write editorial, query logging, rede, ASI ou FULLTEXT.

## T570 — Scope / Capability

Obrigatório:

- Search exige `edit_posts`;
- negação de `edit_posts` => `technical_error/search_forbidden`;
- Projection nunca autoriza acesso;
- cada objeto retornado exige `edit_post(post_id)`;
- documento negado por capability não pode aparecer no resultado final;
- autorização ocorre antes do ranking final;
- fallback usa `perm=editable`;
- post type permanece `Meta_Contract::POST_TYPE`;
- status permanecem allowlisted;
- nenhuma informação de documento descartado é exportada.

Validação ambiental negativa deve usar filtros temporários de capability e removê-los no mesmo processo; não criar usuário e não persistir alteração de permissão.

## T571 — SQL / Bounds

Query:
- <=256 caracteres Unicode;
- <=1024 bytes;
- <=16 tokens distintos;
- <=128 caracteres/token;
- sem truncamento silencioso.

Retrieval:
- candidate hard cap: 200;
- result hard cap: 50;
- default result: 20;
- `$wpdb->prepare()` para valores;
- `$wpdb->esc_like()` para LIKE;
- nomes de campo somente allowlist de código;
- tabela derivada internamente do `$wpdb->prefix`;
- sem fragmento SQL vindo do request;
- sem FULLTEXT/MATCH AGAINST no v1;
- sem SQL/tabela ASI.

Runtime deve comprovar:
- pedido de candidate cap acima do limite não retorna >200;
- pedido de result limit acima do limite não retorna >50;
- limit <=0 é clampado para 1;
- payload SQL-like/wildcard não causa erro SQL nem expande cardinalidade além dos bounds.

## T572 — Abuse / Invalid Input

Casos mínimos:
- tipo não-string;
- query vazia/pontuação-only;
- 257 caracteres;
- >16 tokens;
- token >128 caracteres;
- payload HTML/script;
- payload com metacaracteres SQL/LIKE;
- controles/NUL.

Critério:
- excedentes/malformados => `invalid_query` com error_code estável;
- entradas sanitizáveis não produzem HTML executável;
- nenhum stack trace/SQL/raw editorial em erro;
- nenhum write;
- nenhum fatal/throwable.

## T573 — Performance

### Dataset curado

Consultas:
- `Windows 11`;
- `SCCM`;
- `Termo de assinatura`;
- `essencialmente`;
- `phising`;
- `bdczzzznomatch20260919`.

As consultas são fixtures técnicas/curadas; não representam telemetria de usuário.

### Metodologia

- Projection deve estar `ready`;
- 1 warm-up por consulta, não medido;
- 5 repetições medidas por consulta;
- total: 30 amostras;
- `microtime(true)` em torno de `Search_Service::search()`;
- registrar p50/p95/max global e por consulta;
- registrar delta de `$wpdb->num_queries` apenas como diagnóstico;
- nenhum resultado pode usar `wordpress_fallback`.

### Budget de gate

Este budget é guardrail de homologação para o corpus atual (~623 posts), **não SLA de produção**.

- p95 global <= **750 ms**;
- max global <= **1500 ms**;
- 0 fallback nas amostras;
- 0 technical_error;
- 0 throwable.

Se o ambiente estiver comprovadamente degradado externamente, o gate permanece FAIL/OPEN; o budget não é relaxado por inferência.

## Segurança editorial

Fingerprint antes/depois inclui:
- title/excerpt/content/status/modified;
- Summary;
- `_elementor_data`;
- taxonomias canônicas.

Obrigatório:
- fingerprint idêntico;
- zero query logging;
- zero rede;
- zero ASI;
- zero write editorial.

## Resultado

G-570 PASS somente quando:
- T570 PASS;
- T571 PASS;
- T572 PASS;
- T573 PASS;
- errors=[];
- throwables=[];
- fingerprint editorial igual.

G-570 não autoriza produção nem merge. Próximo gate após PASS: G-580 Lifecycle.
