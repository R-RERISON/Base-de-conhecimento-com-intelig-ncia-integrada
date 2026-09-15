# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline de entrada

- plugin: `0.3.0-rc.1`;
- SPEC-001 Summary: concluída;
- SPEC-002 Classificação: concluída;
- SPEC-003 Review & Governança: concluída;
- Workspace/Histórico: baseline ambiental aprovada;
- runtime temporário de homologação anterior: removido.

## Estado dos gates

- R-200 — Current State do corpus: **PASS**;
- R-210 — Extraction Contract: **PASS**;
- G-220 — Extractor determinístico: **READY / NOT_STARTED**.

O contrato congelado é `extraction-contract-v1.md`, versão `1.0.0`.

## O que existe no novo plugin

O runtime permanente atual ainda não possui `Content_Extractor`, `Knowledge_Document`, índice, chunks, embeddings ou tabela derivada de conteúdo. Portanto o fechamento de R-200/R-210 não introduziu dívida de runtime nesse domínio.

O build temporário `0.4.0-profile.1` contém somente o profiler read-only e deve ser removido em G-250 antes do RC.

O plugin atual já possui contratos que a SPEC-004 não pode quebrar:

- Summary tem owner próprio;
- Classificação tem owner próprio;
- Review usa event log canônico;
- Histórico é read-only;
- Workspace é a superfície administrativa integrada;
- fonte editorial não pertence ao plugin.

## Fonte editorial autorizada

A arquitetura do projeto define:

- `WP_Post` + Elementor como fonte editorial;
- leitura autorizada de `post_content`;
- leitura autorizada de `_elementor_data`;
- HTML renderizado apenas como fallback controlado;
- escrita em `_elementor_data` proibida.

A evidência do R-200 mostrou, porém, que a **representação predominante do corpus atual é Legacy HTML**, não Elementor. Isso muda a prioridade dos adapters sem mudar a autoridade editorial do WordPress.

## Evidência ambiental R-200

Execução sanitizada preservada em:

- `evidence/r200-content-profile-20260915T213342Z.json`;
- `r200-corpus-analysis.md`.

Ambiente:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- plugin `0.4.0-profile.1`;
- multisite: não;
- corpus: 622 posts.

Segurança:

- fingerprint before/after idêntico;
- zero posts alterados durante a execução;
- corpus 622 antes e 622 depois;
- sem execução de shortcode/widget/bloco dinâmico;
- sem persistência do profiler.

## Distribuição real do corpus

Source kind estatístico exclusivo:

- Legacy HTML: 496 (79,74%);
- Elementor: 74 (11,90%);
- Plain text: 31 (4,98%);
- Shortcode/plain: 10 (1,61%);
- Mixed Elementor + blocks: 6 (0,96%);
- Gutenberg: 3 (0,48%);
- Empty: 2 (0,32%).

Flags não exclusivas:

- Elementor: 80;
- Gutenberg: 9;
- HTML: 579;
- shortcode-like: 61;
- plain text: 620.

## Elementor — resultado da hipótese

Dos 80 posts com `_elementor_data`:

- 39 possuem JSON válido;
- 41 possuem JSON inválido;
- 0 possuem meta de tipo inesperado;
- 0 dos JSON válidos ficaram sem campo semântico conhecido.

Widgets confirmados:

- `text-editor`;
- `shortcode`.

Campos confirmados:

- `editor`;
- `shortcode`.

Conclusão: traversal semântico resolve os casos válidos observados sem necessidade de renderização. JSON inválido é frequente e deve ser tratado como condição normal fail-soft, com fallback seguro para `post_content` quando aplicável.

## Gutenberg — resultado da hipótese

Há 9 posts com blocos. Blocos observados:

- `core/freeform`;
- `core/heading`;
- `core/paragraph`;
- `core/list`;
- `core/table`.

Gutenberg é minoritário, mas deve possuir adapter dedicado. `core/freeform` deve reutilizar parsing Legacy HTML.

## Shortcodes — resultado da hipótese

O profiler registrou 108 ocorrências textuais, incluindo tags prováveis (`table`, `n2`, `wpt`, `caption`, `dbc_table`, `faq_wd`, `bdc_resumo_executivo`) e falsos positivos evidentes/fortes candidatos (`hkey_*`, `seu`, `tipo`, `banco`).

Conclusão: regex genérica sobre colchetes não pode determinar shortcode semântico. O contrato v1 exige tag registrada/allowlisted e proíbe execução genérica.

## Estruturas dominantes

No `post_content` bruto:

- headings: 1.020;
- listas: 3.700;
- tabelas: 513;
- imagens: 4.595;
- links: 10.208;
- code/pre: 83.

Isso obriga preservação explícita de boundaries de headings, listas, tabelas e código.

## Tamanhos e budgets

`post_content`:

- p50 3.929 B;
- p95 28.662 B;
- max 156.636 B.

`_elementor_data`:

- p50 0 B;
- p95 15.419 B;
- max 110.029 B.

Profiler:

- 1.253 ms para 622 posts;
- pico ~28 MiB.

Contrato v1:

- soft warning >256 KiB por fonte;
- hard safety limit 1 MiB por fonte;
- sem truncamento silencioso;
- sem justificativa atual para cache/storage durável.

## Prior art — KB2Ops

Referência: `R-RERISON/KB2Ops-Operational-Knowledge-Engine`, `plugin/kb2ops/includes/class-content-extractor.php`.

Comportamentos úteis observados:

- valida post type;
- mantém caches somente in-request;
- detecta Elementor pela presença de `_elementor_data`;
- percorre JSON Elementor com allowlist de campos textuais;
- tenta fallback renderizado apenas quando necessário;
- captura `Throwable` de renderização;
- expande somente shortcodes de tabela permitidos;
- preserva boundaries antes de remover markup;
- produz contagens estruturais de imagens/tabelas/headings/shortcodes.

Limitações que não serão herdadas automaticamente:

- traversal genérico por nome de chave;
- lista fixa não confrontada com corpus;
- ausência de Gutenberg dedicado;
- mistura entre extração e renderização;
- ausência de Knowledge Document versionado/hashado;
- fallback renderizado implícito.

## Hipóteses R-200 — resultado

1. **Confirmada:** Elementor válido observado pode ser extraído sem renderização completa.
2. **Confirmada com ressalva:** há tags adicionais, mas o profiler também revelou falsos positivos por colchetes técnicos.
3. **Confirmada:** existe volume dominante de HTML legado e volume não nulo de Gutenberg/plain.
4. **Confirmada:** há combinação mista Elementor + blocks.
5. **Confirmada:** tabelas/shortcodes exigem política própria.
6. **Confirmada:** há documentos grandes o suficiente para justificar guardrails, mas não para justificar persistência/cache durável.

## Risco principal atualizado

O maior risco continua sendo produzir representação aparentemente limpa que omite conteúdo operacional. Agora existem dois riscos quantitativamente comprovados:

1. tratar Elementor como dominante quando 79,74% do corpus é Legacy HTML;
2. considerar qualquer `[texto]` shortcode e remover/alterar conteúdo técnico legítimo.

## Próximo passo

Implementar G-220 estritamente conforme `extraction-contract-v1.md`, sem introduzir write editorial, renderização arbitrária, IA ou storage durável.
