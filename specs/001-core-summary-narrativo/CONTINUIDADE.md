# Prompt de Continuidade — SPEC-001 em implementação

## Referência versionada

- Repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`
- Branch: `main`
- Baseline anterior a este bloco: `fbf71a46eb88a489de6b6cc5c8e2fa974fc1b8c6`
- SPEC ativa: `SPEC-001 — Core mínimo + Summary narrativo`
- Estado: **Em implementação / Evidência S003**.
- Confirme o HEAD atual antes de qualquer nova alteração.

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
5. Leia specs/001-core-summary-narrativo/spec.md.
6. Leia specs/001-core-summary-narrativo/plan.md.
7. Leia specs/001-core-summary-narrativo/tasks.md.
8. Leia specs/001-core-summary-narrativo/baseline-definition-of-ready.md.
9. Leia specs/001-core-summary-narrativo/runtime-s002.md.
10. Leia specs/001-core-summary-narrativo/evidencia-unitaria-s003.md.
11. Leia specs/001-core-summary-narrativo/preparacao-integracao-s003.md.
12. Leia specs/001-core-summary-narrativo/onclick-diagnostics-s003.md.
13. Leia specs/001-core-summary-narrativo/matriz-mutacao.md.
14. Leia specs/001-core-summary-narrativo/matriz-evidencia.md.
15. Leia docs/DEFINITION-OF-DONE.md.
16. Leia este CONTINUIDADE.md inteiro.
17. Confirme branch/HEAD no GitHub e investigue qualquer divergência antes de escrever.

ESTADO COMPROVADO
- SPEC-000 está CONCLUÍDA.
- T097 autorizou SPEC-001, não release/produção/cutover.
- S001/Definition of Ready está PASS documental.
- SPEC-001 está EM IMPLEMENTAÇÃO.
- Post type suportado: somente `post`.
- Runtime mínimo S002 implementado.
- Suíte unitária: 15 PASS / 0 FAIL.
- PHP lint do runtime: PASS.
- Harness PHPUnit WordPress real em tests/integration/: PREPARADO, execução real NOT_RUN.
- Runner onclick temporário com JSON: IMPLEMENTADO, execução no ambiente alvo NOT_RUN.
- Integração WordPress real: NOT_RUN.
- Browser acceptance: NOT_RUN.
- Package/lifecycle release: NOT_RUN.
- NÃO declarar Homologação, Release ou produção.

RUNTIME ATUAL
Caminho:
plugin/base-conhecimento-inteligencia-integrada/

Arquivos de produto:
- base-conhecimento-inteligencia-integrada.php
- includes/class-plugin.php
- includes/class-meta-contract.php
- includes/class-summary-store.php
- includes/class-admin-page.php
- assets/css/admin.css

Arquivo TEMPORÁRIO de homologação:
- includes/class-diagnostics-runner.php

O runner só é carregado com:
define( 'BDC_KB_ENABLE_DIAGNOSTICS', true );

Sem a flag, nenhum hook/botão de diagnóstico é registrado.

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
- current_user_can('edit_post', $post_id) no objeto;
- allowlist exata dos três campos;
- wp_unslash no request;
- strings somente;
- limite 32768 bytes por campo antes de sanitizar;
- trim(sanitize_textarea_field());
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

PARTIAL_FAILURE_CRITICAL registra somente post_id e nomes lógicos dos campos; não registra conteúdo narrativo.

METODOLOGIA ONCLICK TEMPORÁRIA
Objetivo: executar diagnóstico assistido dentro do WordPress real e gerar JSON de análise, sem deixar sujeira.

Ativação temporária:
define( 'BDC_KB_ENABLE_DIAGNOSTICS', true );

Na tela Base de Conhecimento aparece para manage_options:
"Executar diagnóstico e gerar JSON".

O clique:
1. remove fixtures antigas marcadas pelo próprio runner;
2. cria post/page temporários com marker `_bdc_kb_diagnostic_fixture=spec001-onclick-v1`;
3. executa checks G-001/G-020/G-070 e fault injection B-006;
4. remove filtros temporários em finally;
5. hard-delete das fixtures em finally;
6. verifica resíduos;
7. devolve `bdc-kb-diagnostics-*.json` sem gravar relatório no WP.

O JSON contém schema/versionamento, ambiente, hashes SHA-256 do runtime, checks, summary e cleanup.
`summary.overall=PASS` exige zero FAIL e `cleanup.residual_fixtures=0`.

IMPORTANTE:
- onclick NÃO substitui browser acceptance G-110;
- nonce in-process não substitui validação HTTP completa do handler;
- após gerar o JSON, desabilitar/remover a flag;
- antes de T044/G-130/package, remover integralmente runner, require e hook temporários;
- package final NÃO pode conter onclick de teste.

EVIDÊNCIA UNITÁRIA
Arquivo:
tests/unit/spec001-summary-store.php

Resultado: 15/15 PASS.

INTEGRAÇÃO WORDPRESS PREPARADA
Arquivos:
- tests/integration/bootstrap.php
- tests/integration/phpunit.xml.dist
- tests/integration/test-spec001-summary-integration.php
- tests/integration/README.md

Execução prevista:
WP_TESTS_DIR=/tmp/wordpress-tests-lib phpunit -c tests/integration/phpunit.xml.dist

MATRIZ DE EVIDÊNCIA ATUAL
- T040 unitário: PASS 15/15.
- T041 integração WordPress: NOT_RUN; PHPUnit e onclick READY.
- G-001: NOT_RUN.
- G-020: NOT_RUN.
- G-070: NOT_RUN; onclick cobre parte in-process quando executado.
- T042/B-006 integração: NOT_RUN; unit PASS; PHPUnit/onclick READY.
- G-110: NOT_RUN.
- G-130: NOT_RUN.

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
B-003 não bloqueia desenvolvimento/homologação, mas volta antes de produção, cutover, remoção de legado ou coexistência não controlada de writers.
GO de desenvolvimento != GO de produção.

PRÓXIMO PASSO EXATO
1. Instalar/atualizar plugin em ambiente de homologação WordPress real.
2. Ativar temporariamente `BDC_KB_ENABLE_DIAGNOSTICS=true`.
3. Abrir Base de Conhecimento e executar o onclick.
4. Preservar o JSON gerado como evidência.
5. Confirmar `cleanup.residual_fixtures=0`.
6. Desativar/remover imediatamente a flag.
7. Analisar qualquer FAIL antes de nova mudança.
8. Executar o harness PHPUnit real quando WordPress Test Suite + DB estiverem disponíveis.
9. Só depois seguir para T043 browser acceptance.

CRITÉRIO PARA AVANÇAR
- JSON onclick sem FAIL e zero resíduos é evidência auxiliar de T041/T042;
- G-001/G-020/G-070/B-006 só mudam para PASS quando a evidência exigida estiver completa;
- G-110 continua NOT_RUN até browser acceptance;
- nenhum gate MUST ativo pode ser promovido por suposição.

REGRA DE LIMPEZA ANTES DO RELEASE
T044/G-130 deve remover:
- BDC_KB_ENABLE_DIAGNOSTICS do ambiente;
- class-diagnostics-runner.php;
- require condicional no bootstrap;
- registro condicional no Plugin;
- qualquer fixture marcada remanescente.
Depois repetir lint/regressão.

REGRA
Constituição, Manifesto, T097, SPEC-001, código e evidências versionadas prevalecem sobre memória de chat. Se o HEAD divergir, investigue antes de escrever.
```

## Estado ao encerrar este handoff

S002 permanece implementado. T040 permanece PASS 15/15. A metodologia onclick temporária e o harness PHPUnit estão preparados para executar T041/T042 em WordPress real. Nenhum gate WordPress/browser foi promovido sem execução. O runner é transitório e possui gate explícito de remoção antes de package/release.
