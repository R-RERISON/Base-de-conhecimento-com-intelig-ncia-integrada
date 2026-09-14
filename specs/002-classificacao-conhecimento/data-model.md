# Data Model — SPEC-002 Classificação de Conhecimento

## 1. Resultado do profiling

O profiling real em 622 posts encontrou baixa cobertura nos stores históricos e nenhum dado nos stores KB2Ops perfilados. Os valores GRE observados são insuficientes e ruidosos para seed/migração automática.

Por isso, a nova classificação nasce com owner físico próprio e vocabulário governado.

## 2. Conceitos do primeiro slice

| Conceito | Taxonomy canônica | Cardinalidade | Hierarquia inicial | Migração automática |
|---|---|---|---:|---:|
| Audiência | `bdc_kb_audience` | multi | NÃO | NÃO |
| Equipe responsável | `bdc_kb_responsible_team` | multi | NÃO | NÃO |
| Tipo de conhecimento | `bdc_kb_knowledge_type` | single | NÃO | NÃO |
| Item de catálogo | `bdc_kb_catalog_item` | multi | NÃO | NÃO |

A ausência de hierarquia inicial é deliberada: o profiling não prova uma árvore organizacional ou taxonomia de catálogo autoritativa. Hierarquia poderá ser habilitada por nova decisão sem alterar o owner lógico.

## 3. Contrato das taxonomias

Todas:

- objeto suportado: `post`;
- `public=false`;
- `publicly_queryable=false`;
- `show_ui=true` para gestão nativa de termos;
- `show_in_rest=false`;
- `rewrite=false`;
- `query_var=false`;
- `show_in_nav_menus=false`;
- `show_admin_column=false` inicialmente;
- `show_in_quick_edit=false`;
- `meta_box_cb=false` para impedir um segundo writer de assignment fora da superfície governada do plugin.

Capabilities de termos usam primitives WordPress existentes; assignment no runtime exige também `edit_post` do objeto alvo.

## 4. Classificação canônica

A fonte da verdade classificatória do slice é a relação `term_relationships`/Taxonomy API correspondente às quatro taxonomias namespaced.

Não existe postmeta espelho.

### Leitura

- retornar apenas termos realmente relacionados ao post;
- ordenar IDs antes de comparação técnica;
- termo ausente = estado vazio;
- leitura não cria termo nem relação.

### Escrita

Payload lógico por conceito: array de `term_id`.

- omitido = preservar;
- `[]` = remover todas as relações daquele conceito;
- IDs repetidos = deduplicar antes do diff;
- ID inexistente = erro;
- termo pertencente a outra taxonomy = erro;
- `tipo_conhecimento` com mais de um ID = erro;
- desconhecido fora da allowlist = erro do payload inteiro;
- validar tudo antes do primeiro write.

## 5. Write composto

Fluxo:

`authorize -> validate all -> sanitize IDs -> snapshot -> diff -> writes mínimos -> read-after-write -> compare`

Falha após writes parciais:

`compensação best-effort -> reread`

Estados:

- `SUCCESS`;
- `NO_CHANGE`;
- `FAIL_SAFE`;
- `PARTIAL_FAILURE_CRITICAL`.

Nenhum sucesso é emitido sem confirmação por releitura.

## 6. Legado

Stores históricos permanecem somente como referência read-only:

- `_bdc_es_target_audience`;
- `_kb2ops_target_audience`;
- `_bdc_es_responsible_team`;
- `_bdc_es_catalog_item`.

No primeiro slice, apenas os stores correspondentes aos quatro conceitos podem ser exibidos na UI, com label explícito **Referência legada — não canônica**.

Regras:

- não preselecionar termo por similaridade;
- não criar termo a partir de texto legado;
- não gravar no legado;
- não usar legado como fallback canônico;
- escapar todo output.

## 7. Conceitos adiados

| Conceito | Origem histórica | Estado |
|---|---|---|
| Serviço | `_kb2ops_service` | adiado; store vazio no profiling |
| Serviço afetado | `_bdc_es_affected_service` | adiado; não mesclar com Serviço |
| Tecnologias | `_kb2ops_technologies` | adiado; store vazio |
| Sistemas envolvidos | `_bdc_es_systems_involved` | adiado; não mesclar com Tecnologias |
| Keywords | `_kb2ops_keywords` | adiado; avaliar tags WP vs taxonomy própria em SPEC futura |
| Versões | `_kb2ops_versions` | adiado; provável atributo técnico, primitive futura |

## 8. Taxonomias editoriais existentes

Categorias e tags WordPress continuam sob ownership editorial. A SPEC-002 não as converte, renomeia ou reutiliza silenciosamente.

## 9. Compatibilidade/cutover

Não existe cutover automático nesta SPEC. A nova camada pode coexistir com plugins legados porque usa stores físicos distintos. Uma futura migração exige profiling ampliado, mapeamento aprovado e operação explícita; dual-write permanente continua proibido.