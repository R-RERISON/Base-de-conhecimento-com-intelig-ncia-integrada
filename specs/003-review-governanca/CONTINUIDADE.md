# Continuidade — SPEC-003 Review & Governança

## Estado final

- SPEC-001: concluída.
- SPEC-002: concluída, baseline `0.2.0-rc.1`.
- SPEC-003: **CONCLUÍDA**.
- baseline funcional congelada: **`0.3.0-rc.1`**.
- próxima etapa autorizada: **SPEC-004 — Content Extractor e Knowledge Document**.

## Gates fechados

- R-001: PASS.
- R-010: PASS.
- G-001: PASS.
- G-030: PASS.
- DS-010: PASS.
- G-070: PASS — 22/22, cleanup zero.
- G-110: PASS — 22/22 browser, 7/7 server, cleanup zero.
- G-130: PASS ambiental — RC instalado/substituído, smoke do Workspace e lifecycle deactivate/activate confirmados pelo operador.

## Evidências principais

- `evidencias/bdc-kb-review-http-security-20260915-165537.json`;
- `evidencias/bdc-kb-g110-browser-acceptance-20260915-200043.json`;
- `evidencia-g110-dev11-pass.md`;
- `evidencia-g130-rc1-pass.md`;
- `package-0.3.0-rc.1-clean.md`.

## Baseline `0.3.0-rc.1`

SHA-256 do package validado:

`7f681a3f62d792d30ccb016ae64b03e83d5cc46c4b2b1e2c2d96d41e3dfd0db5`

Garantias congeladas:

- Workspace com cinco tabs: Visão geral, Summary, Classificação, Review & Governança e Histórico;
- Summary e Classificação persistem em seus stores canônicos e retornam à própria tab após save;
- Review usa Comments API append-only com `comment_type=bdc_kb_review_event`;
- estado atual de Review = último evento válido;
- Histórico = projection read-only do event log;
- `post_status` permanece independente da governança;
- sem meta paralela de estado de Review;
- sem tabela customizada de Review;
- sem writer próprio para Histórico;
- sem `AI Ready` ou score no domínio SPEC-003;
- zero runners/flags de homologação no RC;
- lifecycle deactivate/activate sem regressão observada.

## Contrato editorial herdado pela próxima SPEC

A SPEC-004 não recebe autorização para editar a fonte editorial. Permanecem proibidos por leitura/análise:

- write em `post_content`;
- write em `_elementor_data`;
- mudança de `post_status`;
- publicação automática;
- criação de revisão editorial como efeito colateral;
- cópia editorial concorrente.

## Handoff

A SPEC-004 deve partir de `0.3.0-rc.1` e iniciar por Current State/Discovery. A primeira entrega autorizada é inventariar de forma read-only como o corpus real está distribuído entre Elementor, Gutenberg/blocos, HTML legado e shortcodes, antes de definir o contrato definitivo do Content Extractor e do Knowledge Document.
