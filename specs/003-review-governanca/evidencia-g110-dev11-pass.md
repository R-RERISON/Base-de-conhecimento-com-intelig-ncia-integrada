# Evidência G-110 — `0.3.0-dev.11` — PASS

## Ambiente real

- WordPress: `6.9.4`
- PHP: `8.5.10`
- Plugin: `0.3.0-dev.11`
- Multisite: não

Evidência bruta:

`evidencias/bdc-kb-g110-browser-acceptance-20260915-200043.json`

## Resultado

- browser: **22 PASS / 0 FAIL**
- server: **7 PASS / 0 FAIL**
- `overall=PASS`
- `residual_posts=0`
- `residual_terms=0`
- `residual_review_events=0`

## Cobertura comprovada

O Browser Acceptance real confirmou:

- Workspace com exatamente cinco tabs autorizadas;
- Context Header + Main Work Area;
- ausência de `AI Ready`, health score e features proibidas;
- navegação por teclado `ArrowLeft` / `ArrowRight` / `Home` / `End`;
- foco visível e semântica nativa de links/Enter;
- Summary renderizado com labels, save real e permanência em `tab=summary`;
- Classificação renderizada, save real e permanência em `tab=classification`;
- Review iniciando `unreviewed`;
- `unreviewed -> in_review` pela UI real;
- `NO_CHANGE` sem evento adicional;
- `needs_changes` sem nota rejeitado sem evento;
- `needs_changes` com nota persistido e projetado no Histórico;
- `approved` persistido como terceiro evento;
- Histórico consistente com o event log canônico;
- UI não oferece ação de reviewer quando `edit_others_posts` é negado pelo probe assinado;
- reflow contido em 1440 / 1024 / 782 / 492 px;
- uma coluna em `<=782px`;
- preservação de `post_status`, `post_content` e `_elementor_data`;
- Summary e Classificação conferidos pelos stores canônicos;
- event log com exatamente três eventos válidos;
- estado final de Review = `approved`;
- cleanup integral das fixtures.

## Decisão

**G-110: PASS determinístico + ambiental.**

A SPEC-003 pode avançar para **G-130 — Lifecycle / fechamento**.

A partir deste ponto, os runners e hooks temporários de G-070/G-110 deixam de ser necessários no runtime e devem ser removidos antes do RC.
