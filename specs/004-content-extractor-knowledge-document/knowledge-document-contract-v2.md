# Knowledge Document Contract v2 — SPEC-004

**Contract version:** `2.0.0`  
**Motivação:** G-240 v1 FAIL por perda estrutural  
**Natureza:** projeção canônica, determinística, read-only, reconstruível e semanticamente estruturada.

## 1. Objetivo

Evoluir o Knowledge Document para preservar as relações estruturais necessárias a busca, chunking, embeddings, RAG e IA sem acoplar consumidores ao HTML, Gutenberg serializado ou `_elementor_data`.

O Knowledge Document continua não sendo CMS, editor ou fonte editorial.

## 2. Razão da major version

O schema `1.0.0` preservou texto e ordem, mas o G-240 demonstrou que sua projeção linear era insuficiente para listas, tabelas e contexto hierárquico de headings.

Como a correção altera a semântica canônica e consequentemente os hashes, a evolução é **major**: `2.0.0`.

O v1 permanece como evidência histórica de determinismo, mas é `SUPERSEDED_FOR_AI` após o G-240 v1.

## 3. Invariantes

A construção v2:

- é read-only;
- não persiste documento/hash/cache/progresso;
- não altera conteúdo editorial ou metadados de autoria/publicação;
- não executa shortcode/widget/bloco dinâmico arbitrário;
- não depende de IA, embeddings, vetor, Foundry ou rede externa;
- não promove layout visual do Elementor a semântica sem evidência;
- preserva conteúdo semanticamente significativo e sua estrutura;
- é determinística byte-a-byte para o mesmo input lógico.

## 4. Schema v2

```text
schema_version: "2.0.0"
post_id: int
source_kind: string
source_hash: sha256
document_hash: sha256
title: string
canonical_url: string
modified_gmt: string

sections[]:                  # visão linear/diagnóstica
  kind: string
  heading: string
  text: string
  ordinal: int
  source: string
  heading_path[]:
    level: int
    text: string
  meta?: object

blocks[]:                    # representação semântica canônica
  kind: heading|paragraph|list|table|code|quote|image|...
  ordinal: int
  source: string
  heading_path[]

  # atomic blocks
  text?: string
  meta?: object

  # list
  list_id?: string
  list_type?: ordered|unordered|unknown
  depth?: int
  items?:
    - item_id: string
      item_index: int
      parent_item_id: string
      text: string
      children[]: list

  # table
  table_id?: string
  caption?: string
  rows?:
    - row_index: int
      cells:
        - cell_index: int
          kind: header|data
          text: string
          colspan: int
          rowspan: int

structure:
  headings: int
  paragraphs: int
  lists: int
  list_items: int
  tables: int
  table_rows: int
  table_cells: int
  images: int
  links: int
  code_blocks: int
  shortcodes: int

ai_readiness:
  status: candidate_ready|review_required|not_ready|not_applicable
  reasons[]: string
  structure_complete: bool

extraction:
  strategies[]
  fallback_used: bool
  warnings[]
  elementor_compatibility:
    status: native|projectable|review_required|blocked
    reasons[]
```

Nenhum HTML bruto ou `_elementor_data` bruto pertence ao documento.

## 5. Headings e contexto

A hierarquia é calculada por níveis H1–H6.

Para cada heading:

- remover do stack níveis iguais ou inferiores na hierarquia atual;
- inserir o novo heading em seu nível;
- `heading_path` contém a cadeia ativa em ordem crescente de nível.

Blocos subsequentes herdam esse `heading_path`.

Objetivo: permitir chunking/retrieval contextual futuro sem depender de varredura heurística dos blocos anteriores.

## 6. Parágrafos

Parágrafo continua sendo unidade textual simples, mas agora possui `heading_path` explícito.

Normalização textual continua sem reescrita semântica, resumo, stemming, tradução ou correção ortográfica.

## 7. Listas

Cada lista recebe identidade determinística na ordem de travessia.

Preservar:

- ordered versus unordered;
- profundidade;
- índice de item;
- identidade do item;
- item pai quando lista aninhada;
- listas filhas dentro do item semanticamente correspondente.

Texto de uma lista filha não deve ser achatado dentro do texto do item pai.

## 8. Tabelas

Cada tabela recebe identidade determinística.

Preservar:

- caption quando existente;
- ordem de linhas;
- ordem de células;
- `th` versus `td` como `header|data`;
- `rowspan` e `colspan`;
- texto normalizado de cada célula.

O campo textual `"A | B"` pode existir apenas como visão linear de compatibilidade/diagnóstico. A representação semântica oficial é `rows[].cells[]`.

## 9. Elementor

Elementor é direção editorial futura, mas Knowledge Document permanece editor-independent.

Regras:

- estrutura HTML semanticamente significativa dentro de widgets permitidos é preservada pelo adapter estrutural;
- sections/containers/columns puramente visuais não viram hierarquia de conhecimento automaticamente;
- widgets não suportados permanecem warning/review, nunca renderização arbitrária;
- normalização/migration Elementor continua separada em G-245.

## 10. Gutenberg

Blocos estáticos conhecidos continuam parseados sem `render_block()`.

Sua estrutura semântica deve convergir para os mesmos `blocks[]` do restante do corpus. O consumidor não precisa conhecer Gutenberg.

## 11. Fallback sem DOM

A ausência de `DOMDocument` não pode causar fatal, mas listas/tabelas complexas não podem ser declaradas plenamente estruturadas por regex.

Quando fallback sem DOM for usado:

- preservar texto em best effort;
- emitir `HTML_STRUCTURE_DEGRADED_NO_DOM`;
- `ai_readiness.status` não pode ser `candidate_ready`.

O ambiente de homologação/produção observado possui DOM, portanto esse fallback é proteção de portabilidade, não caminho normal.

## 12. AI readiness objetivo

O operador humano não decide diretamente se um documento é adequado para IA.

### `candidate_ready`

- há conteúdo semântico quando esperado;
- contagens estruturais relevantes são coerentes com `blocks[]`;
- nenhum warning crítico ou gap semântico conhecido exige bloqueio/review.

### `review_required`

Estrutura representável, porém existe warning que pode esconder semântica dinâmica ou específica:

- shortcode não expandido;
- bloco dinâmico;
- bloco Gutenberg não suportado;
- widget Elementor não suportado;
- Elementor inválido/fallback que exige análise.

### `not_ready`

- structural count mismatch;
- hard limit sem representação alternativa segura;
- render fallback candidate;
- parser sem DOM com estrutura complexa;
- ausência inesperada de conteúdo.

### `not_applicable`

Fonte editorial realmente vazia.

Esse status é técnico e explicável. Não substitui a aceitação G-240 do modelo estrutural.

## 13. Hashes v2

### `source_hash`

Payload:

```text
schema_version
source_kind
title
sections[]
blocks[]
structure
```

`ai_readiness`, warnings operacionais, URL e timestamp não fazem parte do `source_hash`.

### `document_hash`

Knowledge Document completo, exceto:

- `document_hash`;
- `canonical_url`;
- `modified_gmt`.

Como a estrutura canônica mudou, hashes v2 não devem ser comparados diretamente com hashes v1 como se fossem mesma versão semântica.

## 14. Aceitação humana G-240 v2

O operador avalia apenas o que consegue observar objetivamente:

1. cobertura completa;
2. ordem semântica preservada;
3. nenhum texto inventado;
4. estrutura semântica preservada.

`acceptable_for_knowledge_use` deixa de ser checkbox humano.

A tela deve exibir `ai_readiness` calculado pelo sistema e renderizar semanticamente `blocks[]`, inclusive listas aninhadas e tabelas.

## 15. Gate de promoção

Antes de novo G-240:

- testes sintéticos de heading path;
- lista aninhada;
- tabela com header/data e spans;
- canonical JSON v2 determinístico;
- alteração estrutural altera hash;
- URL/timestamp continuam neutros;
- zero-write;
- package parity/lint/integrity;
- smoke ambiental de repetibilidade v2.

G-245 permanece bloqueado até G-240 v2 PASS.

## 16. Estado

**FROZEN v2.0.0 para structural remediation após G-240 v1 FAIL.**
