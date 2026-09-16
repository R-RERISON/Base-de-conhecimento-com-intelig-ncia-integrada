# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- baseline consolidado em `main`: commit `a676f8daaaf9a794d503ccd6a2c28178be1fb8bf`.
- R-200: **PASS**.
- R-210: **PASS**.
- G-220 — Content Extractor: **PASS ambiental**.
- G-230/v1 — Knowledge Document determinístico: **PASS ambiental**; v1 permanece `SUPERSEDED_FOR_AI` após G-240 v1.
- G-240/v1 — Real Content Acceptance: **FAIL CONTROLADO — perda estrutural**.
- G-240/v2 — `acceptance.5`: **FAIL CONTROLADO — 50/622 structure_incomplete**.
- remediação atual: **travessia de wrappers estruturais legados / `0.4.0-acceptance.6`**.
- branch: `spec004-g240-real-content-acceptance`.
- PR #3: **DRAFT / NÃO MERGEAR** antes de G-240 v2 PASS.
- G-245 — Elementor/produção: **BLOCKED por G-240**; writer proibido.
- G-250: **NOT_RUN**.

## Contrato ativo

`knowledge-document-contract-v2.md` — **FROZEN `2.0.0`**.

Invariantes preservados:

- construção read-only;
- nenhuma persistência de documento/hash/cache/progresso;
- nenhum write em `post_content`, `_elementor_data`, status, revisão ou publicação;
- nenhum `do_shortcode()` genérico;
- nenhum `render_block()` ou renderização dinâmica arbitrária;
- nenhuma dependência de IA, Foundry, embeddings ou vetores;
- `structure_incomplete=0` continua requisito obrigatório do smoke full-corpus.

## Histórico resumido da remediação

### G-240 v1

Evidência: `evidence/g240-acceptance-20260916T085721Z.json`.

- 8/8 slots revisados;
- 8/8 cobertura textual completa;
- 8/8 ordem preservada;
- 8/8 sem texto inventado;
- 7/8 com `structure_loss`.

Conclusão: Knowledge Document v1 era determinístico, porém semanticamente plano demais para listas/tabelas/hierarquia.

### `0.4.0-acceptance.3`

Evidência: `evidence/kd-v2-smoke-20260916T101517Z.json`.

- `structure_incomplete=82`;
- 62 legacy_html, 14 elementor, 5 mixed, 1 gutenberg;
- havia assinaturas `actual > expected`, compatíveis com colisão/fusão de IDs estruturais locais.

### `0.4.0-acceptance.4`

Evidência: `evidence/kd-v2-smoke-20260916T104818Z.json`.

- `structure_incomplete`: **82 → 78**;
- mixed: **5 → 2**;
- gutenberg: **1 → 0**;
- todas as assinaturas `actual > expected` desapareceram.

Conclusão: namespace determinístico removeu a classe de fusão/colisão; o resíduo passou a ser exclusivamente `expected > actual`.

### `0.4.0-acceptance.5`

Evidência: `evidence/kd-v2-smoke-20260916T112024Z.json`.

Ambiente:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- Elementor `4.1.0`;
- KD `2.0.0`;
- `DOMDocument=true`.

Segurança/determinismo:

- corpus `622 → 622`;
- 622/622 documentos nas duas passagens;
- zero errors/throwables;
- zero source/document hash mismatch;
- zero canonical JSON mismatch;
- fingerprint editorial idêntico;
- `changed_posts_during_run=0`.

Resultado estrutural:

- `structure_incomplete`: **78 → 50**;
- legacy_html: **62 → 44**;
- elementor: **14 → 5**;
- mixed: **2 → 1**;
- `candidate_ready`: **499 → 523**;
- `list_items` mismatch: **38 docs → 0**;
- lists mismatch: **43 docs → 5**;
- headings: **47 docs**, expected `504`, actual `302`;
- tables: 2 docs, expected `6`, actual `4`.

Conclusão: a âncora estrutural vazia resolveu a perda de relação pai/filho em listas aninhadas. O gargalo passou a ser predominantemente heading traversal.

## Terceira causa comprovada — wrappers estruturais não atravessados

`Legacy_HTML_Adapter::collect_structure()` conta headings/listas/tabelas em qualquer profundidade do DOM.

Entretanto o walker histórico atravessava recursivamente apenas uma allowlist limitada de wrappers (`div`, `section`, `article`, `main`, `header`, `footer`, `aside`, `nav`, `figure`, `figcaption`). Um wrapper legado ou desconhecido contendo heading/lista/tabela podia ser contado na origem e depois achatado como texto, impedindo a materialização do descendente em `blocks[]`.

Essa assimetria é especialmente compatível com o padrão residual do `acceptance.5`: 47/50 documentos divergentes por headings, sem `actual > expected`.

## `0.4.0-acceptance.6`

Correção controlada:

1. wrappers conhecidos continuam seguindo o comportamento anterior;
2. wrappers inline comuns sem descendentes estruturais continuam achatados como texto;
3. wrapper desconhecido só é atravessado quando contém descendant semanticamente estrutural (`h1..h6`, `p`, `ul/ol`, `table`, `blockquote`, `pre`, `img`);
4. nenhum tema/widget/shortcode/bloco dinâmico é renderizado;
5. a regra `structure_incomplete=0` permanece inalterada.

Telemetria temporária:

- report sobe para schema `1.2.0`;
- agrega `extraction_warnings`;
- `HTML_STRUCTURAL_WRAPPER_TRAVERSED:<tag>`;
- `HTML_DIAG_EMPTY_HEADINGS:<count>`;
- `HTML_DIAG_HEADINGS_IN_TABLE:<count>`;
- `HTML_DIAG_HEADINGS_IN_LIST:<count>`;
- `HTML_DIAG_NESTED_TABLES:<count>`;
- `HTML_DIAG_LISTS_IN_TABLES:<count>`.

A telemetria não exporta IDs, títulos, URLs ou conteúdo e não altera `ai_readiness` por si só.

Package:

- `package-acceptance6.md`;
- versão `0.4.0-acceptance.6`;
- SHA-256 `e9879812b670b0a322920e137e4f1027808a92f93faae24ce7ece8122c9f3e09`;
- PHP lint 23/23 PASS;
- JS syntax PASS;
- ZIP integrity PASS;
- staging ↔ ZIP parity 27/27 PASS;
- safety scan PASS;
- blobs críticos Git ↔ package iguais:
  - bootstrap `144415925f7d86c240f56c8235a91d2724a907eb`;
  - Legacy adapter `6c7061111d9acd47b4ebe044f8b44eac4efe0a41`;
  - smoke v2 `ea5c415b1d1b99b84168e5cd999050ec511ae7c2`.

Limitação local: o PHP CLI utilizado para validação não possui `DOMDocument`; portanto o caminho DOM real permanece para validação ambiental. A homologação observada possui `DOMDocument=true`.

## Sequência obrigatória atual

1. instalar/substituir pelo `0.4.0-acceptance.6` em homologação;
2. **não executar ainda Aceitação G-240 v2**;
3. executar somente **Base de Conhecimento → Validação KD v2**;
4. retornar o novo `bdc-kb-spec004-kd-v2-smoke-*.json`;
5. exigir corpus invariável, fingerprint igual, zero writes/errors/throwables/hash mismatch/JSON mismatch;
6. medir especialmente redução de `headings` e ler `extraction_warnings` para qualquer resíduo;
7. se `structure_incomplete=0`, reexecutar os mesmos oito casos A/B do G-240 v2;
8. se ainda houver resíduo, corrigir somente a classe comprovada — por exemplo headings dentro de tabelas/listas, headings vazios, nested tables — sem alterar o gate;
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
- G-240/v2: **FAIL CONTROLADO / `acceptance.6` ENV SMOKE PENDING**.
- G-245: **BLOCKED**.
- G-250: **NOT_RUN**.
