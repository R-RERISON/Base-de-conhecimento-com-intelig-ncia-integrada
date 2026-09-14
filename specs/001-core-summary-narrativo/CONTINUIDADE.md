# Prompt de Continuidade — SPEC-001 em implementação

## Referência versionada

- Repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`
- Branch: `main`
- Commit do runtime S002: `5e1b35763df091ebf505f8e8f8260c654fc21926`
- Commit da evidência unitária T040: `e5acc691e4de5e91fcb59f48403836c650a54714`
- Commit que atualizou o estado da SPEC: `da5dd1c00500304ccf892eb3efe323f062f426d6`
- Este arquivo é gravado após esses commits; confirme o HEAD atual antes de qualquer alteração.
- SPEC ativa: `SPEC-001 — Core mínimo + Summary narrativo`
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
12. Leia specs/001-core-summary-narrativo/matriz-mutacao.md.
13. Leia specs/001-core-summary-narrativo/matriz-evidencia.md.
14. Leia docs/DEFINITION-OF-DONE.md.
15. Leia este CONTINUIDADE.md inteiro.
16. Confirme branch/HEAD no GitHub e investigue qualquer divergência antes de escrever.

ESTADO COMPROVADO
- SPEC-000 está CONCLUÍDA.
- T097 autorizou SPEC-001, não release/produção/cutover.
- S001/Definition of Ready está PASS documental.
- SPEC-001 está EM IMPLEMENTAÇÃO.
- Post type suportado: somente `post`.
- `page` e CPTs permanecem fora.
- Placeholders antigos `001-core-shell-design-system` e `002-resumo-executivo-integrado` são SUPERSEDIDOS.
- Runtime mínimo S002 implementado no commit 5e1b35763df091ebf505f8e8f8260c654fc21926.
- Suíte unitária inicial versionada no commit e5acc691e4de5e91fcb59f48403836c650a54714.
- Resultado unitário: 15 PASS / 0 FAIL.
- PHP lint dos cinco arquivos PHP de runtime: PASS.
- Integração WordPress real: NOT_RUN.
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

Casos PASS:
- Meta Contract exato;
- read side-effect free;
- update parcial;
- unknown field zero writes;
- oversized zero writes;
- empty/delete;
- NO_CHANGE zero writes;
- falha write #1;
- falha write #2 com compensação;
- falha write #3 com compensação;
- falha delete;
- falha na compensação -> PARTIAL_FAILURE_CRITICAL;
- update+delete;
- page rejeitada;
- capability obrigatória.

IMPORTANTE:
PASS unitário NÃO promove os gates WordPress/browser.

MATRIZ DE EVIDÊNCIA ATUAL
- T040 unitário determinístico: PASS 15/15.
- G-001 Editorial/Elementor: NOT_RUN.
- G-020 Summary em WordPress real: NOT_RUN.
- G-070 Segurança/scope real: NOT_RUN.
- G-110 UI/UX/browser: NOT_RUN.
- G-130 Lifecycle/package: NOT_RUN.
- B-006 gate final: NOT_RUN; unit fault injection PASS, integração real pendente.

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
Continuar S003:
1. T041 criar/executar integração WordPress real para G-001/G-020/G-070;
2. T042 repetir/confirmar fault injection B-006 contra Metadata API real;
3. T043 executar browser acceptance G-110;
4. somente depois avaliar T044 package/lifecycle G-130;
5. registrar T045 relatório de evidência/DoD;
6. atualizar T046 CONTINUIDADE e decidir gate.

CRITÉRIO PARA AVANÇAR A HOMOLOGAÇÃO
- G-001 PASS;
- G-020 PASS;
- G-070 PASS;
- G-110 PASS;
- B-006 PASS em WordPress real;
- nenhum FAIL/NOT_RUN/NOT_CONFIGURED/STALE em gate MUST de Homologação.

REGRA
Constituição, Manifesto, T097, SPEC-001 e evidências versionadas prevalecem sobre memória de chat. Se o HEAD divergir, investigue antes de escrever.
```

## Estado ao encerrar este handoff

S002 está implementado e sintaticamente validado. T040 está PASS 15/15. A SPEC permanece Em implementação porque os gates de integração WordPress, segurança real, browser e B-006 integrado ainda não foram executados. Nenhum GO de produção/cutover foi emitido.
