# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- baseline consolidado em `main`: commit `a676f8daaaf9a794d503ccd6a2c28178be1fb8bf`.
- R-200: **PASS**.
- R-210: **PASS**.
- G-220 — Content Extractor: **PASS ambiental**.
- G-230/v1 — Knowledge Document determinístico: **PASS ambiental**; v1 permanece `SUPERSEDED_FOR_AI` após G-240 v1.
- G-240/v1 — Real Content Acceptance: **FAIL CONTROLADO — perda estrutural**.
- G-240/v2 — remediação ativa; `acceptance.7` reduziu `structure_incomplete` de 50 para **5/622** e eliminou todos os mismatches de headings.
- build atual: **`0.4.0-acceptance.8` diagnóstico-only**, KD `2.0.1`, smoke `1.3.0`.
- branch: `spec004-g240-real-content-acceptance`.
- PR #3: **DRAFT / NÃO MERGEAR** antes de G-240 v2 PASS.
- G-245 — Elementor/produção: **BLOCKED por G-240**; writer proibido.
- G-250: **NOT_RUN**.

## Contratos ativos

- `knowledge-document-contract-v2.md` — base `2.0.0`.
- `knowledge-document-contract-v2.0.1-amendment.md` — patch semântico ativo para `2.0.1`.

Invariantes preservados:

- construção read-only;
- nenhuma persistência de documento/hash/cache/progresso durante validação;
- nenhum write em `post_content`, `_elementor_data`, status, revisão ou publicação;
- nenhum `do_shortcode()` genérico;
- nenhum `render_block()` ou renderização dinâmica arbitrária;
- nenhuma dependência de IA, Foundry, embeddings ou vetores para parsing;
- `structure_incomplete=0` obrigatório;
- `ai_readiness.not_ready=0` obrigatório desde o smoke `1.3.0`.

## Histórico da remediação

### G-240 v1

Evidência: `evidence/g240-acceptance-20260916T085721Z.json`.

- 8/8 slots revisados;
- cobertura textual, ordem e ausência de invenção preservadas;
- 7/8 com `structure_loss`.

Conclusão: KD v1 era determinístico, porém semanticamente plano demais para listas/tabelas/hierarquia.

### `acceptance.3`

Evidência: `evidence/kd-v2-smoke-20260916T101517Z.json`.

- `structure_incomplete=82`;
- 62 legacy_html, 14 elementor, 5 mixed, 1 gutenberg;
- havia `actual > expected`, compatível com colisão/fusão de IDs estruturais locais.

### `acceptance.4`

Evidência: `evidence/kd-v2-smoke-20260916T104818Z.json`.

- 82 → 78;
- mixed 5 → 2;
- gutenberg 1 → 0;
- todas as assinaturas `actual > expected` desapareceram.

Conclusão: namespace determinístico removeu a classe de fusão/colisão.

### `acceptance.5`

Evidência: `evidence/kd-v2-smoke-20260916T112024Z.json`.

- 78 → 50;
- legacy 62 → 44;
- Elementor 14 → 5;
- mixed 2 → 1;
- `list_items` mismatch zerou;
- lists ficaram em 5 docs;
- headings dominaram o resíduo: 47 docs, `504 expected / 302 actual`;
- tables: 2 docs, `6 / 4`.

Conclusão: `structural_anchor` resolveu sublistas órfãs.

### `acceptance.6`

Evidência: `evidence/kd-v2-smoke-20260916T114435Z.json`.

Segurança/determinismo permaneceram PASS, mas `structure_incomplete` ficou em 50. A telemetria mostrou que wrappers históricos eram atravessados, porém existiam muitos headings vazios e headings locais em listas/tabelas.

Conclusão: o problema dominante não era wrapper; era comparar **tag DOM bruta** com **unidade semântica global**.

### Knowledge Document `2.0.1`

Foi introduzido `Semantic_DOM_Expectation`:

1. expected continua vindo diretamente do DOM bruto;
2. não usa `sections[]`/`blocks[]` para derivar expected;
3. heading vazio não conta como heading semântico;
4. heading local em lista/tabela/parágrafo/blockquote/code não é promovido ao outline global;
5. heading local gera `HTML_LOCAL_HEADING_FLATTENED:*` → `review_required`;
6. lista dentro de tabela achatada gera `HTML_NESTED_LIST_IN_TABLE_FLATTENED:*` → `review_required`;
7. tabela aninhada não representada gera `HTML_NESTED_TABLE_UNREPRESENTED:*` → `not_ready`.

O smoke `1.3.0` foi endurecido: `gate.pass=true` também exige `ai_readiness.not_ready=0`.

### `acceptance.7`

Evidência: `evidence/kd-v2-smoke-20260916T124150Z.json`.

Ambiente:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- Elementor `4.1.0`;
- Knowledge Document `2.0.1`;
- `DOMDocument=true`.

Segurança/determinismo:

- corpus `622 → 622`;
- fingerprint editorial idêntico;
- zero posts alterados;
- 622/622 documentos nas duas passagens;
- zero errors/throwables;
- zero hash mismatch;
- zero canonical JSON mismatch.

Resultado:

- `structure_incomplete`: **50 → 5**;
- headings mismatch: **47 docs → 0**;
- 4 casos residuais são `legacy_html`, 1 é Elementor;
- lists: 3 docs, expected 105, actual 90, delta 15;
- tables: 2 docs, expected 6, actual 4, delta 2;
- `ai_readiness`: 546 candidate_ready, 69 review_required, 5 not_ready, 2 not_applicable;
- gate permanece FAIL por `structure_incomplete=5` e `not_ready=5`.

Assinaturas residuais:

- `STRUCTURE_COUNT_MISMATCH:lists:80:74`;
- `STRUCTURE_COUNT_MISMATCH:lists:17:15`;
- `STRUCTURE_COUNT_MISMATCH:lists:8:1`;
- `STRUCTURE_COUNT_MISMATCH:tables:4:3`;
- `STRUCTURE_COUNT_MISMATCH:tables:2:1`.

Conclusão: headings estão resolvidos; o problema ficou restrito a 3 documentos com listas e 2 com tabelas.

## `0.4.0-acceptance.8` — diagnóstico residual

Documento: `package-acceptance8.md`.

Objetivo: **não corrigir no escuro** os cinco casos restantes. O build não altera parser Legacy, schema KD, `ai_readiness` ou gate. Acrescenta somente telemetria agregada no `Semantic_DOM_Expectation`.

Novos warnings diagnósticos:

- `HTML_DIAG_LIST_PARSER_UNREACHABLE:<n>`;
- `HTML_DIAG_LIST_NO_MATERIALIZABLE_CONTENT:<n>`;
- `HTML_DIAG_LIST_IMAGE_ONLY:<n>`;
- `HTML_DIAG_TABLE_PARSER_UNREACHABLE:<n>`;
- `HTML_DIAG_TABLE_NO_MATERIALIZABLE_TEXT:<n>`;
- `HTML_DIAG_TABLE_IMAGE_ONLY:<n>`.

Eles diferenciam três hipóteses:

1. estrutura válida existe, mas o parser não consegue alcançá-la por wrapper/intermediação;
2. tags estruturais existem, porém sem conteúdo semanticamente materializável;
3. conteúdo é essencialmente imagem/alt e exige tratamento próprio para não ser apagado nem falsamente ignorado.

Artefato:

- `base-conhecimento-inteligencia-integrada-0.4.0-acceptance.8.zip`;
- SHA-256 `a089fbd5cdb0fe6cc04a418f9eface8d135ac9d0fee1232307c5a3463b9d0091`;
- 28 arquivos runtime;
- 24 arquivos PHP.

Validação:

- PHP lint 24/24 PASS;
- JS syntax PASS;
- ZIP integrity PASS;
- KD regression 12/12 PASS;
- structural namespace regression 6/6 PASS;
- parser/schema/readiness/gate inalterados;
- Git↔ZIP bootstrap `6f115f8241b774a9f04e7ecbc84a17fb4925730a`;
- Git↔ZIP `Semantic_DOM_Expectation` `125908183b0e2fd931b9aa84cf01930d9205b499`.

## Gate ativo

`gate.pass=true` exige simultaneamente:

- `DOMDocument=true`;
- corpus invariável;
- fingerprint editorial idêntico;
- zero posts alterados;
- duas passagens completas;
- zero errors/throwables;
- zero hash/canonical JSON mismatch;
- `structure_incomplete=0` em ambas as passagens;
- `ai_readiness.not_ready=0` em ambas as passagens.

O `acceptance.8` é diagnóstico-only, portanto é aceitável que o gate continue `false`; a finalidade é explicar precisamente o resíduo para a última correção.

## Sequência obrigatória atual

1. instalar/substituir pelo `0.4.0-acceptance.8` em homologação;
2. **não executar ainda Aceitação G-240 v2**;
3. executar somente **Base de Conhecimento → Validação KD v2**;
4. retornar `bdc-kb-spec004-kd-v2-smoke-*.json`;
5. analisar os seis novos warnings contra os três mismatches de listas e dois de tabelas;
6. aplicar somente a classe de correção comprovada;
7. repetir full-corpus até `structure_incomplete=0` e `not_ready=0`;
8. somente então repetir os mesmos oito casos A/B humanos;
9. somente G-240 v2 PASS libera G-245.

## Amostra A/B congelada

O reteste humano continua usando exatamente os mesmos oito posts do G-240 v1 e seus fingerprints anteriores como baseline. Qualquer alteração editorial posterior torna o slot `stale`.

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
- G-240/v2: **FAIL CONTROLADO / `acceptance.8` DIAGNOSTIC ENV SMOKE PENDING**.
- G-245: **BLOCKED**.
- G-250: **NOT_RUN**.
