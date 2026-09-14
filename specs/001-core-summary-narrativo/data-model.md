# Modelo de Dados — SPEC-001 Core mínimo + Summary narrativo

## Entidade alvo

WordPress `WP_Post` com `post_type = 'post'`.

O post é fonte da verdade editorial. Esta SPEC não modifica título, conteúdo, status, Elementor ou taxonomias.

## Owner lógico

`Resumo Executivo`.

## Metadados canônicos

| Campo lógico | Meta key | Tipo | Cardinalidade | Default projetado | Vazio persistido? | Limite de write |
|---|---|---|---|---|---|---:|
| `objective` | `_bdc_es_objective` | string multiline | single | `''` | não; delete | 32768 bytes |
| `escalation` | `_bdc_es_escalation` | string multiline | single | `''` | não; delete | 32768 bytes |
| `important` | `_bdc_es_important` | string multiline | single | `''` | não; delete | 32768 bytes |

## Contrato de leitura

- meta ausente -> `''`;
- leitura nunca cria meta;
- valores não-string inesperados devem falhar seguro/projetar estado diagnosticável conforme implementação, sem coerção destrutiva;
- título é lido de `post_title` apenas para contexto.

## Contrato de write

1. validar post ID e `post_type`;
2. autorizar por objeto;
3. validar método/nonce;
4. validar allowlist e tipos;
5. aplicar `wp_unslash` no input HTTP;
6. rejeitar campo >32768 bytes antes da sanitização;
7. sanitizar com `trim(sanitize_textarea_field())`;
8. snapshot dos três campos;
9. diff;
10. vazio -> `delete_post_meta`;
11. não vazio -> `update_post_meta` com slashing compatível;
12. reler os três campos e comparar;
13. compensar em mismatch.

## NO_CHANGE

Valor final igual ao snapshot não deve gerar write. Retorno `false` de `update_post_meta()` isoladamente não define falha; o estado relido define.

## Retenção

Indefinida enquanto o post existir. Desativação/uninstall do novo plugin não remove os três metadados nesta SPEC.

## Não existem nesta SPEC

Tabela própria, schema version, migration, vector, cache próprio, queue, histórico/audit genérico ou projection.
