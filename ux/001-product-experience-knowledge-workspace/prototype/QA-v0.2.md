# UX-001 — QA UI as Code v0.2

**Resultado automatizado:** PASS

## Browser / layout

- 1440px: sem overflow horizontal;
- 1024px: sem overflow horizontal;
- 782px: sem overflow horizontal;
- 492px: sem overflow horizontal;
- Summary feedback: PASS;
- Classification feedback: PASS;
- navegação de tabs com ArrowLeft/ArrowRight: PASS;
- filtro com `aria-expanded`: PASS;
- IDs duplicados: 0;
- labels `for` órfãos: 0.

## Contraste WCAG AA — pares principais

- `white-primary`: **5.34:1 — PASS**
- `navy-white`: **15.94:1 — PASS**
- `muted-white`: **4.97:1 — PASS**
- `success-successbg`: **5.92:1 — PASS**
- `warning-warningbg`: **5.46:1 — PASS**
- `danger-dangerbg`: **5.76:1 — PASS**
- `info-infobg`: **5.42:1 — PASS**

## Evidências raster locais

- `viewport-1024.png` — SHA-256 `6ea8f2b3edaf9cda577b1dc86e04851763c17a68662a0f93bde65b8b5b483f32`;
- `viewport-1440.png` — SHA-256 `f8acf3ce2d973c2d463a5c09ba67d8c588c4059b564cf03928fd73ca9c76818b`;
- `viewport-492.png` — SHA-256 `055decaaf45161d440b4ba796b4520242a2183a60928f82f324474afb4f3fabb`;
- `viewport-782.png` — SHA-256 `5aedc0ff80fdb38353a128701c0b8bbe660a6c517e93038b9e8bc7a4c384c9e9`.

## Limite do gate

Este QA comprova o protótipo executável. Não substitui browser acceptance do plugin WordPress real quando a nova shell for implementada.
