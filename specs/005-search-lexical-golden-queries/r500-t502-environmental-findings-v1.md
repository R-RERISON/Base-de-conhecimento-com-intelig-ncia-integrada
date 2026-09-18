# R-500/T502 — Environmental Findings v1

**Data:** 2026-09-18  
**Status:** T502 PASS AMBIENTAL / R-500 READY FOR CLOSEOUT

## 1. Safety

- fingerprint before == after;
- changed posts = 0;
- corpus 623 -> 623;
- no schema/table creation;
- no index/Golden/query-log persistence;
- no network/shortcode/Elementor/dynamic-block execution;
- extractor errors = 0.

Resultado: `t502_read_only_safety_pass=true`.

## 2. Corpus

- total: 623;
- publish: 606;
- draft: 12;
- pending: 3;
- private: 2.

Source kinds:
- legacy_html: 535;
- plain_text: 41;
- Elementor: 34;
- Gutenberg: 5;
- mixed: 5;
- empty: 3.

## 3. Gap de cobertura semântica

610 posts possuem texto semântico no Content Extractor.

91/610 = **14,92%** contêm tokens semânticos que não estão integralmente representados no conjunto nativo pesquisável (title/excerpt/post_content).

### Elementor

- 34 posts;
- mean coverage: 91,4928%;
- p50: 97,0833%;
- p05: 67,4242%;
- mínimo: **1,6975%**.

Conclusão: `post_content` não pode ser assumido como corpus lexical suficiente para Elementor.

### Summary

18 posts possuem sinal de Summary; 14/18 = **77,78%** possuem conteúdo não integralmente pesquisável nativamente.

Conclusão: Summary é sinal lexical importante e atualmente subutilizado pela busca nativa.

### Taxonomia

O diagnóstico encontrou zero posts com sinal taxonômico no corpus medido. Não inferir que taxonomia é irrelevante; apenas não há evidência ambiental atual para atribuir peso no ranker v1.

## 4. Ranking atual da Knowledge List

A semântica atual reproduz `WP_Query s + modified DESC`:

- Top-1: 33,33%;
- Top-3: 61,67%;
- Top-10: 76,67%;
- Top-20: 86,67%;
- missed: 8/60;
- p95: 174,171 ms.

Busca WordPress sem ordenação explícita por modificação:

- Top-1: 85%;
- Top-3: 96,67%;
- Top-10: 98,33%;
- Top-20: 100%;
- missed: 0/60;
- p95: 180,6052 ms.

Diferença:
- Top-1: **+51,67 pp**;
- Top-3: **+35 pp**;
- Top-20: **+13,33 pp**;
- p95: apenas **+6,434 ms**.

Conclusão: a ordenação atual por `modified DESC` destrói relevância sem ganho de latência relevante.

## 5. Performance do Content Extractor

623 posts:
- mean: 2,158 ms/post;
- p50: 1,231 ms;
- p95: 7,4911 ms;
- max: 38,534 ms.

Isto comprova que a representação semântica é barata para rebuild/indexação, mas não autoriza varrer o corpus inteiro por consulta online.

## 6. Classificação de gaps T505

### Proven
- ranking gap: **SIM**;
- semantic coverage gap: **SIM**;
- Summary visibility gap: **SIM**;
- Elementor coverage gap: **SIM**.

### Ainda não provado
- typo/alias/normalization gap: exige Golden Queries reais;
- taxonomy ranking value: sem sinal ambiental atual;
- public permission behavior: fora da superfície inicial;
- necessidade de FULLTEXT: ainda não provada.

## 7. Decisão T506 — superfície inicial

**Admin-first.**

A primeira Search surface da SPEC-005 deve substituir/evoluir a pesquisa da Knowledge List administrativa, porque:
- já existe jornada real;
- capability/scope são conhecidos;
- evita alterar a busca pública global prematuramente;
- permite Golden/human acceptance antes de exposição externa.

Busca pública permanece POSTERGADA até gate específico.

## 8. WordPress-first outcome

`WP_Query` puro permanece:
- baseline;
- fallback;
- fonte de autorização/visibilidade.

Mas não é suficiente como engine final porque:
1. ordenação atual é inadequada;
2. campos nativos não cobrem toda a representação semântica;
3. Summary não participa adequadamente;
4. Elementor possui gaps extremos em casos reais.

### Decisão arquitetural

A SPEC-005 passa a exigir **Search Document post-level derivado do Content Extractor**.

A forma de persistência ainda NÃO está fechada:
- tabela própria;
- FULLTEXT;
- alternativa WordPress-native.

Isto será decidido em G-520 após R-510 Golden Dataset.

## 9. R-500 conclusion

T500–T507: PASS.

**R-500: PASS / CLOSED.**

Isso não libera runtime. DoR global continua bloqueado por:
- R-510 Golden Dataset;
- G-520 contracts/storage/security.
