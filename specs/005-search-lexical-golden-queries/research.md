# Research — SPEC-005

## Fontes internas

### Constituição v1.3.0
- Golden Queries obrigatórias antes de ranking/semantic/IA;
- lexical permanece relevante;
- projeções são reconstruíveis;
- WordPress-first;
- sem big-bang.

### SPEC-000 / ASI 4.6.8
Referência: `R-RERISON/Advanced-search-Intelligence@c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.

Preservar comportamento:
- query normalization bounded;
- retrieval plan limitado;
- ranking explicável;
- FULLTEXT quando justificado;
- fallback lexical;
- Golden Queries;
- set hash;
- algorithm version;
- blocking/warning;
- empty suite = NOT_CONFIGURED;
- explicit run.

Não herdar:
- 12 tabelas;
- migrações ASI;
- queue;
- analytics;
- GAC;
- Word Cloud;
- hardcodes de domínio;
- pesos históricos como verdade.

### SPEC-004
Content Extractor/KD fornecem representação semântica comum. Search não deve voltar a extrair diretamente cada formato editorial por conta própria.

## Baseline atual

`Admin_Page::render_list()` usa `WP_Query` e o parâmetro `s`. Isso atende localização administrativa básica, mas ainda não demonstra:
- cobertura Elementor/mixed;
- ranking de relevância;
- query normalization;
- explicabilidade;
- Golden regression;
- busca pública.

## Hipóteses a testar

H1. `WP_Query s` pode ser suficiente para parte relevante do corpus já normalizado em `post_content`.

H2. Conteúdo Elementor/legado e campos derivados podem gerar gaps que justificam Search Retrieval Projection.

H3. Para ~centenas de artigos, fila durável provavelmente não é necessária no primeiro slice; medir antes de decidir.

H4. Item-level/deep-link deve permanecer fora do v1 até Golden Queries post-level provarem lacuna real.

## Não decidido

- tabela própria;
- FULLTEXT;
- superfície pública;
- persistence Golden;
- aliases/vocabulary;
- pesos finais;
- SLA final.

Essas decisões pertencem a R-500/R-510/G-520.
