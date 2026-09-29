# Avaliação WordPress-first — SPEC-005

**Status:** DECISÃO FECHADA EM G-520/T525  
**ADR:** `adr-005-003-search-storage-wordpress-first.md`

## Evidência

R-500:
- 623 posts; 606 publish;
- WP_Query nativo: 85% Top-1 / 100% Top-20 em 60 probes;
- p95 nativo: 180,6052 ms;
- 91/610 posts com gap semântico (14,92%);
- 14/18 posts com Summary não integralmente pesquisável nativamente (77,78%);
- Content Extractor p95: 7,4911 ms/post;
- MariaDB 12.2.2.

R-510:
- Golden Relevance congelada;
- Technical Challenges comprovam necessidade de linguagem natural, Summary e Elementor/Content Extractor;
- zero runtime dependency ASI.

## Decisão

### A — WP_Query nativo
**REJEITADO como engine canônica / PRESERVADO como fallback.**

Excelente baseline, mas sem cobertura semântica suficiente.

### B — WP_Query + hooks mínimos
**REJEITADO como engine canônica.**

Não resolve de forma limpa o Search Document derivado, explicabilidade e rebuild/versionamento. Hooks globais permanecem proibidos.

### C — Search Retrieval Projection post-level
**ACEITA.**

Uma tabela BDC própria, reconstruível, alimentada por WordPress + Meta_Contract + Classification_Contract + Content_Extractor.

### D — C + FULLTEXT
**POSTERGADA / NÃO AUTORIZADA no v1.**

Não há benchmark que justifique FULLTEXT antes do primeiro engine sobre 623 documentos.

## Retrieval inicial aprovado

- Projection + SQL LIKE bounded;
- strict all-token;
- relaxed any-token fallback;
- candidate cap 200;
- ranking determinístico em PHP;
- per-post authorization revalidated;
- fallback `WP_Query`.

## Critério para reconsiderar FULLTEXT

Somente após G-570, se evidência de p95/escala demonstrar necessidade.

A mudança exigirá:
- ADR revision;
- benchmark A/B;
- Golden/Challenge current;
- fallback preservado;
- migration/lifecycle próprios.

## Regra ASI

Nenhuma tabela, índice ou mecanismo ASI é reutilizado. O ASI poderá ser removido materialmente.
