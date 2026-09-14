# S003 — Evidência unitária inicial

> Estado: **PASS UNITÁRIO — não equivale a PASS de integração WordPress ou Homologação**.  
> Runtime testado: commit `5e1b35763df091ebf505f8e8f8260c654fc21926`.

## Execução

Comandos locais:

```bash
php -l tests/unit/spec001-summary-store.php
php tests/unit/spec001-summary-store.php
```

Ambiente da execução: PHP `8.4.23` CLI.

Resultado:

```text
No syntax errors detected in tests/unit/spec001-summary-store.php
RESULT passed=15 failed=0
```

## Casos aprovados

1. Meta Contract contém exatamente as três metas autorizadas e somente `post`.
2. leitura de meta ausente é side-effect free.
3. update parcial preserva campo omitido e escreve apenas diff.
4. campo fora da allowlist falha antes de qualquer write.
5. valor acima de 32768 bytes falha antes de qualquer write.
6. vazio sanitizado remove a meta.
7. NO_CHANGE não executa write.
8. falha injetada no primeiro write -> `FAIL_SAFE`.
9. falha no segundo write após primeiro sucesso -> compensação do primeiro e `FAIL_SAFE`.
10. falha no terceiro write após dois sucessos -> compensação integral e `FAIL_SAFE`.
11. falha em delete -> detectada e `FAIL_SAFE`.
12. falha durante compensação -> `PARTIAL_FAILURE_CRITICAL` com estado final relido.
13. mistura update + delete confirma estado final esperado.
14. `page` é rejeitada como post type não suportado.
15. write sem `edit_post` é bloqueado antes de mutação.

## Técnica de fault injection

A suíte unitária define stubs namespaced das primitives WordPress usadas pelo Store e permite escolher o número ordinal do write que deve falhar. Isso prova a máquina de consistência B-006 de forma determinística sem adicionar hook de teste ao runtime de produção.

O teste de `PARTIAL_FAILURE_CRITICAL` também confirma emissão do diagnóstico técnico contendo apenas `post_id` e nomes dos campos; nenhum conteúdo narrativo é logado.

## Limite desta evidência

A suíte não prova comportamento real de:

- WordPress Metadata API;
- `register_post_meta` no registry real;
- nonces/admin-post;
- `WP_Query`/permissões do ambiente;
- Elementor/editorial before/after;
- browser, foco, teclado ou responsividade;
- activation/package.

Por isso os gates G-001/G-020/G-070/G-110/G-130 e o gate final B-006 permanecem `NOT_RUN` até a integração/acceptance correspondente. T040 está concluída; T042 permanece aberta para prova em WordPress real.
