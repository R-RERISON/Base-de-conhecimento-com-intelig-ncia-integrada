# T093 — Análise de Block Projection e abertura do T094

**Status:** T093 PASS AMBIENTAL  
**Corpus:** 623 posts  
**Build:** `0.4.0-g245-block-projection-t093.1`

## Resultado T093

- duas passagens 623/623;
- errors 0;
- throwables 0;
- block projection hash mismatches 0;
- canonical hash mismatches 0;
- safety violations 0;
- fingerprint editorial before/after idêntico;
- `gate_result.t093_block_projection_pass=true`.

Distribuição:

- `projectable`: 347;
- `native_noop`: 4;
- `not_applicable`: 3;
- `review_required`: 269.

## Decomposição do review

O runner confirmou 233 Knowledge Documents em `review_required`. As razões agregadas por família foram:

- `HIERARCHY_AMBIGUOUS`: 964 ocorrências;
- `SHORTCODE_NOT_EXPANDED`: 55;
- `HTML_LOCAL_HEADING_FLATTENED`: 19;
- `HIERARCHY_NUMBERING_CONFLICT`: 6;
- `HTML_NESTED_LIST_IN_TABLE_FLATTENED`: 2.

Warnings de Block Projection:

- table span: 32;
- image sem mapping seguro: 40;
- KD review_required: 233.

O alto volume de `HIERARCHY_AMBIGUOUS` representa ocorrências, não 964 posts. Um mesmo post pode carregar várias razões.

## Descoberta crítica antes do serializer

O Knowledge Document 2.1 foi desenhado como projeção semântica para busca/IA e aceite estrutural. Ele não é, sozinho, um contrato de fidelidade editorial para migração.

O `Legacy_HTML_Adapter` materializa headings/parágrafos/list items/quotes/células como texto visível. Links inline, marcações como `strong/em` e outros detalhes de rich text não são preservados no KD como estrutura editorial. Imagens materializam `alt` + `image_id` sintético, sem `src`/attachment ID canônico.

Consequência: serializar diretamente o KD para Core Blocks pode preservar texto/estrutura e ainda assim perder links, mídia e formatação inline. Isso viola o objetivo de normalização editorial sem regressão.

## Decisão

T094 deixa de ser imediatamente “serializer”. Antes, deve congelar **Editorial Fidelity Contract** e executar inventário read-only do corpus para identificar dependências editoriais não representadas pelo KD.

Arquitetura:

`fonte editorial original -> Migration Fidelity Source -> Core Block Serializer`

em paralelo com:

`fonte editorial original -> Content Extractor -> Knowledge Document -> busca/IA/semantic validation`.

O KD continua sendo usado como checksum semântico/estrutural e guardrail de não-invenção, mas não como única fonte para o writer futuro.

## T094 — critérios

Inventariar, sem exportar conteúdo:

- links/hrefs;
- imagens/src e attachment references quando identificáveis;
- rich inline tags relevantes;
- tabelas/spans;
- shortcodes;
- Gutenberg nativo;
- Elementor/mixed que exigem adapter específico.

O relatório deve produzir apenas contagens/riscos/hashes agregados, sem conteúdo editorial ou URLs.

Nenhum serializer persistente ou writer será habilitado antes dessa evidência.
