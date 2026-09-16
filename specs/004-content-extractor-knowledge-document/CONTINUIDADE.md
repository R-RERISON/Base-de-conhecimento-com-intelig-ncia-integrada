# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- baseline consolidado em `main`: commit `a676f8daaaf9a794d503ccd6a2c28178be1fb8bf`.
- R-200: **PASS**.
- R-210: **PASS**.
- G-220 — Content Extractor: **PASS ambiental**.
- G-230/v1 — Knowledge Document determinístico: **PASS ambiental**; v1 permanece `SUPERSEDED_FOR_AI` após G-240 v1.
- G-240/v1 — Real Content Acceptance: **FAIL CONTROLADO — perda estrutural**.
- G-240/v2 — remediação ativa; último ambiente `acceptance.6` permaneceu com `50/622 structure_incomplete`.
- candidato atual: **`0.4.0-acceptance.7` / Knowledge Document `2.0.1` / smoke `1.3.0`**.
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
- `structure_incomplete=0` continua obrigatório;
- a partir do smoke `1.3.0`, `ai_readiness.not_ready=0` também é obrigatório.

## Histórico resumido

### G-240 v1

Evidência: `evidence/g240-acceptance-20260916T085721Z.json`.

- 8/8 slots revisados;
- 8/8 cobertura textual completa;
- 8/8 ordem preservada;
- 8/8 sem texto inventado;
- 7/8 com `structure_loss`.

Conclusão: KD v1 era determinístico, porém semanticamente plano demais para listas/tabelas/hierarquia.

### `acceptance.3`

Evidência: `evidence/kd-v2-smoke-20260916T101517Z.json`.

- `structure_incomplete=82`;
- 62 legacy_html, 14 elementor, 5 mixed, 1 gutenberg;
- havia assinaturas `actual > expected`, compatíveis com colisão/fusão de IDs estruturais locais.

### `acceptance.4`

Evidência: `evidence/kd-v2-smoke-20260916T104818Z.json`.

- `structure_incomplete`: 82 → 78;
- mixed: 5 → 2;
- gutenberg: 1 → 0;
- todas as assinaturas `actual > expected` desapareceram.

Conclusão: namespace determinístico removeu a classe de fusão/colisão; o resíduo passou a ser exclusivamente `expected > actual`.

### `acceptance.5`

Evidência: `evidence/kd-v2-smoke-20260916T112024Z.json`.

- corpus `622 → 622`;
- 622/622 documentos em duas passagens;
- zero errors/throwables/hash mismatch/canonical JSON mismatch;
- fingerprint editorial idêntico;
- zero posts alterados;
- `structure_incomplete`: 78 → 50;
- legacy_html: 62 → 44;
- elementor: 14 → 5;
- mixed: 2 → 1;
- `list_items` mismatch: 38 docs → 0;
- lists mismatch: 43 docs → 5;
- headings: 47 docs, expected 504, actual 302;
- tables: 2 docs, expected 6, actual 4.

Conclusão: `structural_anchor` resolveu sublistas órfãs e isolou o problema em headings/estrutura local.

### `acceptance.6`

Evidência: `evidence/kd-v2-smoke-20260916T114435Z.json`.

Ambiente:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- Elementor `4.1.0`;
- Knowledge Document `2.0.0`;
- `DOMDocument=true`.

Segurança/determinismo:

- corpus `622 → 622`;
- fingerprint editorial antes/depois idêntico;
- zero posts alterados;
- 622/622 documentos nas duas passagens;
- zero errors/throwables;
- zero hash mismatch;
- zero canonical JSON mismatch.

Estrutura:

- `structure_incomplete` permaneceu **50**;
- legacy_html 44, elementor 5, mixed 1;
- headings continuaram em 47 docs (`504 expected / 302 actual`);
- lists em 5 docs (`113 / 96`);
- tables em 2 docs (`6 / 4`).

Telemetria decisiva:

- wrappers históricos foram efetivamente atravessados, porém o mismatch não caiu;
- foram observados muitos headings vazios;
- headings locais aparecem dentro de listas e tabelas;
- existem listas dentro de tabelas e casos residuais de estruturas aninhadas.

Conclusão: a hipótese de wrapper desconhecido como causa dominante foi rejeitada. O erro conceitual restante era comparar **tag DOM bruta** com **unidade semântica global**.

## Knowledge Document 2.0.1

O patch `2.0.1` preserva a forma externa do KD, mas corrige a semântica de `structure`.

Nova camada:

`Semantic_DOM_Expectation`

Características:

1. calcula expected diretamente do DOM bruto;
2. não usa `sections[]` nem `blocks[]` para derivar expected;
3. heading vazio não conta como heading semântico esperado;
4. heading local dentro de lista/tabela/parágrafo/blockquote/code não é promovido ao outline global;
5. heading local gera `HTML_LOCAL_HEADING_FLATTENED:*` e `review_required`;
6. lista achatada dentro de tabela gera `HTML_NESTED_LIST_IN_TABLE_FLATTENED:*` e `review_required`;
7. tabela aninhada não representada gera `HTML_NESTED_TABLE_UNREPRESENTED:*` e `not_ready`.

A expectativa permanece independente dos blocos emitidos, portanto o gate continua capaz de detectar perda real.

## Gate endurecido — smoke 1.3.0

`gate.pass=true` exige simultaneamente:

- corpus invariável;
- fingerprint editorial idêntico;
- zero posts alterados;
- duas passagens completas;
- zero errors;
- zero throwables;
- zero hash mismatches;
- zero canonical JSON mismatches;
- `structure_incomplete=0` nas duas passagens;
- `ai_readiness.not_ready=0` nas duas passagens;
- `DOMDocument=true`.

O requisito `not_ready=0` impede falso PASS quando a contagem estrutural reconcilia, mas existe perda crítica conhecida.

## Package atual — `0.4.0-acceptance.7`

Documento: `package-acceptance7.md`.

Artefato:

`base-conhecimento-inteligencia-integrada-0.4.0-acceptance.7.zip`

SHA-256:

`635016ef4a5acb4d94d04f7b0e9e05ce8d86af637c5654f96b0d58e3904cc91e`

Validação:

- 28 arquivos runtime;
- PHP lint 24/24 PASS;
- JS syntax PASS;
- ZIP integrity PASS;
- staging ↔ ZIP parity 28/28 PASS;
- KD regression 12/12 PASS;
- structural namespace regression 6/6 PASS;
- warning de heading local → `review_required` PASS;
- nested table não representada → `not_ready` PASS;
- focused safety scan PASS.

Git ↔ package parity fechado nos blobs críticos:

- bootstrap `c0e9dac678b78c07194cef88dd59f964900e0ec3`;
- `Semantic_DOM_Expectation` `a4440c728d644a3963652aa71c73818d9968ecd5`;
- `Knowledge_Document` `87ec7f861368dbb8c15691432e9f8d9a13bc0ea9`;
- `Semantic_Structure` `c34815516378c35bfb2e47075dc9a110cdac70b6`;
- smoke KD v2 `058cd1873dcaf86c16372302e9baafd71b6159b6`.

Nenhum marcador temporário/NOOP permanece no delta da branch.

## Sequência obrigatória atual

1. instalar/substituir pelo `0.4.0-acceptance.7` em homologação;
2. **não executar ainda Aceitação G-240 v2**;
3. executar somente **Base de Conhecimento → Validação KD v2**;
4. retornar `bdc-kb-spec004-kd-v2-smoke-*.json`;
5. exigir `gate.pass=true`, incluindo `structure_incomplete=0` e `not_ready=0`;
6. somente depois repetir os mesmos oito casos A/B do G-240 v1;
7. somente G-240 v2 PASS libera G-245.

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
- G-240/v2: **FAIL CONTROLADO / `acceptance.7` ENV SMOKE PENDING**.
- G-245: **BLOCKED**.
- G-250: **NOT_RUN**.
