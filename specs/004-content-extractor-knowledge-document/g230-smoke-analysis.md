# G-230 — Análise do smoke ambiental

## Resultado

**PASS — 2026-09-15.**

Evidência: `evidence/g230-smoke-20260915T233450Z.json`.

## Ambiente

- WordPress `6.9.4`;
- PHP `8.5.10`;
- plugin `0.4.0-smoke.2`;
- Elementor `4.1.0`;
- Knowledge Document schema `1.0.0`;
- multisite: não.

## Segurança

- `read_only_design=true`;
- não exporta conteúdo editorial, IDs, títulos ou URLs;
- não persiste Knowledge Documents ou hashes;
- fingerprint editorial before/after idêntico;
- `changed_posts_during_run=0`;
- corpus `622 -> 622`.

## Determinismo ambiental

O runner executou duas construções independentes para cada post:

- first pass documents: `622`;
- second pass documents: `622`;
- first pass errors: `0`;
- second pass errors: `0`;
- first pass throwables: `0`;
- second pass throwables: `0`;
- `hash_mismatches=0`;
- `canonical_json_mismatches=0`.

Isso comprova no ambiente WordPress real que, para o mesmo estado editorial, o builder produz hashes e serialização canônica reproduzíveis.

## Cardinalidade dos hashes

- `unique_source_hashes=601`;
- `unique_document_hashes=622`;
- total de posts: `622`.

A existência de 601 `source_hash` distintos para 622 posts não é falha: `source_hash` representa conhecimento semântico extraído, portanto conteúdos semanticamente equivalentes podem compartilhar hash. Já `document_hash` incorpora identidade/documento canônico suficiente para distinguir os 622 documentos nesta execução.

Não usar a cardinalidade de `source_hash` como identificador de post.

## Estrutura

- sections total: `21.969`;
- source kinds: 536 legacy_html / 41 plain_text / 34 elementor / 5 mixed / 4 gutenberg / 2 empty;
- readiness Elementor: 39 native / 505 projectable / 78 review_required / 0 blocked.

A distribuição permanece coerente com G-220.

## Hashes agregados da execução

- aggregate source hash: `53bac368abb9bbcf9d55b59db457aa6044e371ee7ab96e88b0374638a1cfc415`;
- aggregate document hash: `839012c3336a3c6323be1b5bd2abc18146cac67254c376dc0c8a966426d7c0dd`.

Eles servem como evidência ambiental do corpus naquele instante, não como substitutos dos hashes por documento.

## Performance

- runtime total das duas passagens: `3096 ms`;
- peak memory: `31.457.280 bytes` (~30 MiB).

O resultado não cria evidência para storage/cache durável no escopo atual.

## Decisão

G-230 está fechado.

Próximo gate: **G-240 — Real Content Acceptance**.

G-240 deve validar amostra real representativa e comparar fonte editorial versus Knowledge Document por inspeção controlada, sem introduzir writer, renderização arbitrária ou IA para reparar divergências.
