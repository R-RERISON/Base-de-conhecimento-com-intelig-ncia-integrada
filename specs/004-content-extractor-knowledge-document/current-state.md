# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline

- baseline de entrada: `0.3.0-rc.1`;
- SPEC-001 Summary: concluída;
- SPEC-002 Classificação: concluída;
- SPEC-003 Review & Governança: concluída;
- R-200: **PASS**;
- R-210: **PASS**;
- G-220: **IMPLEMENTED / LOCAL PASS / ENV SMOKE PENDING**;
- package ativo de homologação: `0.4.0-smoke.1`.

Contratos:

- `extraction-contract-v1.md` — v1.0.0;
- `extraction-contract-v1.1.md` — v1.1.0;
- `elementor-normalization-contract-v1.md`;
- `production-rollout-contract-v1.md`.

## Direção editorial

Elementor é o editor padrão atual da equipe. O corpus majoritariamente Legacy HTML é consequência histórica de migrações anteriores e não define a direção editorial futura.

A arquitetura separa:

1. **knowledge normalization** — read-only, multi-source e editor-independent;
2. **editorial normalization para Elementor** — migration futura, explícita, versionada, auditável e reversível.

O plugin não vira CMS próprio.

## Evidência R-200

Corpus: 622 posts.

Distribuição estatística exclusiva:

- Legacy HTML: 496 (79,74%);
- Elementor: 74;
- Plain text: 31;
- Shortcode/plain: 10;
- Mixed Elementor + blocks: 6;
- Gutenberg: 3;
- Empty: 2.

Elementor:

- presente em 80;
- 39 JSON válidos;
- 41 inválidos;
- widgets observados: `text-editor` e `shortcode`.

Gutenberg:

- 9 posts com blocos;
- `core/freeform`, `core/heading`, `core/paragraph`, `core/list`, `core/table`.

Segurança do profiler:

- fingerprint before/after idêntico;
- 0 posts alterados;
- corpus 622 → 622.

## G-220 implementado

Componentes permanentes:

- `Content_Normalizer`;
- `Shortcode_Inspector`;
- `Legacy_HTML_Adapter`;
- `Content_Source`;
- `Elementor_Adapter`;
- `Gutenberg_Adapter`;
- `Content_Extractor`.

Características:

- read-only;
- sem hook/job próprio no núcleo do extractor;
- sem storage;
- sem IA/rede externa;
- sem `do_shortcode()`;
- sem `render_block()`;
- sem renderização Elementor;
- sem writers editoriais;
- soft budget 256 KiB / hard safety limit 1 MiB;
- sem truncamento silencioso;
- `DOMDocument` opcional com fallback estrutural determinístico.

## Readiness Elementor

A saída intermediária classifica:

- `native`;
- `projectable`;
- `review_required`;
- `blocked`.

Isso não grava `_elementor_data`; serve de base ao futuro Projection Plan.

## Validação local

`g220-local-validation.md`:

- lint: PASS;
- 14/14 unit tests: PASS;
- zero-write após cada caso;
- repetibilidade: PASS;
- fallback sem `DOMDocument`: PASS.

## Package `0.4.0-smoke.1`

SHA-256:

`3dad9f5f7c01e7970d314a0d0788756ad694cc9f3b9327a2834ead90b86e4c8f`

Validações:

- PHP lint antes/depois da extração do ZIP: 20/20 PASS;
- JS syntax: PASS;
- ZIP integrity: PASS;
- source parity novos/alterados: 9/9 PASS;
- 14/14 testes executados contra o conteúdo extraído do próprio ZIP.

Flags:

- `BDC_KB_SPEC004_PROFILE_BUILD=false`;
- `BDC_KB_SPEC004_SMOKE_BUILD=true`.

O antigo profiler permanece fisicamente até cleanup, mas não é carregado/registrado.

### Smoke runner temporário

`Content_Extractor_Smoke` adiciona somente no build de homologação:

**Base de Conhecimento → Smoke G-220**

Controles:

- `manage_options`;
- POST + nonce;
- nenhuma persistência;
- nenhum conteúdo/ID/título/URL exportado;
- fingerprint editorial before/after;
- agregação de source kinds, warnings, strategies, facts, readiness e performance.

Este runner não poderá chegar ao RC/produção.

## Produção

Homologação é cópia da produção, mas o rollout futuro será target-aware.

G-245 exigirá:

- Production Preflight;
- matriz WordPress/PHP/Elementor/plugins;
- versionamento independente de runtime/schema/projection/migration;
- dry-run;
- journal/rollback;
- stale-source guard;
- canário;
- batches retomáveis;
- runbook.

Activation/update nunca executará migration editorial para Elementor.

## Próximo passo

Instalar `0.4.0-smoke.1` em homologação, executar smoke das funcionalidades existentes e o runner G-220, retornar o JSON e fechar o gate ambiental somente se:

- fingerprint equal = true;
- changed posts = 0;
- corpus count unchanged;
- extractor errors = 0;
- throwables = 0.

Depois disso inicia G-230.
