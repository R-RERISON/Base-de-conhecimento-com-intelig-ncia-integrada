# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- baseline consolidado em `main`: commit `a676f8daaaf9a794d503ccd6a2c28178be1fb8bf`.
- R-200: **PASS**.
- R-210: **PASS**.
- G-220 — Content Extractor: **PASS ambiental**.
- G-230/v1 — Knowledge Document determinístico: **PASS ambiental**; v1 permanece `SUPERSEDED_FOR_AI` após G-240 v1.
- G-240/v1 — Real Content Acceptance: **FAIL CONTROLADO — perda estrutural**.
- G-240/v2 — `acceptance.4`: **FAIL CONTROLADO — 78/622 structure_incomplete**.
- remediação atual: **preservação de item-pai vazio como âncora estrutural / `0.4.0-acceptance.5`**.
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

- corpus 622/622 nas duas passagens;
- zero errors/throwables/hash mismatch/canonical JSON mismatch;
- fingerprint editorial idêntico;
- zero posts alterados;
- `structure_incomplete=82`;
- distribuição: 62 legacy_html, 14 elementor, 5 mixed, 1 gutenberg.

O diagnóstico encontrou assinaturas `actual > expected` em listas, compatíveis com fusão indevida de árvores por IDs locais repetidos.

### `0.4.0-acceptance.4`

Evidência: `evidence/kd-v2-smoke-20260916T104818Z.json`.

Ambiente:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- Elementor `4.1.0`;
- KD `2.0.0`;
- `DOMDocument=true`.

Segurança/determinismo:

- corpus `622 → 622`;
- 622/622 documentos nas duas passagens;
- zero errors;
- zero throwables;
- zero source/document hash mismatch;
- zero canonical JSON mismatch;
- fingerprint editorial antes/depois idêntico;
- `changed_posts_during_run=0`.

Resultado estrutural:

- `structure_incomplete`: **82 → 78**;
- legacy_html: **62 → 62**;
- elementor: **14 → 14**;
- mixed: **5 → 2**;
- gutenberg: **1 → 0**;
- `candidate_ready`: 495 → 499.

Métricas residuais:

- headings: 47 docs, expected 504, actual 302;
- lists: 43 docs, expected 1150, actual 604;
- list_items: 38 docs, expected 1450, actual 1001;
- tables: 2 docs, expected 6, actual 4.

Conclusão importante: após o namespace estrutural, todas as assinaturas residuais passaram a ser `expected > actual`. A classe de fusão/colisão foi efetivamente removida; o resíduo é de **estrutura detectada na fonte mas não materializada em `blocks[]`**.

## Segunda causa comprovada — item pai vazio de sublista

O fluxo v2 identifica listas filhas por `parent_item_id`.

Antes do `acceptance.5`, um `<li>` cujo texto próprio normalizado fosse vazio era descartado por `Content_Normalizer::fragment()`, mesmo quando continha uma `<ul>/<ol>` filha. A sublista permanecia com `parent_item_id`, porém o item-pai não existia na projeção. O resultado era uma subárvore órfã que não podia ser materializada corretamente pelo `Semantic_Structure`.

Esse caso explica uma classe objetiva de `expected > actual` em `lists`/`list_items` sem necessidade de relaxar o gate.

## `0.4.0-acceptance.5`

Correção isolada:

1. `Content_Normalizer::fragment()` preserva `list_item` de texto vazio quando existe identidade estrutural válida (`list_id` + `item_id`);
2. o fragmento recebe `meta.structural_anchor=true`;
3. `text` permanece `""` — nenhum conteúdo é inventado;
4. `Semantic_Structure::sections()` aceita texto vazio somente nesse caso explicitamente marcado;
5. a sublista volta a encontrar o item-pai e mantém a relação pai/filho;
6. o mesmo `structure_complete` continua comparando expected × actual sem tolerância ou compensação.

Deliberadamente não alterados nesta rodada:

- `Legacy_HTML_Adapter`;
- Elementor Adapter;
- Gutenberg Adapter;
- Knowledge Document schema/hash contract;
- runner ambiental KD v2;
- política de shortcodes;
- critério `structure_incomplete=0`;
- qualquer writer/migration Elementor.

Regressões:

- `tests/unit/spec004-structural-id-namespace.php`;
- `tests/unit/spec004-empty-list-anchor.php`.

Package:

- `package-acceptance5.md`;
- versão `0.4.0-acceptance.5`;
- SHA-256 `43c5783263683c44ce98fdb1196b0d289e5cf1fe779df698f23bbccc04aca51c`;
- PHP lint 23/23 PASS;
- JS syntax PASS;
- ZIP integrity PASS;
- staging ↔ ZIP parity 27/27 PASS;
- KD v2 regression 12/12 PASS;
- namespace regression 6/6 PASS;
- structural-anchor regression PASS;
- safety scan PASS.

Limitação local: o PHP CLI utilizado para validação não possui `DOMDocument`; portanto o caminho DOM real permanece para validação ambiental. A homologação observada possui `DOMDocument=true`.

## Sequência obrigatória atual

1. instalar/substituir pelo `0.4.0-acceptance.5` em homologação;
2. **não executar ainda Aceitação G-240 v2**;
3. executar somente **Base de Conhecimento → Validação KD v2**;
4. retornar o novo `bdc-kb-spec004-kd-v2-smoke-*.json`;
5. exigir novamente corpus invariável, fingerprint igual, zero writes/errors/throwables/hash mismatch/JSON mismatch;
6. medir especialmente a redução de `lists` e `list_items`;
7. se `structure_incomplete=0`, reexecutar os mesmos oito casos A/B do G-240 v2;
8. se ainda houver resíduo, instrumentar/corrigir somente a classe restante comprovada — headings/wrappers/tabelas — sem alterar o gate;
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
- G-240/v2: **FAIL CONTROLADO / `acceptance.5` ENV SMOKE PENDING**.
- G-245: **BLOCKED**.
- G-250: **NOT_RUN**.
