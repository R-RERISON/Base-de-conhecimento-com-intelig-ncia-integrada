# Package — SPEC-004 `0.4.0-acceptance.4`

## Objetivo

Executar um experimento full-corpus controlado após o diagnóstico `0.4.0-acceptance.3`, corrigindo somente colisões determinísticas de IDs estruturais entre parciais extraídos independentemente.

O package **não** altera o schema Knowledge Document `2.0.0`, o algoritmo de `Semantic_Structure`, o parser Legacy HTML, a política de AI readiness nem o critério `structure_incomplete=0`.

## Evidência que motivou a correção

`evidence/kd-v2-smoke-20260916T101517Z.json`:

- 622/622 documentos em ambas as passagens;
- zero errors/throwables/hash mismatch/canonical JSON mismatch;
- zero mutação editorial;
- 82 documentos com `structure_complete=false`;
- 62 legacy_html, 14 elementor, 5 mixed e 1 gutenberg;
- mismatches em headings, lists, list_items e tables;
- assinaturas incluem casos com `actual > expected`, compatíveis com fusão indevida de estruturas que compartilham IDs locais.

## Causa corrigida nesta rodada

Adapters parciais iniciavam identidades locais determinísticas como `list-0`, `list-0-item-0` e `table-0`. Ao combinar widgets Elementor, blocos Gutenberg ou fontes mixed, duas estruturas independentes podiam chegar ao `Semantic_Structure` com a mesma identidade e serem agregadas como se fossem uma só.

Foi introduzido namespace determinístico antes de cada merge:

- Elementor: `elementor-merge-<offset>::...`;
- Gutenberg: `gutenberg-merge-<offset>::...`;
- merge final do Content Extractor: `extract-merge-<offset>::...`.

São namespaced `list_id`, `item_id`, `parent_item_id`, `table_id` e `image_id`.

## Arquivos runtime alterados

- `base-conhecimento-inteligencia-integrada.php` — versão `0.4.0-acceptance.4`;
- `includes/class-content-normalizer.php` — helper canônico de namespace estrutural;
- `includes/class-elementor-adapter.php` — namespace antes de merge;
- `includes/class-gutenberg-adapter.php` — namespace antes de merge;
- `includes/class-content-extractor.php` — namespace entre adapters/fontes.

Deliberadamente **não alterados**:

- `includes/class-legacy-html-adapter.php`;
- `includes/class-semantic-structure.php`;
- `includes/class-knowledge-document.php`;
- critério do smoke ambiental.

## Regressão adicionada

`tests/unit/spec004-structural-id-namespace.php` comprova que dois parciais que começam ambos com `list-0` e `table-0` continuam sendo duas listas e duas tabelas independentes depois do merge.

Resultado local: `6/6 PASS`.

## Validação do artefato

Arquivo:

`base-conhecimento-inteligencia-integrada-0.4.0-acceptance.4.zip`

SHA-256:

`f70066dcf915f8e62428b7fe43becce3ea476be92f489305fbd59c3a19658159`

Checks:

- staging ↔ ZIP parity: PASS (`27/27` arquivos);
- PHP lint: `23/23 PASS`;
- JS syntax: PASS;
- ZIP integrity: PASS;
- Knowledge Document v2 regression: `12/12 PASS`;
- structural ID namespace regression: PASS;
- safety scan: PASS, sem primitives de write editorial, `do_shortcode()` ou renderização arbitrária.

## Gate ambiental

Executar somente **Base de Conhecimento → Validação KD v2**.

Não executar ainda **Aceitação G-240 v2**.

O resultado será comparado contra `acceptance.3` para determinar se a correção remove os casos Elementor/mixed/Gutenberg. Se divergências Legacy permanecerem, elas serão tratadas separadamente com evidência específica; não haverá relaxamento do gate.

G-245 permanece bloqueado e nenhum writer/migration Elementor está autorizado.
