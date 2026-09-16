# Package — 0.4.0-acceptance.10

## Objetivo

Diagnóstico final read-only do pipeline estrutural dos 5 documentos ainda `not_ready`.

Compara, para cada assinatura agregada:

1. expectativa semântica no DOM bruto;
2. expectativa semântica após `Shortcode_Inspector::unwrap_without_execution()`;
3. containers estruturais presentes nos fragments do `Legacy_HTML_Adapter`;
4. containers efetivamente materializados em `Semantic_Structure::blocks()`.

O build não altera parser, Knowledge Document `2.0.1`, hashes, readiness ou gate.

## Segurança

- nenhuma persistência de documento/hash/progresso;
- nenhum write editorial;
- não exporta post IDs, títulos, URLs ou conteúdo;
- fingerprint before/after e changed count continuam sendo medidos;
- nenhum `do_shortcode()` ou `render_block()`.

## Artefato

`base-conhecimento-inteligencia-integrada-0.4.0-acceptance.10.zip`

SHA-256:

`720d8c87d0119825b04999db605f96d778a45d84951078d208bef85f6905f776`

Validação local:

- 30 arquivos runtime;
- 26 PHP;
- PHP lint 26/26 PASS;
- JS syntax PASS;
- ZIP integrity PASS;
- safety scan do runner PASS.

Git ↔ package parity dos arquivos novos/alterados:

- bootstrap `f34ade78ad1c2f258d02007a59fc6118737d79e0`;
- `class-pipeline-structure-diagnostic.php` `39b6b923a32270d89b59f65938f8a73414acb4ba`.

## Execução

Instalar em homologação e executar somente:

`Base de Conhecimento → Diagnóstico Pipeline Final`

Retornar:

`bdc-kb-spec004-pipeline-diag-*.json`

Não executar ainda `Aceitação G-240 v2`. G-245 permanece bloqueado.
