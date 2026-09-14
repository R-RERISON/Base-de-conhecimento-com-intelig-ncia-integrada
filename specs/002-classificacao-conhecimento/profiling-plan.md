# Plano de Profiling Read-only — SPEC-002 S001

## Objetivo

Medir o estado real dos 11 stores históricos de classificação sem alterar qualquer conteúdo, metadata, taxonomy, option, transient, cron ou schema.

## Escopo de posts

- `post_type=post`;
- status: `publish`, `draft`, `pending`, `private`, `future`;
- excluir `trash`, `auto-draft` e revisões.

## Stores

- `_bdc_es_target_audience`;
- `_kb2ops_target_audience`;
- `_bdc_es_responsible_team`;
- `_bdc_es_catalog_item`;
- `_kb2ops_service`;
- `_bdc_es_affected_service`;
- `_kb2ops_technologies`;
- `_bdc_es_systems_involved`;
- `_kb2ops_knowledge_type`;
- `_kb2ops_keywords`;
- `_kb2ops_versions`.

## Métricas por store

- total de rows;
- posts com key;
- posts com valor não vazio;
- cobertura percentual;
- distinct raw aproximado;
- distinct normalized;
- razão de cardinalidade;
- tamanho máximo de valor;
- tipos após unserialize (`string`, `array`, etc.);
- presença de serialized/JSON;
- sinais de delimitadores `, ; | newline`;
- top valores normalizados por frequência, limitados;
- amostras limitadas, sem post title/content/user/IP.

## Comparações entre conceitos

### Audiência GRE ↔ KB2Ops

- only-left;
- only-right;
- both;
- conjuntos iguais por post;
- conflitos por post;
- overlap global de valores.

### Serviço ↔ Serviço afetado

Somente medir overlap. **Não inferir merge.**

### Tecnologias ↔ Sistemas envolvidos

Somente medir overlap. **Não inferir merge.**

## Normalização analítica

Usada somente para medir colisões; não é contrato de write:

1. decode HTML entities;
2. remover tags;
3. trim;
4. colapsar whitespace;
5. lowercase quando `mb_strtolower` disponível.

O valor original não é alterado no WordPress.

## Privacidade e minimização

O relatório não inclui:

- títulos dos posts;
- `post_content`;
- `_elementor_data`;
- autores/usuários;
- IP/User identity;
- IDs por conflito individual.

Inclui apenas agregados e valores classificatórios limitados necessários à decisão do modelo.

## Safety

- método: POST administrativo com nonce;
- capability: `manage_options`;
- zero writes intencionais;
- sem fixture;
- sem persistência de relatório;
- JSON baixado diretamente;
- package de profiling é temporário e não é release.
