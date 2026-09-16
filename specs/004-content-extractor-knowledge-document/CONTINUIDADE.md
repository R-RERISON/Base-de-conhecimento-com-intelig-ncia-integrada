# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- baseline consolidado em `main`: commit `a676f8daaaf9a794d503ccd6a2c28178be1fb8bf`.
- R-200: **PASS**.
- R-210: **PASS**.
- G-220 — Content Extractor: **PASS ambiental**.
- G-230/v1 — Knowledge Document determinístico: **PASS ambiental**.
- G-240/v1 — Real Content Acceptance: **FAIL CONTROLADO — perda estrutural**.
- remediação atual: **Knowledge Document v2 / structural remediation**.
- branch: `spec004-g240-real-content-acceptance`.
- PR #3: **DRAFT / NÃO MERGEAR** antes de novo G-240 PASS.
- G-245 — Elementor/produção: **BLOCKED por G-240**; writer proibido.

## Evidência que bloqueou G-240

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
- 7/8 `structure_adequate=false`, reason `structure_loss`;
- 0/8 marcaram “acceptable_for_knowledge_use”.

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

Não haverá tentativa de mascarar a falha via IA, renderização dinâmica ou migração Elementor.

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
- `review_required`: estrutura representada, mas warnings semânticos permanecem (shortcode/widget/bloco dinâmico etc.);
- `not_ready`: perda estrutural objetiva, hard limit, fallback crítico ou conteúdo inesperadamente ausente;
- `not_applicable`: fonte realmente vazia.

Esse status não é uma aprovação genérica da IA. É um sinal técnico para consumidores futuros e não substitui a aceitação humana.

## Aceitação humana v2

A pergunta subjetiva “aceitável para busca/IA” foi removida.

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

Os fingerprints editoriais observados no v1 viram baseline A/B. Qualquer alteração posterior marca o item como `stale`.

## Implementação v2 já presente no draft

- `class-legacy-html-adapter.php`: preservação estrutural de listas/tabelas;
- `class-semantic-structure.php`: heading ancestry, blocks, reconciliação estrutural e AI readiness;
- `class-knowledge-document.php`: schema `2.0.0`, blocks e hashes v2;
- `class-knowledge-document-v2-smoke.php`: duas passagens full-corpus;
- `class-real-content-acceptance-v2.php`: reteste A/B dos mesmos oito casos;
- `tests/unit/spec004-semantic-structure-v2.php`: testes sintéticos estruturais.

Build de desenvolvimento atual: `0.4.0-acceptance.2`.

## Sequência obrigatória antes de novo G-240

1. fechar validação local/package `0.4.0-acceptance.2`;
2. em homologação executar **Base de Conhecimento → Validação KD v2**;
3. exigir duas passagens completas, zero errors/throwables/hash mismatch/JSON mismatch;
4. exigir `structure_incomplete=0`, fingerprint igual e zero writes;
5. somente após esse PASS executar **Aceitação G-240 v2**;
6. revisar os mesmos oito casos com os quatro critérios observáveis;
7. qualquer falha estrutural mantém G-240 bloqueado;
8. somente G-240 v2 PASS libera G-245.

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
- G-240/v2: **REMEDIATION ACTIVE / ENV SMOKE PENDING**.
- G-245: **BLOCKED**.
- G-250: **NOT_RUN**.
