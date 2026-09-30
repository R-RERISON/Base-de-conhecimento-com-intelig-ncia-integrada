# P-660 — Security / Privacy Baseline Contract v1

**Status:** IMPLEMENTATION  
**Data:** 2026-09-30

## Objetivo

Transformar os findings de segurança/privacidade já inventariados no P-620 em um baseline de produção verificável sobre o mesmo ZIP homologado no P-650, priorizando risco real e correção mínima.

## Escopo

P-660 cobre:

- entrada de usuário e superglobals;
- `wp_unslash()` + sanitização;
- capability e nonce em mutações;
- escaping contextual;
- SQL preparado / ownership de consultas diretas;
- uso de HTTP externo e SSRF;
- secrets no source/package;
- minimização de dados;
- retenção/telemetria quando aplicável;
- exportações/diagnósticos;
- superfícies administrativas e públicas distribuídas.

## Fora de escopo

- IA;
- semantic search;
- redesign;
- alteração de ranking;
- migração editorial;
- cutover;
- retirement de plugins legados.

## Princípios

1. corrigir por risco e consumer path, não por contagem bruta;
2. não aplicar PHPCBF/bulk rewrite;
3. não criar camada própria quando WordPress Core já oferece primitive;
4. nonce não substitui capability;
5. prepared statements obrigatórios para SQL dinâmico;
6. escaping ocorre no contexto de saída;
7. nenhuma mutation via GET;
8. secrets fora do source;
9. HTTP externo somente com WordPress HTTP API e allowlist/validation quando necessário;
10. evidência deve ser local e reproduzível; GitHub Actions não é executor de gate.

## Baseline herdada do P-620

Findings roteados a P-660: 150.

Códigos dominantes observados:

- `WordPress.Security.ValidatedSanitizedInput.InputNotSanitized`;
- `WordPress.Security.ValidatedSanitizedInput.MissingUnslash`;
- `WordPress.Security.NonceVerification.Missing`;
- `WordPress.Security.NonceVerification.Recommended`;
- `WordPress.DB.DirectDatabaseQuery.DirectQuery`;
- `WordPress.DB.DirectDatabaseQuery.NoCaching`;
- `WordPress.DB.PreparedSQL.NotPrepared`;
- `WordPress.Security.EscapeOutput.OutputNotEscaped`.

## Estratégia

### P660-01 — inventory do ZIP homologado

Inventariar:

- arquivos que leem superglobals;
- presença/ausência de nonce/capability nos mesmos consumers;
- consultas `$wpdb`;
- saídas diretas;
- chamadas HTTP;
- possíveis secrets;
- exports/downloads.

### P660-02 — classificar por risco

Estados:

- `MUTATION_HIGH`;
- `READ_MEDIUM`;
- `OUTPUT_MEDIUM`;
- `DB_HIGH`;
- `NETWORK_HIGH`;
- `SECRET_CRITICAL`;
- `FALSE_POSITIVE_OR_JUSTIFIED`.

### P660-03 — remediação por slices

Ordem:

1. mutation handlers;
2. SQL dinâmico;
3. escaping;
4. public input;
5. exports;
6. privacy/data minimization.

### P660-04 — regressão

Cada slice exige:

- PHP lint;
- WPCS afetado quando tooling estiver disponível;
- teste estático/funcional pertinente;
- nenhum novo endpoint;
- nenhuma mudança de domínio.

### P660-05 — closeout

P-660 só fecha com:

- findings críticos/altos resolvidos ou waiver explícito;
- secrets = 0;
- mutation sem capability/nonce = 0;
- SQL dinâmico não preparado = 0;
- evidence versionada;
- package final revalidado.

## Rollback

Toda alteração P-660 deve ser reversível por commit e não pode alterar schema/dados.
