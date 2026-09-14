# Matriz de Evidência — SPEC-001

> Estado atual: **runtime S002 implementado; suíte unitária PASS 15/15; harness PHPUnit e runner onclick JSON preparados; gates WordPress/browser permanecem NOT_RUN até execução no ambiente real.**

| Gate | Aplicabilidade | Evidência exigida | Estado atual | Gate de saída |
|---|---|---|---|---|
| T040 Unitário determinístico | MUST | contrato, validação, diff, no-op e máquina B-006 isolada | PASS — 15/15 em PHP 8.4.23 | pré-requisito de integração |
| G-001 Editorial/Elementor | MUST | before/after de `_elementor_data`, `post_content`, `post_title`; GET sem write; browser smoke | NOT_RUN — PHPUnit/onclick preparados | Homologação |
| G-020 Summary | MUST | read/save/omit/empty/delete/no-op/allowlist/limite/sanitização/read-after-write em WordPress real | NOT_RUN — PHPUnit/onclick preparados | Homologação |
| G-070 Segurança/scope | MUST | capability, nonce, GET, IDOR, tipo inválido, mass assignment, XSS, escaping | NOT_RUN — onclick cobre parte in-process; browser/handler ainda obrigatório | Homologação |
| G-110 UI/UX | MUST | browser/manual: integração wp-admin, feedback, labels, foco, teclado, viewport, cor | NOT_RUN | Homologação |
| G-130 Lifecycle/release | CONDICIONAL/MUST quando houver pacote | activation/deactivation/uninstall, package/checksum; remoção do runner onclick; upgrade N/A na primeira versão | NOT_RUN | Release candidate |
| B-006 Write composto | MUST | fault injection determinístico + confirmação em integração WordPress | NOT_RUN — unit fault injection PASS; PHPUnit/onclick preparados | Homologação |
| Golden Queries | N/A | Search fora de escopo | N/A — Search não existe na SPEC | — |
| IA/custo | N/A | IA fora de escopo | N/A — sem chamada externa | — |
| Analytics/privacy logging | N/A | query logging fora de escopo | N/A — nenhuma telemetria de query/identidade | — |

## Evidência unitária executada

Arquivo: `tests/unit/spec001-summary-store.php`.

Resultado versionado em `evidencia-unitaria-s003.md`: 15 testes aprovados, 0 falhas.

## Evidência WordPress preparada

### PHPUnit/Core Test Suite

`tests/integration/` contém harness para execução reproduzível contra WordPress + MySQL/MariaDB reais.

### Onclick temporário

`includes/class-diagnostics-runner.php` é carregado somente com `BDC_KB_ENABLE_DIAGNOSTICS=true` e exige `manage_options` + nonce. O clique gera JSON sem persistir relatório e usa fixtures temporárias marcadas por `_bdc_kb_diagnostic_fixture=spec001-onclick-v1`.

O JSON só termina `overall=PASS` quando todos os checks passam e `cleanup.residual_fixtures=0`.

O onclick não promove G-110 e não substitui browser acceptance.

## Casos mínimos G-001

1. salvar Summary não altera `_elementor_data`;
2. não altera `post_content`;
3. não altera `post_title`;
4. GET da tela não altera meta/editorial.

## Casos mínimos G-020

1. meta ausente -> vazio sem write;
2. leitura dos três campos;
3. update de um campo preserva omitidos;
4. vazio sanitizado remove meta;
5. campo estranho rejeita tudo;
6. valor não-string rejeita tudo;
7. valor >32768 bytes rejeita tudo;
8. HTML/script é sanitizado;
9. multiline/backslash/unicode preservados conforme WordPress;
10. submit idêntico é NO_CHANGE;
11. estado relido define sucesso.

## Casos mínimos G-070

1. autorizado+nonce válido -> sucesso;
2. sem capability -> bloqueio;
3. capacidade de menu sem `edit_post` do alvo -> bloqueio;
4. nonce ausente/inválido -> bloqueio;
5. GET tentando salvar -> zero efeito;
6. IDOR trocando `post_id` -> bloqueio;
7. ID inexistente -> erro seguro;
8. `page`/CPT -> unsupported;
9. meta extra -> zero write;
10. XSS armazenado/refletido -> não executa.

## Casos mínimos G-110

- fluxo real completo;
- sucesso/erro visíveis;
- labels associados;
- navegação por teclado/foco;
- cor não exclusiva;
- viewport administrativa estreita;
- nenhum segundo shell/sidebar;
- botão temporário de diagnóstico ausente quando a flag está desabilitada.

## Fault injection B-006

Aprovados unitariamente e preparados para WordPress real:

- falha write #1;
- falha write #2 após #1;
- falha write #3 após #1/#2;
- falha delete;
- falha de compensação;
- mistura delete/update;
- no-op sem write.

O runner onclick executa ao menos `FAIL_SAFE` por falha no segundo write e `PARTIAL_FAILURE_CRITICAL` por falha também na compensação.

## Gate de limpeza T044/G-130

Antes de package/release:

- remover a flag do ambiente;
- remover runner/require/hook temporários;
- confirmar zero fixtures marcadas;
- repetir lint/regressão após remoção.

## Regra de gate

`FAIL`, `NOT_RUN`, `NOT_CONFIGURED` ou `STALE` em gate ativo bloqueiam Homologação/Concluída/Release conforme a coluna de saída. PASS unitário ou PASS onclick não promove automaticamente browser/lifecycle.
