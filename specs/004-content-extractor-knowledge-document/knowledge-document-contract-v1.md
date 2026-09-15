# Knowledge Document Contract v1 — SPEC-004

**Contract version:** `1.0.0`  
**Gate:** G-230  
**Pré-requisito:** G-220 ambientalmente PASS  
**Natureza:** projeção canônica, determinística, read-only e reconstruível.

## 1. Objetivo

Definir a representação canônica consumível por busca lexical, busca semântica, chunking, embeddings e IA sem acoplar esses consumidores a HTML, Gutenberg serializado ou `_elementor_data`.

O Knowledge Document não é fonte editorial, não é CMS e não possui writer.

## 2. Invariantes

A construção do documento:

- é read-only;
- não persiste documento, hash, cache ou progresso;
- não altera `post_content`, `_elementor_data`, status, datas, revisões, termos ou options;
- não executa shortcode/widget/bloco dinâmico arbitrário;
- não depende de IA, Foundry, embeddings, vetor ou rede externa;
- usa exclusivamente a saída contratual do `Content_Extractor` e metadados editoriais nativos necessários;
- é reproduzível byte-a-byte para o mesmo input lógico.

## 3. Schema v1

```text
schema_version: "1.0.0"
post_id: int
source_kind: string
source_hash: sha256
document_hash: sha256
title: string
canonical_url: string
modified_gmt: string
sections[]:
  kind: string
  heading: string
  text: string
  ordinal: int
  source: string
  meta?: object
structure:
  headings: int
  lists: int
  tables: int
  images: int
  links: int
  code_blocks: int
  shortcodes: int
extraction:
  strategies[]: string
  fallback_used: bool
  warnings[]: string
  elementor_compatibility:
    status: native|projectable|review_required|blocked
    reasons[]: string
```

Nenhum HTML/JSON bruto pertence ao documento.

## 4. Seções

`sections[]` deriva dos fragments normalizados do extractor.

Regras:

- ordem editorial é preservada;
- `ordinal` é reconstruído de `0..N-1`;
- fragmento vazio após normalização é descartado;
- `heading` contém o próprio texto somente para `kind=heading`; nos demais casos é string vazia;
- `source` preserva proveniência (`post_content`, Elementor etc.) sem copiar markup bruto;
- `meta` entra apenas quando já for metadado estrutural controlado, como nível de heading;
- `code` preserva whitespace conforme contrato do normalizador.

## 5. `source_hash`

`source_hash` representa o **conhecimento semântico extraído**, não o armazenamento bruto.

Payload canônico v1:

```text
schema_version
source_kind
title
sections[]
structure
```

Consequências intencionais:

- mudança de título altera `source_hash`;
- mudança textual/ordem/estrutura semântica altera `source_hash`;
- mudança apenas em `_elementor_data` bruto que não altera a projeção semântica não altera `source_hash`;
- tamanho bruto da fonte não contamina o hash;
- `post_modified_gmt` e URL não contaminam o hash.

O hash bruto necessário a `STALE_SOURCE` da futura migração Elementor é outro contrato e não deve ser confundido com `source_hash`.

## 6. `document_hash`

`document_hash` representa a projeção canônica consumível do documento, incluindo identidade do post e proveniência de extração relevante.

Payload v1: Knowledge Document sem:

- `document_hash`;
- `canonical_url`;
- `modified_gmt`.

Motivo: URL e timestamp são envelope operacional. Alterá-los sem mudança de conhecimento não deve invalidar o hash semântico consumido pelos índices futuros.

`post_id` permanece dentro do hash do documento, pois o documento representa uma entidade editorial específica mesmo quando dois posts possuam conteúdo equivalente.

## 7. Canonical JSON

Antes da serialização:

- mapas/arrays associativos têm chaves ordenadas lexicograficamente;
- listas preservam ordem;
- Unicode não é escapado desnecessariamente;
- barras não são escapadas desnecessariamente;
- `0.0` permanece distinguível quando aplicável;
- serialização inválida falha fechada com `WP_Error`.

Implementação v1: JSON UTF-8 + SHA-256.

## 8. Proveniência operacional

`canonical_url` e `modified_gmt` permanecem no documento para navegação, diagnóstico e consumidores operacionais, mas não participam de `source_hash` nem `document_hash`.

`elementor_compatibility` é somente readiness/proveniência. Não autoriza writer nem altera a independência do Knowledge Document em relação ao editor.

## 9. Error handling

Falhas em:

- post inexistente/post type não suportado;
- Content Extractor;
- serialização canônica;
- cálculo de hash;

retornam `WP_Error` e nunca conteúdo parcial apresentado como sucesso.

## 10. Storage

G-230 mantém o documento **in-memory**.

Nenhuma tabela, post meta, transient, object cache ou arquivo persistente é criado nesta etapa.

Persistência futura só pode nascer quando um consumidor posterior provar necessidade, ownership, invalidação e lifecycle.

## 11. Critérios G-230

PASS quando houver evidência de:

1. schema v1 congelado;
2. JSON canônico determinístico;
3. input lógico idêntico → mesmos hashes e mesmos bytes;
4. mudança semântica → hashes diferentes;
5. mudança de título → hashes diferentes;
6. URL/data operacional → hashes semânticos estáveis;
7. ruído bruto/layout não utilizado → hashes estáveis;
8. ordem editorial diferente → hash diferente;
9. build read-only;
10. nenhuma persistência durável;
11. smoke ambiental sobre corpus real com duas passagens e zero mismatch.

## 12. Relação com normalização Elementor

O Knowledge Document não depende da conversão do corpus para Elementor.

A normalização editorial futura pode alterar a proveniência/source kind. O conteúdo derivado continua sendo comparável por seções/hashes e a migração só avança com validação própria de equivalência semântica definida em G-245.

## 13. Estado

**FROZEN v1.0.0 para implementação e smoke ambiental G-230.**
