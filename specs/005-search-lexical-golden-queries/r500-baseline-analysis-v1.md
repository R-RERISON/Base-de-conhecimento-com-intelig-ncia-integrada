# R-500 — Baseline Analysis v1

**Data:** 2026-09-18  
**Estado:** R-500 PASS/CLOSED.

## T500 — Baseline congelada

Branch SPEC-005 criada a partir de:

`main @ 07f877b2978429dc6b31fbe172e6ce8fca7ee634`

Plugin baseline:

`0.4.0-spec004-rc2`

SPEC-004 está CLOSED/main.

Último corpus ambiental conhecido: 623 artigos. Esse número é referência, não substitui recontagem R-500.

## T501 — Busca atual

A única busca existente no novo plugin está em `Admin_Page::render_list()`:

- recebe `$_GET['s']`;
- sanitiza com `sanitize_text_field`;
- usa `WP_Query`;
- `post_type = Meta_Contract::POST_TYPE = post`;
- statuses: publish, draft, pending, private, future;
- `perm = editable`;
- ordenação final da lista: modified DESC;
- não há ranker BDC;
- não há normalizer BDC;
- não há Search Document;
- não há projection;
- não há FULLTEXT próprio;
- não há Golden runtime;
- não há query logging;
- não há IA/vetor.

Portanto, a busca atual é **localização administrativa básica**, não o contrato de Search Retrieval da SPEC-005.

## Gap já comprovado arquiteturalmente

A SPEC-004 estabeleceu Content Extractor/KD como camada semântica comum. A busca nativa `WP_Query s` não consome automaticamente essa projeção; portanto R-500 deve medir, e não presumir, cobertura suficiente em fontes Elementor/mixed/legadas.

## ASI

Baseline histórico: ASI 4.6.8 @ `c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.

Contratos recuperados:
- QueryContext bounded;
- retrieval lexical;
- ranking explicável;
- Golden expected_post/max_rank/severity;
- empty suite NOT_CONFIGURED;
- set hash;
- algorithm version;
- blocking fail NO-GO;
- explicit run.

Pesos, tabelas e equivalências hardcoded não são herdados.

## Próximo

T502 — diagnóstico read-only ambiental para:
- recontar corpus;
- perfilar source kinds;
- medir busca nativa;
- exportar JSON;
- zero write editorial/schema.


## Fechamento R-500

Ver:
- `r500-t502-environmental-findings-v1.md`;
- `r500-search-baseline-decision.md`;
- `evidence/r500-t502-environmental-20260918T200421Z.json`.

R-500 foi fechado em 2026-09-18. Próximo gate: R-510.
