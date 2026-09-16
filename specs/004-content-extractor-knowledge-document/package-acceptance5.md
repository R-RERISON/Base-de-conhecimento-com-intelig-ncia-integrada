# Package `0.4.0-acceptance.5`

## Objetivo

Remediar uma segunda causa estrutural comprovada após o smoke `acceptance.4`, sem relaxar o gate do Knowledge Document v2.

O `acceptance.4` eliminou a classe de colisão/fusão de IDs estruturais, mas o full-corpus permaneceu com `78/622 structure_incomplete`. Todas as assinaturas residuais passaram a ser `expected > actual`.

## Evidência de entrada

- `evidence/kd-v2-smoke-20260916T104818Z.json`;
- WordPress `6.9.4`;
- PHP `8.5.10`;
- Elementor `4.1.0`;
- Knowledge Document `2.0.0`;
- `DOMDocument=true`;
- corpus `622 → 622`;
- zero writes, errors, throwables, hash mismatch ou canonical JSON mismatch;
- `structure_incomplete=78` nas duas passagens.

Distribuição residual:

- legacy_html: 62;
- elementor: 14;
- mixed: 2;
- gutenberg: 0.

## Correção isolada

Um `list_item` pode existir apenas como container estrutural de uma sublista. Até o `acceptance.4`, um item com texto normalizado vazio era descartado por `Content_Normalizer::fragment()`. A sublista continuava carregando `parent_item_id`, mas o item pai não chegava à projeção semântica.

O `acceptance.5`:

1. preserva `list_item` vazio quando possui identidade estrutural válida (`list_id` + `item_id`);
2. marca o fragmento como `meta.structural_anchor=true`;
3. mantém `text=""` — nenhum texto é inventado;
4. permite em `Semantic_Structure::sections()` texto vazio somente para `list_item` com âncora explícita;
5. mantém a mesma montagem pai/filho, o mesmo schema `2.0.0` e a mesma regra de reconciliação estrutural.

## Deliberadamente não alterado

- `Legacy_HTML_Adapter`;
- Elementor Adapter;
- Gutenberg Adapter;
- `Knowledge_Document` schema/hash contract;
- runner ambiental KD v2;
- exigência `structure_incomplete=0`;
- política de shortcodes;
- qualquer writer/migration/renderização arbitrária.

## Artefato

- versão: `0.4.0-acceptance.5`;
- arquivo: `base-conhecimento-inteligencia-integrada-0.4.0-acceptance.5.zip`;
- SHA-256: `43c5783263683c44ce98fdb1196b0d289e5cf1fe779df698f23bbccc04aca51c`;
- arquivos no ZIP: 27.

## Validação local

- PHP lint: **23/23 PASS**;
- JS syntax: **PASS**;
- ZIP integrity: **PASS**;
- staging ↔ ZIP parity: **27/27 PASS**;
- KD v2 regression: **12/12 PASS**;
- structural namespace regression: **6/6 PASS**;
- structural-anchor regression: **PASS**;
- safety scan para primitivas de write/render arbitrário nos arquivos alterados: **PASS**.

O CLI PHP local não dispõe de `DOMDocument`; portanto o comportamento DOM real não é declarado como validado localmente. O ambiente WordPress de homologação já demonstrou `DOMDocument=true`; o full-corpus ambiental permanece o gate correto.

## Execução autorizada

1. instalar/substituir pelo `0.4.0-acceptance.5` em homologação;
2. executar **somente** `Base de Conhecimento → Validação KD v2`;
3. não executar ainda `Aceitação G-240 v2`;
4. retornar o JSON `bdc-kb-spec004-kd-v2-smoke-*.json`;
5. exigir novamente zero mutation/determinismo e analisar a redução específica de `lists`/`list_items`;
6. se `structure_incomplete` continuar > 0, corrigir somente a classe residual comprovada antes da aceitação humana.

## Gate

**G-240 v2 continua FAIL CONTROLADO / STRUCTURAL EMISSION REMEDIATION ACTIVE.**

G-245 permanece bloqueado. Nenhum writer/migration Elementor está autorizado.
