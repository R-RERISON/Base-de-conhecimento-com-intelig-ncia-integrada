# Prompt de Continuidade — SPEC-001 em implementação

## Referência versionada

- Repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`
- Branch: `main`
- Runtime S002: `5e1b35763df091ebf505f8e8f8260c654fc21926`
- Evidência unitária T040: `e5acc691e4de5e91fcb59f48403836c650a54714`
- Estado/handoff anterior: `b2f0d26cd21a915e9b4570adbbd45f5ce4685108`
- Harness de integração T041/T042 versionado na sequência de commits iniciada em `936769bf5d65ae5c8d0351e26c36d5ee29665557` e concluída documentalmente antes deste handoff.
- Manifesto alinhado ao runtime/evidência atual em `65a214bfe8bfc06f22d6d7619928d2cea4a2e5b9`.
- Este arquivo é gravado após esses commits; confirme o HEAD atual antes de qualquer alteração.
- SPEC ativa: `SPEC-001 — Core mínimo + Summary narrativo`.
- Estado: **Em implementação**.

## Prompt pronto para colar em novo chat

```text
Você é o Orquestrador Principal do projeto "Base de Conhecimento com Inteligência Integrada".
Idioma obrigatório: português do Brasil.
Mantra: "Quem não sabe onde está, não sabe para onde quer ir".

ANTES DE QUALQUER ALTERAÇÃO
1. Leia AGENTS.md.
2. Leia .specify/PROJECT_MANIFEST.md.
3. Leia .specify/memory/constitution.md integralmente.
4. Leia specs/000-inventario-profundo-e-contratos/decisao-t097.md.
5. Leia specs/000-inventario-profundo-e-contratos/relatorio-final-spec-000.md.
6. Leia specs/001-core-summary-narrativo/spec.md.
7. Leia specs/001-core-summary-narrativo/plan.md.
8. Leia specs/001-core-summary-narrativo/tasks.md.
9. Leia specs/001-core-summary-narrativo/baseline-definition-of-ready.md.
10. Leia specs/001-core-summary-narrativo/runtime-s002.md.
11. Leia specs/001-core-summary-narrativo/evidencia-unitaria-s003.md.
12. Leia specs/001-core-summary-narrativo/preparacao-integracao-s003.md.
13. Leia specs/001-core-summary-narrativo/matriz-mutacao.md.
14. Leia specs/001-core-summary-narrativo/matriz-evidencia.md.
15. Leia tests/integration/README.md e a suíte tests/integration/test-spec001-summary-integration.php.
16. Leia docs/DEFINITION-OF-DONE.md.
17. Leia este CONTINUIDADE.md inteiro.
18. Confirme branch/HEAD no GitHub e investigue qualquer divergência antes de escrever.

ESTADO COMPROVADO
- SPEC-000 está CONCLUÍDA.
- T097 autorizou SPEC-001, não release/produção/cutover.
- S001/Definition of Ready está PASS documental.
- SPEC-001 está EM IMPLEMENTAÇÃO.
- Post type suportado: somente `post`.
- `page` e CPTs permanecem fora.
- Runtime mínimo S002 está implementado.
- T040 unitário: PASS 15/15.
- PHP lint do runtime: PASS.
- Harness T041/T042: VERSIONADO.
- PHP lint do bootstrap/teste de integração: PASS.
- Integração WordPress real: NOT_RUN.
- B-006 contra Metadata API real: NOT_RUN.
- Browser acceptance: NOT_RUN.
- Package/lifecycle release: NOT_RUN.
- Portanto NÃO declarar Homologação, Release ou produção.

RUNTIME ATUAL
Caminho:
plugin/base-conhecimento-inteligencia-integrada/

Arquivos:
- base-conhecimento-inteligencia-integrada.php
- includes/class-plugin.php
- includes/class-meta-contract.php
- includes/class-summary-store.php
- includes/class-admin-page.php
- assets/css/admin.css

Piso declarado:
- WordPress >= 6.6
- PHP >= 8.1

ESCOPO FUNCIONAL
Usuário: Analista de Conhecimento.
Jornada: selecionar post -> ler -> editar -> salvar -> reler -> confirmar Summary.

Campos:
- objective -> `_bdc_es_objective`
- escalation -> `_bdc_es_escalation`
- important -> `_bdc_es_important`

CONTRATO IMPLEMENTADO
- wp-admin server-rendered;
- GET read-only;
- listagem paginada, 20 posts por página;
- POST autenticado via admin-post;
- nonce vinculado ao post;
- `current_user_can('edit_post', $post_id)` no objeto;
- allowlist exata dos três campos;
- `wp_unslash` no request;
- strings somente;
- limite 32768 bytes por campo antes de sanitizar;
- `trim(sanitize_textarea_field())`;
- vazio = delete;
- omitido = preservar;
- idêntico = NO_CHANGE sem write;
- Metadata API;
- escaping contextual;
- POST-Redirect-GET;
- nenhum payload cru em mensagens de feedback.

B-006 IMPLEMENTADO
Fluxo:
authorize -> validate all -> sanitize all -> snapshot -> diff -> writes mínimos -> reread -> compare.

Mismatch:
compensação best-effort -> reread.

Resultados:
- estado esperado -> SUCCESS;
- snapshot restaurado -> FAIL_SAFE;
- restauração incompleta -> PARTIAL_FAILURE_CRITICAL.

PARTIAL_FAILURE_CRITICAL registra apenas post_id e nomes lógicos dos campos; não registra conteúdo narrativo.

EVIDÊNCIA UNITÁRIA
Arquivo:
tests/unit/spec001-summary-store.php

Resultado: 15 PASS / 0 FAIL.

PASS unitário NÃO promove gates WordPress/browser.

HARNESS DE INTEGRAÇÃO T041/T042
Arquivos:
- tests/integration/bootstrap.php
- tests/integration/phpunit.xml.dist
- tests/integration/test-spec001-summary-integration.php
- tests/integration/README.md

Cobertura preparada:
- G-001 proteção de post_title/post_content/_elementor_data;
- G-020 Metadata API/read/update/delete/allowlist/limite/sanitização/NO_CHANGE/read-after-write;
- G-070 capability por objeto, nonce, IDOR, page rejeitada e GET sem mutação;
- B-006 com filtros reais `update_post_metadata` e `delete_post_metadata`.

Comando esperado quando houver WordPress Core Test Suite + banco:
`WP_TESTS_DIR=/tmp/wordpress-tests-lib phpunit -c tests/integration/phpunit.xml.dist`

IMPORTANTE:
- O harness foi lintado, mas NÃO foi executado contra WordPress real nesta sessão.
- A sessão possui PHP 8.4.23, porém não possui MySQL/MariaDB + WordPress Core Test Suite operacional.
- Tentativa de disponibilizar banco via gerenciador de pacotes não concluiu dentro da janela operacional.
- Não usar mocks para promover T041/T042.

MATRIZ DE EVIDÊNCIA ATUAL
- T040 unitário determinístico: PASS 15/15.
- G-001 Editorial/Elementor: NOT_RUN — harness pronto.
- G-020 Summary em WordPress real: NOT_RUN — harness pronto.
- G-070 Segurança/scope real: NOT_RUN — harness pronto.
- G-110 UI/UX/browser: NOT_RUN.
- G-130 Lifecycle/package: NOT_RUN.
- B-006 gate final: NOT_RUN — unit fault injection PASS; harness WordPress pronto.

FORA DE ESCOPO
- Classificação.
- Review/AI READY.
- Content Extractor.
- Search/Golden/Search Knowledge.
- Analytics/query logging.
- queue.
- tabela/schema/migration.
- REST/AJAX/SPA.
- Foundry/LLM/embeddings/vector/semantic/rerank/agentes.
- aliases/shortcodes de compatibilidade.
- remoção/desativação automática de GRE/KB2Ops/ASI.
- cutover produtivo.
- cinco campos classificatórios restantes do GRE.

COEXISTÊNCIA
B-003 continua não bloqueando desenvolvimento/homologação, mas volta antes de produção, cutover, remoção de legado ou coexistência não controlada de writers.
GO de desenvolvimento != GO de produção.

PRÓXIMO PASSO EXATO
Continuar S003 sem inventar PASS:
1. provisionar/usar ambiente WordPress Core Test Suite real com MySQL/MariaDB efêmero;
2. executar T041 via `tests/integration/phpunit.xml.dist`;
3. analisar qualquer FAIL e corrigir runtime + regressão antes de promover G-001/G-020/G-070;
4. executar T042/fault injection B-006 no mesmo ambiente;
5. versionar saída, versões de WordPress/PHP/DB/PHPUnit e resultado por gate;
6. somente com T041/T042 PASS, avançar T043 browser acceptance;
7. depois avaliar T044 package/lifecycle;
8. T045 relatório DoD/evidência;
9. T046 atualizar CONTINUIDADE e decidir gate.

CRITÉRIO PARA AVANÇAR A T043
- T041 executado em WordPress real;
- G-001 PASS;
- G-020 PASS;
- G-070 PASS;
- T042/B-006 integração PASS;
- nenhum FAIL oculto;
- evidência versionada com ambiente e comando.

CRITÉRIO PARA AVANÇAR A HOMOLOGAÇÃO
- G-001 PASS;
- G-020 PASS;
- G-070 PASS;
- G-110 PASS;
- B-006 PASS em WordPress real;
- nenhum FAIL/NOT_RUN/NOT_CONFIGURED/STALE em gate MUST de Homologação.

REGRA
Constituição, Manifesto, T097, SPEC-001, runtime e evidências versionadas prevalecem sobre memória de chat. Se o HEAD divergir, investigue antes de escrever.
```

## Estado ao encerrar este handoff

S002 está implementado e sintaticamente validado. T040 está PASS 15/15. O harness T041/T042 está versionado e lintado, mas a execução WordPress real permanece NOT_RUN por ausência de ambiente MySQL/MariaDB + WordPress Core Test Suite nesta sessão. A SPEC permanece Em implementação; nenhum GO de Homologação, Release, produção ou cutover foi emitido.
