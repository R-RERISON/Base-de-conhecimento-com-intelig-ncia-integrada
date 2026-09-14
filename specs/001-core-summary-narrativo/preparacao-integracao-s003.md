# S003 — Preparação da integração WordPress T041/T042

> Estado: **PREPARADA — execução real ainda NOT_RUN**.  
> Baseline de preparação: `main @ b2f0d26cd21a915e9b4570adbbd45f5ce4685108` antes da inclusão do harness.  
> A existência desta suíte não promove nenhum gate de integração.

## Objetivo

Materializar testes executáveis contra o WordPress Core Test Suite real para reduzir T041/T042 a uma execução reproduzível, sem adicionar infraestrutura de produção ou GitHub Actions.

## Arquivos criados

- `tests/integration/bootstrap.php`;
- `tests/integration/phpunit.xml.dist`;
- `tests/integration/test-spec001-summary-integration.php`;
- `tests/integration/README.md`.

## Evidência já obtida

PHP lint local com PHP 8.4.23:

- `bootstrap.php`: PASS;
- `test-spec001-summary-integration.php`: PASS.

Isso prova somente sintaxe do harness.

## Cobertura preparada

### G-001 — Editorial/Elementor

- gravação do Summary não altera `post_title`;
- não altera `post_content`;
- não altera `_elementor_data`.

### G-020 — Summary

- contrato registrado das três metas;
- `show_in_rest=false`;
- leitura de meta ausente projeta vazio sem write;
- update parcial preserva omitidos;
- vazio remove meta;
- campo desconhecido e excesso de limite falham antes de write;
- sanitização de HTML/script;
- Unicode/backslash/multiline;
- NO_CHANGE sem write;
- estado relido como fonte de sucesso.

### G-070 — Segurança/scope

- capability por objeto;
- `page` rejeitada;
- POST com nonce válido;
- nonce inválido não escreve;
- IDOR com usuário que possui `edit_posts` mas não `edit_post` no objeto;
- GET no handler não muta.

### B-006 — fault injection real

A suíte usa os filtros reais do WordPress:

- `update_post_metadata`;
- `delete_post_metadata`.

Cenários preparados:

1. falha no segundo write, seguida de compensação completa -> esperado `FAIL_SAFE`;
2. falha no segundo write e falha no primeiro write de compensação -> esperado `PARTIAL_FAILURE_CRITICAL`.

## Ambiente desta sessão

Disponível:

- PHP 8.4.23;
- Node.js 22.

Indisponível para execução válida de T041/T042:

- MySQL/MariaDB operacional;
- WordPress Core Test Suite configurado;
- PHPUnit conectado ao ambiente WordPress real.

Foi tentada preparação local de banco por gerenciador de pacotes, mas o ambiente não conseguiu disponibilizar os pré-requisitos dentro da janela operacional. Não foi usado mock para promover gate.

## Como executar

Com `wordpress-tests-lib` e banco de testes configurados:

```bash
WP_TESTS_DIR=/tmp/wordpress-tests-lib phpunit -c tests/integration/phpunit.xml.dist
```

A saída bruta da execução e as versões de WordPress/PHP/DB devem ser registradas antes de qualquer alteração de estado da Matriz de Evidência.

## Estado dos gates

- T041: **NOT_RUN — harness READY**;
- G-001: `NOT_RUN`;
- G-020: `NOT_RUN`;
- G-070: `NOT_RUN`;
- T042/B-006 integração: `NOT_RUN`;
- G-110: `NOT_RUN`;
- G-130: `NOT_RUN`.

## Próximo passo

Executar T041/T042 em ambiente WordPress real com banco efêmero e versionar:

1. versões WordPress/PHP/MySQL ou MariaDB/PHPUnit;
2. comando executado;
3. total de testes/assertions;
4. PASS/FAIL por gate;
5. qualquer defect com reprodução;
6. correção + teste de regressão antes de promover o gate.

Sem essa evidência, Homologação continua proibida.
