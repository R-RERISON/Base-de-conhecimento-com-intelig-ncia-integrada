# Integração WordPress — SPEC-001

Esta suíte valida **T041/T042** contra o WordPress Core Test Suite real. Ela não usa mocks das primitives de Metadata, Users/Capabilities, Nonces ou Posts.

## Pré-requisitos

- PHP compatível com a matriz do WordPress testado;
- PHPUnit compatível com a versão do WordPress Core Test Suite;
- WordPress Core Test Suite (`wordpress-tests-lib`) configurado com banco MySQL/MariaDB efêmero;
- variável `WP_TESTS_DIR` apontando para a pasta `wordpress-tests-lib` (opcional se estiver em `/tmp/wordpress-tests-lib`).

O projeto deliberadamente **não** adiciona GitHub Actions, Composer obrigatório, Docker ou banco próprio apenas para esta SPEC.

## Execução

A partir da raiz do repositório:

```bash
WP_TESTS_DIR=/tmp/wordpress-tests-lib phpunit -c tests/integration/phpunit.xml.dist
```

Se o executável do PHPUnit estiver em outro caminho, substitua apenas o comando `phpunit`.

## Cobertura

A suíte cobre:

- registro exato das três metas e `show_in_rest=false`;
- G-001: Summary não altera `post_title`, `post_content` ou `_elementor_data`;
- G-020: leitura vazia, update parcial, delete por vazio, allowlist, limite, sanitização, Unicode/backslash, NO_CHANGE e read-after-write;
- G-070: capability por objeto, `page` rejeitada, nonce válido/inválido, IDOR e mutação por GET;
- B-006: falha no segundo write com compensação e falha de compensação crítica usando os filtros reais `update_post_metadata`/`delete_post_metadata`.

## Regra de evidência

A existência desta suíte **não** altera gates. Até a execução real produzir saída versionada:

- G-001 = `NOT_RUN`;
- G-020 = `NOT_RUN`;
- G-070 = `NOT_RUN`;
- B-006 integração = `NOT_RUN`.

Não declarar Homologação, Release ou produção com base apenas em lint ou leitura de código.
