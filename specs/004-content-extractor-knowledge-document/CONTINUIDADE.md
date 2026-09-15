# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- baseline de entrada: `0.3.0-rc.1`.
- SPEC-004: **ATIVA**.
- R-200 — Current State: **PASS**.
- R-210 — Extraction Contract: **PASS**.
- G-220 — Content Extractor: **PASS ambiental**.
- G-230 — Knowledge Document: **IMPLEMENTED / LOCAL PASS / ENV SMOKE PENDING**.
- contrato de extração: `Extraction Contract v1.1.0`.
- contrato Knowledge Document: `Knowledge Document Contract v1.0.0`.
- package ambiental ativo: **`0.4.0-smoke.2`**.
- G-245 — normalização Elementor/produção: planejado; writer ainda proibido.

## Evidência ambiental G-220

Arquivo:

- `evidence/g220-smoke-20260915T221710Z.json`.

Ambiente observado:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- Elementor `4.1.0`;
- plugin `0.4.0-smoke.1`;
- corpus: 622 posts.

Segurança/gate:

- fingerprint editorial before/after idêntico;
- zero posts alterados;
- corpus 622 → 622;
- `extractor_errors=0`;
- `throwables=0`;
- sem shortcode/widget/dynamic block rendering;
- sem persistência.

Resultado do extractor:

- `legacy_html`: 536;
- `plain_text`: 41;
- `elementor`: 34;
- `mixed`: 5;
- `gutenberg`: 4;
- `empty`: 2;
- fragments: 21.969.

Readiness Elementor:

- `native`: 39;
- `projectable`: 505;
- `review_required`: 78;
- `blocked`: 0.

Logo 544/622 (87,46%) estão nativos ou projetáveis; os 78 restantes exigem revisão controlada.

## Direção editorial

- Elementor é o editor operacional padrão futuro.
- Legacy/Gutenberg/plain continuam suportados pelo knowledge plane porque representam o histórico real.
- Knowledge Document é editor-independent.
- convergência para Elementor é migration editorial explícita, separada da leitura e separada de installation/update.

## Runtime permanente G-220

- `Content_Normalizer`;
- `Shortcode_Inspector`;
- `Legacy_HTML_Adapter`;
- `Content_Source`;
- `Elementor_Adapter`;
- `Gutenberg_Adapter`;
- `Content_Extractor`.

Todos read-only, sem IA, storage, cron, renderização arbitrária ou writers editoriais.

## Runtime G-230

Componentes implementados:

- `Canonical_JSON`;
- `Knowledge_Document`.

Schema v1:

- identidade/proveniência do post;
- `source_kind`;
- `source_hash`;
- `document_hash`;
- título;
- URL/data operacional;
- `sections[]` ordenadas;
- facts estruturais;
- estratégias/warnings;
- `elementor_compatibility` somente como readiness.

### Hashes

`source_hash` cobre conhecimento semântico extraído e não hashes/tamanhos brutos.

`document_hash` cobre a projeção canônica, excluindo:

- o próprio hash;
- `canonical_url`;
- `modified_gmt`.

Isso evita invalidar o conhecimento por mudança puramente operacional de URL/timestamp.

## Validação local G-230

- `g230-local-validation.md`;
- Knowledge Document tests: **10/10 PASS**;
- regressão Content Extractor no mesmo package: **14/14 PASS**;
- zero-write: PASS;
- canonical JSON byte-a-byte: PASS;
- mudança semântica/título/ordem altera hash: PASS;
- URL/data e ruído bruto não utilizado não alteram hash semântico: PASS.

## Package ambiental — `0.4.0-smoke.2`

Documento: `package-smoke2.md`.

SHA-256:

`1466cd4fcd18120d0b2405bf04ec629230f23c2a2e759869a8123c45cedaf204`

Validação:

- ZIP: 25 arquivos;
- PHP lint extraído: **21/21 PASS**;
- JS syntax: PASS;
- ZIP integrity: PASS;
- source parity: **25/25 PASS**;
- G-220 tests no package: **14/14 PASS**;
- G-230 tests no package: **10/10 PASS**.

Build flags:

- profiler R-200: OFF;
- smoke G-220: OFF;
- smoke G-230: ON.

Menu esperado:

**Base de Conhecimento → Smoke G-230**

O runner constrói cada Knowledge Document duas vezes, compara hashes e JSON canônico e exporta somente métricas agregadas.

## Produção / normalização Elementor

Contratos ativos:

- `elementor-normalization-contract-v1.md`;
- `production-rollout-contract-v1.md`.

Regras permanentes:

- activation/update não migra posts;
- Production Preflight antes de promoção;
- writer futuro atrás de `Elementor_Gateway` version-gated;
- dry-run, journal/rollback, stale-source guard, canário e batches retomáveis;
- plugin rollback e editorial rollback independentes.

## Próximo passo exato

1. instalar/substituir o plugin por `0.4.0-smoke.2` em homologação;
2. confirmar versão `0.4.0-smoke.2`;
3. confirmar que Smoke G-220/Profiler antigo não aparecem;
4. abrir **Base de Conhecimento → Smoke G-230**;
5. evitar edição concorrente;
6. executar **Smoke G-230 e baixar JSON**;
7. retornar `bdc-kb-spec004-g230-smoke-*.json`;
8. exigir:
   - `editorial_fingerprint_equal=true`;
   - `changed_posts_during_run=0`;
   - corpus 622 → 622;
   - first/second pass documents = 622;
   - errors = 0;
   - throwables = 0;
   - `hash_mismatches=0`;
   - `canonical_json_mismatches=0`;
9. se PASS, fechar G-230 e iniciar G-240 Real Content Acceptance.

## Gates

- R-200: **PASS**.
- R-210: **PASS**.
- G-220: **PASS**.
- G-230: **IMPLEMENTED / LOCAL PASS — ENV SMOKE PENDING**.
- G-240: **BLOCKED por G-230**.
- G-245: **PLANNED — writer não autorizado**.
- G-250: **NOT_RUN**.
