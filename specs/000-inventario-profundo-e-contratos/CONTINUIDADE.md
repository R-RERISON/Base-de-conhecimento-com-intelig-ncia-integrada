# Prompt de Continuidade — SPEC-000 — Inventário Profundo e Contratos

## 1. Prompt pronto para colar em novo chat

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
7. Confirme o HEAD atual no GitHub.
8. Se qualquer baseline divergir, pare a decisão correspondente e investigue antes de escrever.

PROJETO
- Repositório: R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada
- Branch: main
- HEAD de partida do bloco KB2Ops: 0f42f856a8ff83ba915f7a98f91cb8a053f33f6a
- SPEC ativa: SPEC-000 — Inventário Profundo e Contratos dos Projetos de Referência
- Estado: inventário individual das três referências concluído documentalmente; runtime novo continua bloqueado.

BASELINES CONFIRMADAS
- ASI: R-RERISON/Advanced-search-Intelligence 4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1
- GRE: R-RERISON/Gerenciador-de-Resumo-Executivo-da-Base-de-Conhecimento 0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365
- KB2Ops: R-RERISON/KB2Ops-Operational-Knowledge-Engine 0.2.1 hardened @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94

TAREFAS CONCLUÍDAS
- T000–T002: preparação/governança.
- T010–T019: KB2Ops.
- T020–T034: Advanced Search Intelligence.
- T040–T047: Gerenciador de Resumo Executivo.

PRÓXIMO BLOCO EXATO
T050–T059 — cruzamento consolidado das três referências.

ORDEM OBRIGATÓRIA DO CRUZAMENTO
1. T052 ownership de dados — antes de escolher taxonomia/schema.
2. T053 sobreposição funcional — antes de escolher telas/módulos.
3. T050/T051 consolidar persistência/hooks com ownership resolvido.
4. T054 consolidar drifts/contratos quebrados.
5. T056 WordPress-first — provar o que primitives nativas atendem.
6. T057 infraestrutura própria — somente o que WordPress não atende com qualidade/performance/durabilidade.
7. T055 regressão/Golden — definir gates para as decisões tomadas.
8. T058 IA/vetor — priorizar apenas extensões opcionais após baseline.
9. T059 matriz de paridade final.

Não inverter essa ordem para começar por banco, vetor ou classes.

INVARIANTES VIGENTES
- WordPress-first.
- Princípio de negação antes de adicionar complexidade.
- WordPress/Elementor são fonte editorial.
- O plugin não faz manutenção editorial do post.
- Nunca escrever `_elementor_data` por pipeline derivado.
- Nunca reescrever `post_content` silenciosamente.
- Um Content Extractor canônico alimenta todos os consumidores.
- Qualidade da extração precede qualidade de Search/RAG.
- Persistência confirmada precede qualquer evento de domínio.
- IA sugere; humano decide; WordPress persiste.
- Retrieval precede síntese.
- IA/vetor são opcionais/degradáveis.
- Lexical continua funcional sem IA.
- Sem regressão silenciosa; Golden blockers obrigatórios para Search.
- Nenhuma implementação sem SPEC correspondente.
- Nenhum runtime antes de T097 autorizar SPEC-001.

CONCLUSÕES ASI QUE NÃO DEVEM SER PERDIDAS
1. Não copiar as 12 tabelas como arquitetura futura.
2. Preservar lexical degradável, QueryContext limitado, explicabilidade e Golden Queries.
3. Preservar Item Knowledge/identity/deep-link, reconstruindo sobre extractor único.
4. PostIndex, Item Knowledge, Structural Audit e Word Cloud não podem ter parsers independentes.
5. Vocabulary/bindings/rules têm valor; storage final continua aberto.
6. Queue só nasce se durabilidade/workload justificarem.
7. Migrations/Reconciler/Orchestrator ASI carregam história e não pertencem automaticamente ao greenfield.
8. Events/Interactions/Outcomes têm valor, mas privacy/query retention precisam ser redesenhadas.
9. Tracking público: nonce + rate limit + HMAC + server authority + idempotência.
10. Golden suite vazia não é PASS.
11. Site Health é primitive preferida para diagnostics.
12. GAC fica fora do core.

CONCLUSÕES GRE
1. Oito `_bdc_es_*` são contrato de compatibilidade conhecido.
2. `post_title` é título canônico; `_bdc_es_title` proibida.
3. Metadata API + `edit_post` + nonce + allowlist + sanitização são baseline.
4. Reads são side-effect free; vazio remove meta.
5. Multi-campo não tem atomicidade compensatória comprovada.
6. Coverage Dashboard atual é read-only mas sem workload bound.
7. Não há evidência para tabela/REST/AJAX/cron próprios de Resumo.
8. `Objective_Provider` e `bdc_es_objective_updated` esperados pelo ASI não existem.
9. Futuro: Summary Store interno único + evento após write confirmado.

CONCLUSÕES KB2OPS
1. Baseline correta: 0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94.
2. Content Extractor é principal contrato técnico: `_elementor_data` read-only -> parser determinístico -> render fallback -> `post_content` fallback.
3. O extractor executa somente `table/tablepress`, nunca shortcodes arbitrários.
4. Gap crítico: extração parcialmente não vazia pode omitir custom widget sem acionar render fallback. Criar regressão antes de Search/RAG final.
5. Knowledge Studio/review states/checklist/suggestions são fortes contratos de produto.
6. AI READY runtime = publish + approved + Resumo 8/8 + include_ai. Docs antigos omitem include_ai.
7. `save_review()` emite approval sem checar todos os retornos de persistência; futuro deve confirmar estado antes do evento.
8. `_kb2ops_review_history` e `_kb2ops_view_count` estão fora do registro explícito das metas principais.
9. Summary_Bridge é read-only e duplica as oito keys; deve desaparecer no bounded context unificado.
10. Search atual é provisória: WP_Query/meta LIKE/_elementor_data + score simples. Não portar como engine.
11. Preservar Search scope/detail visibility/UX; combinar depois com engine ASI-inspired.
12. Analytics option/view count são leves, mas insuficientes em privacy/concurrency/outcomes.
13. Design System KB2Ops é principal referência visual: wp-admin shell, server-rendered, CSS namespaced, tokens, progressive disclosure, JS mínimo, sem SPA/IA obrigatória.
14. Activation/migration/uninstall são reversíveis/não destrutivos por default; preservar princípio, não hardcodes legacy.
15. Build determinístico é valioso; release audit existe, mas não há suíte `tests/` reproduzível versionada no baseline.

DRIFTS ESTADO ATUAL
- D-001 ASI -> Objective_Provider: QUEBRADO CONFIRMADO.
- D-002 ASI -> bdc_es_objective_updated: QUEBRADO CONFIRMADO.
- D-003 ASI post_content vs Elementor: direção de resolução confirmada por Content Extractor único.
- D-004 múltiplos extractors: duplicação confirmada; convergir em um serviço.
- D-005 GAC: dependência ambiental, fora do core.
- D-006 GRE vs KB2Ops classifications: sobreposição confirmada, ownership ainda aberto.
- D-007 UI fragmentada: direção DS único KB2Ops-derived confirmada; superfícies finais abertas.
- D-008 AI READY docs vs runtime KB2Ops: drift interno confirmado.

D-006 — NÃO RESOLVER POR NOME
- GRE target_audience vs KB2Ops target_audience;
- GRE affected_service vs KB2Ops service;
- GRE systems_involved vs KB2Ops technologies.
Antes de unir: definir significado, cardinalidade, owner, quem escreve, quem lê, filtros/relatórios e compatibilidade.

ARTEFATOS JÁ MATERIALIZADOS
- inventario-asi.md
- inventario-resumo-executivo.md
- inventario-kb2ops.md
- catalogo-persistencia.md
- catalogo-integracoes.md
- catalogo-testes-regressao.md
- matriz-paridade-futura.md
- riscos-e-drifts.md
- research.md
- tasks.md
- checklists/requisitos.md

O QUE NÃO FAZER NO PRÓXIMO BLOCO
- não criar PHP/JS/CSS/runtime;
- não criar tabelas;
- não criar taxonomias ainda, antes do ownership;
- não criar queue;
- não criar REST/AJAX novo;
- não criar chunks/vectors/embeddings;
- não integrar Foundry;
- não alterar as referências;
- não iniciar SPEC-001;
- não tratar migration legacy como core futuro;
- não confundir similaridade de campos com identidade semântica.

PRÓXIMO PASSO T050–T059 — DELIVERABLES
1. `mapa-ownership-dados.md`: cada dado, owner atual, writer, readers, cardinalidade, canônico/derivado, futuro proposto.
2. `matriz-sobreposicao-funcional.md`: Studio/Search/Resumo/Analytics/Reports/UI/Lifecycle com manter/fundir/descartar.
3. consolidar `catalogo-persistencia.md` após ownership.
4. consolidar `catalogo-integracoes.md` após superfícies.
5. consolidar `riscos-e-drifts.md` com resolução/proposta por drift.
6. `matriz-wordpress-first.md`: primitive nativa testada antes de infra própria.
7. `infraestrutura-propria-justificada.md`: cada tabela/queue/cache projection precisa de requisito + alternativa WP rejeitada + gate de medição.
8. consolidar `catalogo-testes-regressao.md` e Golden gate futuro.
9. `priorizacao-ia-vetor.md`: dependências, fallback, custo, segurança, benefício e ordem.
10. fechar `matriz-paridade-futura.md`.
11. atualizar research/tasks/checklist/CONTINUIDADE.

CRITÉRIO DE CONCLUSÃO T050–T059
- todo dado tem owner único ou razão explícita para compartilhamento;
- toda duplicidade funcional tem destino;
- cada tabela futura tem justificativa negativa contra WordPress primitives;
- cada evento tem writer e consumidores documentados;
- extractor/content ownership está fechado;
- Search baseline e Golden gate estão definidos conceitualmente;
- privacy/retention mínimas estão descritas;
- IA/vetor continuam opcionais e ordenados depois do baseline;
- desconhecidos críticos estão listados para T095;
- nenhum runtime foi criado.

APÓS T050–T059
Executar T090–T097. Somente T097 pode autorizar SPEC-001.

REGRA DE CONTINUIDADE
Se houver divergência entre este arquivo e o repositório/Constituição, repositório e Constituição prevalecem. Investigue antes de alterar.
```

## 2. Estado resumido para humanos

- **SPEC:** SPEC-000.
- **HEAD de partida do bloco KB2Ops:** `0f42f856a8ff83ba915f7a98f91cb8a053f33f6a`.
- **Baselines:** ASI `c0ddff8…`; GRE `1120a534…`; KB2Ops `f2d2aa6…`.
- **Inventários concluídos:** KB2Ops T010–T019; ASI T020–T034; GRE T040–T047.
- **Próximo bloco:** T050–T059 — cruzamento consolidado.
- **SPEC-001:** bloqueada.
- **Runtime novo:** inexistente por decisão arquitetural.

## 3. Evidências novas do bloco KB2Ops

- baseline/HEAD 0.2.1 hardened confirmados;
- runtime completo e persistência mapeados;
- Content Extractor decomposto e gap de custom widgets registrado;
- Summary Bridge comparada ao GRE;
- Search atual classificada como provisória;
- Design System confirmado como referência de produto/UX;
- activation/migration/uninstall reversíveis classificados;
- build determinístico registrado;
- lacuna de testes executáveis versionados formalizada;
- D-003/D-004/D-007 ganharam direção; D-008 foi adicionado.

## 4. Regra de atualização

Atualizar este arquivo após T050–T059 e novamente no gate T090–T097. Substituir estados obsoletos; não acumular handoffs contraditórios.