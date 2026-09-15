# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- baseline de entrada: `0.3.0-rc.1`.
- SPEC-004: **ATIVA**.
- R-200 — Current State do corpus: **PASS**.
- R-210 — Extraction Contract: **PASS**.
- contrato congelado: **`Extraction Contract v1.0.0`**.
- próxima etapa: **G-220 — Content Extractor determinístico**.
- nenhuma mudança permanente de runtime da SPEC-004 foi implementada nesta branch de fechamento documental.

## Evidência ambiental R-200

Execução:

- build: `0.4.0-profile.1`;
- data: `2026-09-15T21:33:42+00:00`;
- WordPress: `6.9.4`;
- PHP: `8.5.10`;
- corpus: 622 posts.

Arquivos:

- `evidence/r200-content-profile-20260915T213342Z.json`;
- `r200-corpus-analysis.md`;
- `extraction-contract-v1.md`.

### Segurança comprovada

- `editorial_fingerprint_equal=true`;
- `changed_posts_during_run=0`;
- corpus count `622 -> 622`;
- sem execução de shortcode;
- sem render Elementor;
- sem dynamic block rendering;
- sem persistência de progresso/resultado;
- sem exportação de corpo editorial, títulos, URLs ou IDs.

## Descobertas principais do corpus

Distribuição exclusiva:

- `legacy_html`: 496 / 79,74%;
- `elementor`: 74 / 11,90%;
- `plain_text`: 31 / 4,98%;
- `shortcode_plain`: 10 / 1,61%;
- `mixed_elementor_blocks`: 6 / 0,96%;
- `gutenberg`: 3 / 0,48%;
- `empty`: 2 / 0,32%.

Elementor:

- presente em 80;
- JSON válido: 39;
- JSON inválido: 41;
- widgets confirmados: `text-editor` e `shortcode`;
- nenhum JSON válido ficou sem campo semântico conhecido.

Gutenberg observado:

- `core/freeform`;
- `core/heading`;
- `core/paragraph`;
- `core/list`;
- `core/table`.

Shortcodes:

- profiler encontrou tags reais prováveis e falsos positivos por texto técnico entre colchetes;
- runtime definitivo não poderá usar detecção ingênua por regex como verdade semântica;
- execução genérica por `do_shortcode()` permanece proibida.

## Decisões congeladas no Extraction Contract v1

- detecção por flags independentes antes de source selection;
- Legacy HTML é adapter de primeira classe e caminho dominante do corpus atual;
- Elementor válido usa traversal allowlisted;
- Elementor inválido falha de forma isolada e tenta `post_content` seguro;
- Gutenberg usa estrutura de blocos sem render dinâmico por padrão;
- `core/freeform` delega ao adapter HTML legado;
- shortcodes só são reconhecidos quando registrados/allowlisted e nunca executados genericamente;
- `bdc_resumo_executivo` não duplica Summary no corpo editorial;
- renderização completa permanece desabilitada por padrão;
- fallback renderizado só poderá ser habilitado com evidência de G-240 e budget medido;
- soft source budget: 256 KiB;
- hard source safety limit: 1 MiB;
- sem truncamento silencioso;
- sem tabela/cache durável nesta etapa.

## Build temporário

O profiler `0.4.0-profile.1` continua presente apenas porque G-250 ainda não foi executado.

Package original: `package-profile1.md`.

SHA-256 do ZIP:

`eeae2f7a5c37dead27bd21f486bea7a64b75d510392a35b742ec6eb338a59bdd`

Ele deve ser removido antes de `0.4.0-rc.1`.

## Próximo passo exato — G-220

Implementar, somente após este contrato documental estar aceito/mergeado:

1. detector de origem/flags;
2. adapter Elementor;
3. adapter Gutenberg;
4. adapter Legacy HTML;
5. normalizador estrutural/textual;
6. política de shortcodes sem execução;
7. warnings/códigos de integridade;
8. guardrails de tamanho;
9. fallback estrutural fail-soft;
10. unit tests e repetibilidade.

O runtime deve seguir `extraction-contract-v1.md` sem introduzir renderização arbitrária, storage durável, IA ou write editorial.

## Gates

- R-200: **PASS**.
- R-210: **PASS**.
- G-220: **READY / NOT_STARTED**.
- G-230: bloqueado por G-220.
- G-240: bloqueado por G-230.
- G-250: NOT_RUN.
