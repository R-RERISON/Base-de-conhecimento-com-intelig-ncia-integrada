# SPEC-002 — Ferramenta temporária de profiling classificatório

Esta pasta contém fonte de homologação, não runtime de produto.

## Objetivo

Gerar evidência read-only sobre os stores classificatórios históricos antes de decidir Taxonomy vs Post Meta.

## Package de referência

`base-conhecimento-inteligencia-integrada-0.2.0-profile.1.zip`

SHA-256:
`0b09d21603f08a9c8d6ee887f0fd169032dd68a374ced80d18560f009f16d000`

O build parte da baseline de produto `0.1.0-rc.1` e adiciona somente:

- `BDC_KB_CLASSIFICATION_PROFILE_BUILD=true` no bootstrap do package temporário;
- `includes/class-classification-profiler.php`;
- registro explícito da classe temporária.

## Safety

- exige `manage_options`;
- POST + nonce;
- usa SQL preparado somente para leitura dos 11 meta keys alvo;
- não cria/edita posts, metas, taxonomias, options, transients, cron ou tabelas;
- não lê `post_content`, `_elementor_data`, título, autor ou identidade de usuário;
- relatório não é persistido no WordPress;
- após a coleta, o package temporário deve ser substituído pela baseline limpa ou por um próximo build formal.

## Remoção

Esta classe nunca deve entrar no package final da SPEC-002.
