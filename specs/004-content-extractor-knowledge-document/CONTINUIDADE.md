# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- baseline consolidado em `main`: commit `a676f8daaaf9a794d503ccd6a2c28178be1fb8bf`.
- R-200: **PASS**.
- R-210: **PASS**.
- G-220 — Content Extractor: **PASS ambiental**.
- G-230/v1 — Knowledge Document determinístico: **PASS ambiental**.
- G-240/v1 — Real Content Acceptance: **FAIL CONTROLADO — perda estrutural**.
- G-240/v2 — smoke `0.4.0-acceptance.2`: **FAIL CONTROLADO — 82/622 structure_incomplete**.
- remediação atual: **diagnóstico estrutural agregado / `0.4.0-acceptance.3`**.
- branch: `spec004-g240-real-content-acceptance`.
- PR #3: **DRAFT / NÃO MERGEAR** antes de G-240 v2 PASS.
- G-245 — Elementor/produção: **BLOCKED por G-240**; writer proibido.

## Evidência G-240 v1

Arquivo:

- `evidence/g240-acceptance-20260916T085721Z.json`.

Resultado:

- 8/8 slots revisados;
- 0/8 slots passaram;
- 0 stale;
- 0 repeatability failures;
- 0 selection mismatches;
- fingerprint editorial igual;
- 0 posts alterados.

Padrão humano:

- 8/8 `coverage_complete=true`;
- 8/8 `order_preserved=true`;
- 8/8 `no_invented_text=true`;
- 7/8 `structure_adequate=false`, reason `structure_loss`.

Conclusão: o v1 é determinístico e textual/ordinalmente fiel na amostra, mas sua projeção plana perde relações semânticas.

Análise de causa raiz:

- `g240-failure-analysis-20260916.md`.

## Causa raiz v1

- `<li>` era preservado como texto, mas sem identidade da lista, `ul/ol`, profundidade, pai/filho e índice;
- listas aninhadas podiam contaminar o texto do item pai;
- `<tr>` virava string `A | B`, perdendo `th/td`, linha/célula, `rowspan/colspan` e identidade da tabela;
- parágrafos/listas/tabelas não carregavam ancestry de headings;
- Elementor `text-editor` e Gutenberg convergiam para o mesmo modelo plano;
- `sections[]` v1 não tinha uma estrutura canônica de árvore/lista/tabela.

## Decisão de arquitetura

Knowledge Document v1 permanece como evidência histórica de G-230 e determinismo, mas está:

**`SUPERSEDED_FOR_AI`**

Novo contrato ativo para remediação:

- `knowledge-document-contract-v2.md` — **FROZEN `2.0.0`**.

Não haverá tentativa de mascarar falha via IA, renderização dinâmica ou migração Elementor.

## Knowledge Document v2

Novos elementos:

- `sections[]` continua como visão linear/diagnóstica;
- `heading_path[]` explícito;
- `blocks[]` como representação semântica canônica;
- listas com `ordered|unordered`, profundidade, item ID, parent item, item index e children;
- tabelas com caption, linhas, células, `header|data`, `rowspan` e `colspan`;
- `structure` ampliado com paragraphs/list_items/table_rows/table_cells;
- `ai_readiness` objetivo.

### AI readiness

Estados:

- `candidate_ready`: estrutura reconciliada e sem gaps críticos conhecidos;
- `review_required`: estrutura representada, mas warnings semânticos permanecem;
- `not_ready`: perda estrutural objetiva, hard limit, fallback crítico ou conteúdo inesperadamente ausente;
- `not_applicable`: fonte realmente vazia.

Esse status é técnico e explicável. Não substitui a aceitação humana.

## Smoke ambiental v2 — `0.4.0-acceptance.2`

Evidência versionada:

- `evidence/kd-v2-smoke-20260916T100412Z.json`.

Ambiente:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- Elementor `4.1.0`;
- Knowledge Document schema `2.0.0`;
- `DOMDocument=true`;
- multisite=false.

Segurança/determinismo:

- 622 posts antes e depois;
- fingerprint editorial antes/depois idêntico;
- `changed_posts_during_run=0`;
- 622/622 documentos na primeira passagem;
- 622/622 documentos na segunda passagem;
- zero errors;
- zero throwables;
- zero hash mismatches;
- zero canonical JSON mismatches.

Bloqueio:

- `first_pass_structure_incomplete=82`;
- `second_pass_structure_incomplete=82`;
- `ai_readiness`: 495 `candidate_ready`, 82 `not_ready`, 43 `review_required`, 2 `not_applicable`;
- `gate.pass=false`.

Conclusão: o parser é determinístico e read-only no corpus real, mas o critério de reconciliação estrutural ainda encontra 82 documentos divergentes. Não há evidência suficiente para alterar semântica/runtime no escuro.

## Diagnóstico `0.4.0-acceptance.3`

Objetivo: explicar os 82 casos sem exportar conteúdo ou identificadores.

O runner `Validação KD v2` passa a relatório temporário schema `1.1.0` e adiciona agregados:

- `structure_incomplete_by_source_kind`;
- `structure_incomplete_by_strategy`;
- `structure_incomplete_by_elementor_compatibility`;
- `structure_mismatch_metrics`;
- `structure_mismatch_signatures`;
- `ai_readiness_reasons`.

O parser estrutural, Knowledge Document v2, hashes e regra do gate **não foram relaxados nem modificados** nesta etapa.

Package:

- `package-acceptance3.md`;
- versão `0.4.0-acceptance.3`;
- SHA-256 `31ad45e85053409cb7dce4b7547703d4f7a9c52b156174c9ff688c24ed5931f5`;
- PHP lint 23/23 PASS;
- JS syntax PASS;
- teste sintético KD v2 PASS;
- ZIP integrity PASS;
- staging ↔ ZIP parity PASS;
- blobs críticos Git ↔ package iguais.

## Aceitação humana v2

A pergunta subjetiva “aceitável para busca/IA” permanece removida.

O operador avalia somente:

1. cobertura completa;
2. ordem semântica preservada;
3. nenhum texto inventado;
4. estrutura semântica preservada.

A tela exibe `ai_readiness` calculado e renderiza semanticamente listas/tabelas/headings.

## Amostra A/B congelada

O reteste usa exatamente os mesmos posts que revelaram a falha v1:

- `elementor_native_typical`: 44981;
- `elementor_or_mixed_complex`: 1290;
- `legacy_typical`: 370;
- `legacy_complex`: 1307;
- `gutenberg`: 45782;
- `shortcode_or_table`: 36431;
- `review_required`: 1289;
- `empty_or_corrupt`: 28748.

Os fingerprints editoriais observados no v1 são baseline A/B. Qualquer alteração posterior marca o item como `stale`.

## Sequência obrigatória atual

1. instalar/substituir pelo `0.4.0-acceptance.3` em homologação;
2. não executar **Aceitação G-240 v2**;
3. executar somente **Base de Conhecimento → Validação KD v2**;
4. analisar os agregados para localizar a dimensão exata dos 82 mismatches;
5. corrigir apenas a causa comprovada;
6. reempacotar e reexecutar full-corpus;
7. exigir `structure_incomplete=0`, zero errors/throwables/mismatch, fingerprint igual e zero writes;
8. somente após esse PASS executar **Aceitação G-240 v2**;
9. somente G-240 v2 PASS libera G-245.

## Produção / Elementor

Permanece inalterado:

- Elementor é direção editorial futura;
- Knowledge plane é multi-source e read-only;
- installation/activation/update nunca migra posts automaticamente;
- migration editorial exige Production Preflight, version gate, dry-run, journal/rollback, stale-source guard, canário e batches retomáveis;
- writer continua desabilitado.

## Gates

- R-200: **PASS**.
- R-210: **PASS**.
- G-220: **PASS**.
- G-230/v1: **PASS determinístico**.
- G-240/v1: **FAIL CONTROLADO — STRUCTURE LOSS**.
- G-240/v2: **FAIL CONTROLADO — 82 STRUCTURE_INCOMPLETE / DIAGNOSTIC ACTIVE**.
- G-245: **BLOCKED**.
- G-250: **NOT_RUN**.
