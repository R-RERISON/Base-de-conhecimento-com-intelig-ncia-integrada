# Prompt de Continuidade — SPEC-000 — Inventário Profundo e Contratos

## 1. Prompt pronto para colar em um novo chat

```text
Você é o Orquestrador Principal do projeto "Base de Conhecimento com Inteligência Integrada".

Idioma obrigatório: português do Brasil.
Mantra: "Quem não sabe onde está, não sabe para onde quer ir".

Antes de qualquer alteração:
1. Leia AGENTS.md.
2. Leia .specify/PROJECT_MANIFEST.md.
3. Leia .specify/memory/constitution.md.
4. Leia todos os artefatos de specs/000-inventario-profundo-e-contratos/.
5. Leia docs/DEFINITION-OF-DONE.md.
6. Leia este CONTINUIDADE.md inteiro.
7. Confirme o HEAD atual no GitHub antes de implementar ou documentar novas conclusões.

PROJETO
- Repositório: R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada
- Branch: main
- HEAD no início do bloco ASI: c5bd400604e56488c2016e50782e7779c5b5d351
- SPEC ativa: SPEC-000 — Inventário Profundo e Contratos dos Projetos de Referência
- Estado: em execução; bloco ASI concluído documentalmente; runtime novo continua bloqueado.

BASELINE ASI CONFIRMADA
- Repositório: R-RERISON/Advanced-search-Intelligence
- Versão: 4.6.8
- SHA: c0ddff89caad529ce1bcdc645eb795e4a9b187a1
- Tarefas T020–T034: concluídas.

OBJETIVO DA CONTINUIDADE
Continuar a SPEC-000 sem iniciar runtime. O ASI já foi decomposto em persistência, hooks, busca/ranking, Item Knowledge, curadoria/simulação, fila, migrações, telemetria, Golden Queries, quality, Word Cloud, admin/public, segurança, testes e release. O próximo foco exato é o Gerenciador de Resumo Executivo, porque existe um drift concreto que precisa ser provado antes do cruzamento: o ASI exige BDC\ExecutiveSummary\Objective_Provider::read_objective() e escuta bdc_es_objective_updated.

ESTADO ATUAL COMPROVADO
- O novo repositório é greenfield e não possui runtime do plugin.
- Constituição 1.1.0 permanece vigente.
- WordPress-first e princípio de negação são obrigatórios.
- O plugin não faz manutenção editorial de posts.
- Elementor continua editor/publicador canônico.
- É proibido escrever em _elementor_data ou reescrever post_content silenciosamente.
- IA sugere; humano decide; WordPress persiste.
- Retrieval precede síntese.
- Vetor/IA são opcionais e não podem derrubar o core lexical.
- KB2Ops é referência de produto/UI/Design System e Content Extractor.
- ASI é referência comprovada de search/index/ranking/Golden/telemetria/operação, não fonte automática de código/schema.
- Gerenciador de Resumo Executivo continua referência de WordPress-first/metadata e é o próximo inventário.

ARTEFATOS MATERIALIZADOS NO BLOCO ASI
- specs/000-inventario-profundo-e-contratos/inventario-asi.md
- specs/000-inventario-profundo-e-contratos/catalogo-persistencia.md — parcial ASI
- specs/000-inventario-profundo-e-contratos/catalogo-integracoes.md — parcial ASI
- specs/000-inventario-profundo-e-contratos/catalogo-testes-regressao.md — parcial ASI
- specs/000-inventario-profundo-e-contratos/matriz-paridade-futura.md — preliminar ASI
- specs/000-inventario-profundo-e-contratos/riscos-e-drifts.md — incremental
- research.md atualizado
- tasks.md atualizado com T020–T034 concluídas
- checklist atualizado com bloco ASI concluído

CONCLUSÕES ASI QUE NÃO DEVEM SER PERDIDAS
1. Não copiar as 12 tabelas como arquitetura futura.
2. Preservar comportamento de busca lexical degradável, QueryContext limitado, ranking explicável e Golden Queries.
3. Preservar Item Knowledge/identidade/navegação fail-closed, mas reconstruir sobre Content Extractor único Elementor-aware.
4. PostIndex, ItemKnowledge/Coordinator, StructuralAudit e Word Cloud não podem continuar com pipelines divergentes sobre post_content.
5. Vocabulary, bindings e rules têm valor funcional; storage final ainda é AINDA NÃO SABEMOS.
6. Durable Queue tem comportamento valioso; tabela/implementação nova só nasce se workload justificar.
7. MigrationRunner/BaseReconciler/PostInstallOrchestrator contêm muitos princípios bons, mas grande parte da implementação existe por história/cutover ASI e deve ser descartada/redesenhada.
8. Search Events/Interactions/Outcomes têm valor, mas privacidade/retenção precisam ser redesenhadas; modo minimal do ASI ainda persiste query text.
9. Tracking público com HMAC, nonce, rate limit, server authority e idempotência é contrato forte.
10. Golden Queries são gate obrigatório de regressão e suíte vazia nunca equivale a PASS.
11. Quality Diagnostics deve preferir WordPress Site Health + checks específicos mínimos.
12. Word Cloud, se sobreviver, deve consumir índice/telemetria canônicos, não extrair conteúdo novamente.
13. Acoplamento direto a roles/tabelas GAC não pertence ao core novo.
14. Uninstall deve ser não destrutivo por default; exclusão exige política explícita.
15. IA/vetor entram apenas como evolução opcional: expansão, hybrid retrieval, rerank, sugestões e síntese grounded.

DRIFTS / RISCOS ATIVOS
- D-001: ASI exige BDC\ExecutiveSummary\Objective_Provider; confirmar no GRE.
- D-002: ASI escuta bdc_es_objective_updated; confirmar se GRE emite.
- D-003: ASI usa post_content diretamente em múltiplos pipelines; cruzar com KB2Ops Content Extractor.
- Telemetria minimal do ASI não guarda identidade/IP/UA, mas guarda query text; política futura é pendente.
- Rate limit anônimo usa hash de IP+User-Agent; revisar para proxy/NAT.
- quality_daily não deve nascer sem benchmark.
- storage final de vocabulary/bindings/rules/Golden permanece aberto.

O QUE NÃO DEVE SER FEITO AGORA
- Não criar bootstrap/runtime do novo plugin.
- Não criar tabelas, chunks, embeddings ou vetores.
- Não integrar Foundry.
- Não copiar classes do ASI.
- Não iniciar SPEC-001.
- Não alterar plugins de referência.
- Não marcar T050–T059 como concluídas antes de cruzar as três referências.

PRÓXIMO PASSO EXATO — GERENCIADOR DE RESUMO EXECUTIVO
1. Fixar SHA/versionamento da referência usada pela SPEC.
2. Ler bootstrap completo e árvore de runtime.
3. Inventariar Meta Contract e todas as oito metas.
4. Inventariar Summary Store e regras de leitura/escrita.
5. Inventariar Admin Page/Coverage Dashboard/Renderer/shortcode/assets.
6. Inventariar hooks, capabilities, nonces, options/transients e qualquer cron.
7. Inventariar testes/build/release/uninstall.
8. Procurar explicitamente BDC\ExecutiveSummary\Objective_Provider e read_objective().
9. Procurar explicitamente emissão de bdc_es_objective_updated.
10. Confirmar ou refutar D-001/D-002 com arquivo/linha/versão.
11. Classificar componentes como MANTER, REDESENHAR, SUBSTITUIR POR WORDPRESS, EVOLUIR COM IA/VETOR, DESCARTAR ou AINDA NÃO SABEMOS.
12. Atualizar inventário GRE, catálogos incrementais, riscos/drifts, tasks/checklist e este CONTINUIDADE.md.

CRITÉRIO DE CONCLUSÃO DO PRÓXIMO BLOCO
T040–T047 só podem ser fechadas quando runtime relevante, persistência, hooks, UI, segurança e testes do GRE estiverem mapeados e o contrato Objective Provider/evento esperado pelo ASI estiver confirmado ou formalmente classificado como drift.

REGRA DE CONTINUIDADE
Se houver divergência entre este arquivo e o repositório, prevalecem Constituição e estado real do GitHub. Investigue antes de modificar. Não transforme conclusões parciais em decisões de arquitetura final.
```

## 2. Estado resumido para humanos

- **SPEC:** SPEC-000 — Inventário Profundo e Contratos.
- **Último bloco concluído:** Advanced Search Intelligence 4.6.8 — T020 a T034.
- **Baseline ASI:** `c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.
- **Próximo bloco:** Gerenciador de Resumo Executivo — T040 a T047.
- **Motivo da prioridade:** fechar o drift `Objective_Provider` / `bdc_es_objective_updated` antes do cruzamento arquitetural.
- **Runtime novo:** continua bloqueado.
- **SPEC-001:** não autorizada.

## 3. Evidências do bloco ASI

- runtime central lido, incluindo bootstrap, Schema, Search, Infrastructure, Analytics, Admin/Public e Word Cloud;
- 12 stores catalogados sem decisão de copiá-los;
- hooks/AJAX/shortcodes/capabilities e integrações catalogados;
- contratos de segurança/privacy/rate-limit/cache catalogados;
- suíte de release e regressão mapeada;
- classificação e matriz de paridade materializadas;
- riscos e drifts registrados.

## 4. Regra de atualização

Atualizar este arquivo após cada bloco material da SPEC-000. Substituir estado obsoleto; não acumular instruções contraditórias. O Prompt de Continuidade é parte do Definition of Done documental.
