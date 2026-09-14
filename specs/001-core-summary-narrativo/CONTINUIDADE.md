# Prompt de Continuidade — SPEC-001 em implementação

## Referência versionada

- Repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`
- Branch: `main`
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
8. Leia specs/001-core-summary-narrativo/runtime-s002.md.
9. Leia specs/001-core-summary-narrativo/evidencia-unitaria-s003.md.
10. Leia specs/001-core-summary-narrativo/preparacao-integracao-s003.md.
11. Leia specs/001-core-summary-narrativo/onclick-diagnostics-s003.md.
12. Leia specs/001-core-summary-narrativo/diagnostics-hardening-v2.md.
13. Leia specs/001-core-summary-narrativo/browser-acceptance-t043.md.
14. Leia specs/001-core-summary-narrativo/browser-acceptance-json-schema.md.
15. Leia specs/001-core-summary-narrativo/matriz-mutacao.md.
16. Leia specs/001-core-summary-narrativo/matriz-evidencia.md.
17. Leia docs/DEFINITION-OF-DONE.md.
18. Leia este CONTINUIDADE.md inteiro.
19. Confirme branch/HEAD no GitHub e investigue qualquer divergência antes de escrever.

ESTADO COMPROVADO
- SPEC-000 está CONCLUÍDA.
- T097 autorizou SPEC-001, não release/produção/cutover.
- S001/Definition of Ready está PASS documental.
- SPEC-001 está EM IMPLEMENTAÇÃO.
- Post type suportado: somente `post`.
- Runtime mínimo S002 implementado.
- Suíte unitária: 15 PASS / 0 FAIL.
- PHP lint do runtime: PASS.
- Harness PHPUnit WordPress real em tests/integration/: PREPARADO; execução real NOT_RUN.
- Runner onclick técnico v2: IMPLEMENTADO; execução no ambiente alvo NOT_RUN.
- Browser acceptance guiado com JSON: IMPLEMENTADO; execução manual NOT_RUN.
- Integração WordPress real: NOT_RUN.
- Package/lifecycle release: NOT_RUN.
- NÃO declarar Homologação, Release ou produção.

RUNTIME DE PRODUTO
plugin/base-conhecimento-inteligencia-integrada/
- base-conhecimento-inteligencia-integrada.php
- includes/class-plugin.php
- includes/class-meta-contract.php
- includes/class-summary-store.php
- includes/class-admin-page.php
- assets/css/admin.css

ARQUIVOS TEMPORÁRIOS DE HOMOLOGAÇÃO
- includes/class-diagnostics-runner.php
- includes/class-browser-acceptance.php

Eles só são carregados quando:
define( 'BDC_KB_ENABLE_DIAGNOSTICS', true );

Sem essa flag, nenhum hook, botão ou painel de teste é registrado.

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
- strings somente;
- limite 32768 bytes por campo antes de sanitizar;
- trim(sanitize_textarea_field());
- vazio = delete;
- omitido = preservar;
- idêntico = NO_CHANGE sem write;
- Metadata API;
- escaping contextual;
- POST-Redirect-GET.

B-006 IMPLEMENTADO
Fluxo:
authorize -> validate all -> sanitize all -> snapshot -> diff -> writes mínimos -> reread -> compare.

Resultados:
- estado esperado -> SUCCESS;
- snapshot restaurado -> FAIL_SAFE;
- restauração incompleta -> PARTIAL_FAILURE_CRITICAL.

ONCLICK TÉCNICO V2
- schema JSON 1.1.0;
- marker `_bdc_kb_diagnostic_fixture=spec001-onclick-v2`;
- exige `manage_options` + nonce;
- cria apenas fixtures efêmeras marcadas;
- cleanup em lotes de 100 até esgotar marker ou atingir guard rail;
- residual count com `WP_Query::found_posts`;
- resultado global PASS exige zero FAIL e zero resíduos;
- cobre G-001, G-020, parte de G-070 e B-006;
- não persiste relatório.

BROWSER ACCEPTANCE TEMPORÁRIO
- exige a mesma flag + `manage_options` + nonce;
- coleta oito checks G-110 observados manualmente;
- gera `bdc-kb-browser-acceptance-*.json`;
- usa `source=operator_assertion` e `manual_browser_observation`;
- não grava respostas no WordPress;
- não é automação E2E.

MATRIZ ATUAL
- T040 unitário: PASS 15/15.
- T041 integração WordPress: NOT_RUN; PHPUnit + onclick v2 preparados.
- G-001: NOT_RUN.
- G-020: NOT_RUN.
- G-070: NOT_RUN.
- T042/B-006 integração: NOT_RUN; unit PASS; harness/onclick preparados.
- G-110: NOT_RUN; coletor JSON preparado.
- G-130: NOT_RUN.

PRÓXIMO PASSO EXATO NO AMBIENTE ALVO
1. Atualizar o plugin em homologação.
2. Ativar temporariamente `BDC_KB_ENABLE_DIAGNOSTICS=true`.
3. Abrir Base de Conhecimento.
4. Executar **Executar diagnóstico e gerar JSON**.
5. Confirmar no JSON `summary.overall=PASS` e `cleanup.residual_fixtures=0`.
6. Executar manualmente a jornada real do Summary no browser.
7. Preencher o painel G-110 e gerar `bdc-kb-browser-acceptance-*.json`.
8. Desativar/remover imediatamente a flag.
9. Enviar os dois JSONs para revisão/versionamento.
10. Corrigir qualquer FAIL antes de promover gates.

CRITÉRIO PARA AVANÇAR
- nenhum JSON técnico com FAIL;
- zero resíduos de fixtures;
- G-110 sem FAIL na evidência manual revisada;
- gates só mudam para PASS após evidência real do ambiente alvo;
- não promover Homologação por suposição.

REGRA DE LIMPEZA ANTES DO RELEASE
T044/G-130 deve remover:
- BDC_KB_ENABLE_DIAGNOSTICS do ambiente;
- class-diagnostics-runner.php;
- class-browser-acceptance.php;
- requires condicionais;
- registros/hook temporários;
- qualquer fixture `spec001-onclick-v2`.
Depois repetir lint/regressão e comprovar ausência das ferramentas no package.

FORA DE ESCOPO
Classificação; Review/AI READY; Extractor; Search; Analytics; queue; tabela/schema/migration; REST/AJAX/SPA; Foundry/LLM/vector; aliases legados; cutover produtivo.

REGRA
Constituição, Manifesto, T097, SPEC-001, código e evidências versionadas prevalecem sobre memória de chat. Se o HEAD divergir, investigue antes de escrever.
```

## Estado ao encerrar este handoff

S002 permanece implementado. T040 permanece PASS 15/15. O harness PHPUnit, o onclick técnico v2 e o coletor temporário de browser acceptance estão preparados. Nenhum gate WordPress/browser foi promovido sem execução real. As duas ferramentas temporárias possuem gate explícito de remoção antes de T044/G-130/package.
