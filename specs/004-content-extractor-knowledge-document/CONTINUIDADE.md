# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- baseline de entrada: `0.3.0-rc.1`.
- SPEC-004: **ATIVA**.
- R-200 — Current State: **PASS**.
- R-210 — Extraction Contract: **PASS**.
- G-220 — Content Extractor: **PASS ambiental**.
- G-230 — Knowledge Document: **PASS ambiental — 2026-09-15**.
- próximo gate: **G-240 — Real Content Acceptance**.
- contrato de extração: `Extraction Contract v1.1.0`.
- contrato Knowledge Document: `Knowledge Document Contract v1.0.0`.
- G-245 — normalização Elementor/produção: planejado; writer ainda proibido.

## Evidência ambiental G-220

- `evidence/g220-smoke-20260915T221710Z.json`;
- WordPress `6.9.4` / PHP `8.5.10` / Elementor `4.1.0`;
- corpus 622 → 622;
- fingerprint editorial idêntico;
- zero changed posts;
- zero extractor errors/throwables;
- 21.969 fragments;
- readiness Elementor: 39 native / 505 projectable / 78 review_required / 0 blocked.

**G-220: PASS.**

## Evidência ambiental G-230

Arquivos:

- `evidence/g230-smoke-20260915T233450Z.json`;
- `g230-smoke-analysis.md`.

Ambiente:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- plugin `0.4.0-smoke.2`;
- Elementor `4.1.0`;
- Knowledge Document schema `1.0.0`;
- corpus: 622 posts.

Segurança:

- `read_only_design=true`;
- fingerprint editorial before/after idêntico;
- `changed_posts_during_run=0`;
- corpus `622 -> 622`;
- nenhum Knowledge Document/hash persistido;
- nenhum conteúdo, post ID, título ou URL exportado.

Determinismo real:

- first pass documents: `622`;
- second pass documents: `622`;
- errors: `0/0`;
- throwables: `0/0`;
- `hash_mismatches=0`;
- `canonical_json_mismatches=0`;
- `unique_source_hashes=601`;
- `unique_document_hashes=622`;
- sections total: `21.969`.

Performance das duas passagens:

- `3096 ms`;
- peak memory `31.457.280 bytes` (~30 MiB).

Hashes agregados da evidência:

- source: `53bac368abb9bbcf9d55b59db457aa6044e371ee7ab96e88b0374638a1cfc415`;
- document: `839012c3336a3c6323be1b5bd2abc18146cac67254c376dc0c8a966426d7c0dd`.

**G-230: PASS.**

## Direção editorial consolidada

- Elementor é o editor operacional padrão futuro.
- Legacy/Gutenberg/plain continuam suportados pelo knowledge plane por representarem o histórico real.
- Knowledge Document é editor-independent.
- normalização para Elementor é migration editorial explícita, separada da leitura e da instalação/update.

## Runtime permanente validado

### G-220

- `Content_Normalizer`;
- `Shortcode_Inspector`;
- `Legacy_HTML_Adapter`;
- `Content_Source`;
- `Elementor_Adapter`;
- `Gutenberg_Adapter`;
- `Content_Extractor`.

### G-230

- `Canonical_JSON`;
- `Knowledge_Document`;
- schema `1.0.0`;
- `source_hash` semântico;
- `document_hash` canônico;
- sections e facts estruturais ordenados;
- warnings/proveniência/readiness;
- zero storage durável.

## Política de hashes

- `source_hash` representa o conhecimento semanticamente extraído; não é identificador de post.
- conteúdos semanticamente equivalentes podem compartilhar `source_hash` — no corpus atual são 601 hashes para 622 posts.
- `document_hash` representa a projeção canônica do documento; na evidência atual são 622 hashes distintos.
- URL/data operacional não invalidam o hash semântico.
- fingerprint bruto continua reservado ao futuro `STALE_SOURCE` do plano de migration Elementor.

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

## Próximo passo exato — G-240

1. consolidar G-220/G-230 em `main`;
2. abrir branch dedicada G-240;
3. selecionar amostra representativa a partir do corpus real, cobrindo:
   - Elementor native;
   - Elementor/mixed;
   - legacy HTML típico e complexo;
   - Gutenberg;
   - shortcode/tabela;
   - `review_required`;
   - vazio/corrompido quando aplicável;
4. criar runner read-only de aceitação que permita inspeção controlada sem persistir conteúdo;
5. comparar fonte editorial e Knowledge Document com critérios objetivos de cobertura e ordem;
6. repetir hashes e provar zero mutação na amostra;
7. somente após G-240 avaliar avanço do plano de normalização Elementor G-245.

## Gates

- R-200: **PASS**.
- R-210: **PASS**.
- G-220: **PASS**.
- G-230: **PASS**.
- G-240: **READY — próximo gate**.
- G-245: **PLANNED — writer não autorizado**.
- G-250: **NOT_RUN**.
