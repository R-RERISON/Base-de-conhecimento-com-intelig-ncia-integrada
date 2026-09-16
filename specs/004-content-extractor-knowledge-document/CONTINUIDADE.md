# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- baseline consolidado em `main`: commit `a676f8daaaf9a794d503ccd6a2c28178be1fb8bf`.
- R-200: **PASS**.
- R-210: **PASS**.
- G-220 — Content Extractor: **PASS ambiental**.
- G-230/v1 — Knowledge Document determinístico: **PASS ambiental**.
- G-240/v1 — Real Content Acceptance: **FAIL CONTROLADO — perda estrutural**.
- G-240/v2 — `acceptance.2` e `acceptance.3`: **FAIL CONTROLADO — 82/622 structure_incomplete**.
- remediação atual: **correção controlada de colisões de IDs estruturais / `0.4.0-acceptance.4`**.
- branch: `spec004-g240-real-content-acceptance`.
- PR #3: **DRAFT / NÃO MERGEAR** antes de G-240 v2 PASS.
- G-245 — Elementor/produção: **BLOCKED por G-240**; writer proibido.

## Evidência G-240 v1

- `evidence/g240-acceptance-20260916T085721Z.json`.
- 8/8 slots revisados; 0/8 passaram; zero stale/repeatability/selection mismatch; fingerprint igual; zero posts alterados.
- 8/8 cobertura completa; 8/8 ordem preservada; 8/8 sem texto inventado; 7/8 `structure_loss`.

Conclusão: v1 determinístico e textual/ordinalmente fiel, porém estruturalmente insuficiente. v1 permanece `SUPERSEDED_FOR_AI`.

## Knowledge Document v2

Contrato ativo: `knowledge-document-contract-v2.md` — **FROZEN `2.0.0`**.

- `sections[]` para visão linear/diagnóstica;
- `heading_path[]` explícito;
- `blocks[]` semânticos;
- listas ordered/unordered, profundidade, item ID, parent/children;
- tabelas com caption, rows/cells, header/data, rowspan/colspan;
- `ai_readiness` objetivo: candidate_ready/review_required/not_ready/not_applicable.

A pergunta subjetiva “aceitável para IA” permanece removida da aceitação humana.

## Smoke `0.4.0-acceptance.3`

Evidência versionada:

- `evidence/kd-v2-smoke-20260916T101517Z.json`.

Ambiente:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- Elementor `4.1.0`;
- KD `2.0.0`;
- `DOMDocument=true`.

Segurança/determinismo:

- corpus `622 → 622`;
- fingerprint editorial idêntico;
- `changed_posts_during_run=0`;
- 622/622 documentos nas duas passagens;
- zero errors/throwables;
- zero hash mismatch;
- zero canonical JSON mismatch.

Bloqueio reproduzido:

- `structure_incomplete=82` nas duas passagens;
- `ai_readiness`: 495 candidate_ready, 82 not_ready, 43 review_required, 2 not_applicable.

Distribuição dos 82:

- legacy_html: 62;
- elementor: 14;
- mixed: 5;
- gutenberg: 1.

Métricas divergentes:

- headings: 47 documentos, expected 504, actual 302;
- lists: 47 documentos, expected 1196, actual 626;
- list_items: 40 documentos, expected 1540, actual 1107;
- tables: 8 documentos, expected 19, actual 10.

As assinaturas também apresentam casos `actual > expected` para `list_items`, o que é compatível com fusão incorreta de árvores e não apenas estruturas vazias.

## Diagnóstico técnico

Foram identificadas duas classes potenciais, mantidas separadas:

1. **colisão de IDs estruturais locais entre parciais**: cada widget/bloco pode produzir `list-0`, `table-0`, etc.; ao combinar resultados, `Semantic_Structure` agrupa por esses IDs e pode fundir estruturas independentes;
2. **Legacy HTML**: as 62 divergências restantes podem envolver diferença entre estrutura DOM observada e estrutura semanticamente emitida. Essa segunda causa ainda não será corrigida sem evidência adicional.

## `0.4.0-acceptance.4` — correção experimental isolada

Escopo desta rodada: corrigir **somente** a classe 1.

Foi introduzido namespace determinístico de `list_id`, `item_id`, `parent_item_id`, `table_id` e `image_id` antes de merges em:

- Elementor Adapter;
- Gutenberg Adapter;
- Content Extractor.

O helper canônico está em `Content_Normalizer::namespace_structural_ids()`.

Deliberadamente não alterados:

- Legacy HTML parser;
- `Semantic_Structure`;
- Knowledge Document schema/hash contract;
- regra `structure_incomplete=0`.

Regressão dedicada `tests/unit/spec004-structural-id-namespace.php` garante que duas listas/tabelas com IDs locais iguais permanecem estruturas independentes após merge.

Package:

- `package-acceptance4.md`;
- versão `0.4.0-acceptance.4`;
- SHA-256 `f70066dcf915f8e62428b7fe43becce3ea476be92f489305fbd59c3a19658159`;
- PHP lint 23/23 PASS;
- JS syntax PASS;
- ZIP integrity PASS;
- package parity 27/27 PASS;
- KD v2 regression 12/12 PASS;
- namespace regression PASS;
- safety scan PASS.

## Sequência obrigatória atual

1. instalar/substituir pelo `0.4.0-acceptance.4` em homologação;
2. **não executar Aceitação G-240 v2**;
3. executar somente **Base de Conhecimento → Validação KD v2**;
4. comparar distribuição de `structure_incomplete` contra `acceptance.3`;
5. se Elementor/mixed/Gutenberg zerarem ou reduzirem conforme esperado, considerar comprovada a correção de colisões;
6. se Legacy permanecer, diagnosticar/corrigir apenas sua causa comprovada;
7. repetir full-corpus até `structure_incomplete=0` com zero mutation/determinismo intacto;
8. somente então reexecutar os mesmos 8 casos da Aceitação G-240 v2;
9. somente G-240 v2 PASS libera G-245.

## Amostra A/B congelada

O reteste humano continua usando exatamente os mesmos oito posts do G-240 v1 e os fingerprints anteriores como baseline. Qualquer alteração editorial torna o slot `stale`.

## Produção / Elementor

Permanece inalterado:

- Elementor é direção editorial futura;
- Knowledge plane é multi-source e read-only;
- install/activation/update nunca migra posts automaticamente;
- migration editorial exige Production Preflight, version gate, dry-run, journal/rollback, stale-source guard, canário e batches retomáveis;
- writer continua desabilitado.

## Gates

- R-200: **PASS**.
- R-210: **PASS**.
- G-220: **PASS**.
- G-230/v1: **PASS determinístico**.
- G-240/v1: **FAIL CONTROLADO — STRUCTURE LOSS**.
- G-240/v2: **FAIL CONTROLADO / COLLISION REMEDIATION ENV SMOKE PENDING**.
- G-245: **BLOCKED**.
- G-250: **NOT_RUN**.
