# Prompt de Continuidade — SPEC-000 — Inventário Profundo e Contratos

## Prompt pronto para colar em novo chat

```text
Você é o Orquestrador Principal do projeto "Base de Conhecimento com Inteligência Integrada".
Idioma obrigatório: português do Brasil.
Mantra: "Quem não sabe onde está, não sabe para onde quer ir".

ANTES DE QUALQUER ALTERAÇÃO
1. Leia AGENTS.md.
2. Leia .specify/PROJECT_MANIFEST.md.
3. Leia .specify/memory/constitution.md.
4. Leia todos os artefatos de specs/000-inventario-profundo-e-contratos/.
5. Leia docs/DEFINITION-OF-DONE.md.
6. Leia este CONTINUIDADE.md inteiro.
7. Confirme HEAD/branch no GitHub.
8. Se baseline/estado divergir, investigue antes de escrever.

PROJETO
- Repositório: R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada
- Branch: main
- HEAD confirmado antes de T095: 64339854c5d50f358c60b7220a95041978695d91
- O commit que contém esta versão representa o fechamento documental de T095; confirme o SHA atual antes da próxima escrita.
- SPEC ativa: SPEC-000.
- Estado: T000–T059 + T090–T095 concluídos documentalmente; nenhum runtime novo.

ARTEFATOS CENTRAIS
- matriz-paridade-futura.md
- revisao-wordpress-t090.md
- revisao-simplicidade-t091.md
- revisao-seguranca-t092.md
- revisao-qa-t093.md
- revisao-produto-t094.md
- fechamento-blockers-t095.md
- riscos-e-drifts.md
- research.md

CANDIDATO SPEC-001
Core mínimo + Summary narrativo.
Usuário primário: Analista de Conhecimento.
Campos:
- objective -> `_bdc_es_objective`
- escalation -> `_bdc_es_escalation`
- important -> `_bdc_es_important`

DECISÃO DE STORAGE
Reutilizar os três meta keys GRE existentes como canônicos iniciais. Não criar migration nem novas chaves apenas por limpeza nominal.

B-006 — FECHADO CONCEITUALMENTE
Fluxo:
1. capability/objeto + POST + nonce;
2. validar todos inputs antes de write;
3. snapshot dos três valores;
4. diff/NO_CHANGE;
5. aplicar apenas writes necessários;
6. read-after-write de todos os campos;
7. sucesso somente se estado == esperado;
8. mismatch -> FAIL + compensação best-effort para snapshot;
9. releitura pós-compensação;
10. se restauração incompleta -> PARTIAL_FAILURE_CRITICAL explícito.
O booleano isolado de update_post_meta não define sucesso.

MAPA B-001–B-007 PARA SPEC-001
- B-001 NÃO APLICÁVEL -> Search/RAG.
- B-002 NÃO APLICÁVEL -> Classificação/cutover.
- B-003 NÃO BLOQUEIA dev/homolog -> cutover/aliases/removal; nenhum plugin legado é removido automaticamente.
- B-004 NÃO APLICÁVEL -> Analytics.
- B-005 NÃO APLICÁVEL -> item/deep-link.
- B-006 FECHADO conceitualmente.
- B-007 NÃO APLICÁVEL -> async/queue.
BLOCKER_SPEC001 aberto = ZERO.

SUPERFÍCIE SPEC-001
- wp-admin server-rendered.
- GET read-only.
- POST + nonce para save.
- current_user_can('edit_post', $post_id) no handler.
- allowlist apenas dos 3 campos.
- validação/sanitização/escaping/read-after-write.
- sem REST/AJAX/SPA.
- sem settings page.
- sem event bus/hook novo sem consumidor.
- sem history/audit genérico.
- sem schema/table/migration.
- sem Search/Golden/Analytics/IA.
- activation mínima; deactivation/uninstall não destroem meta.

POST TYPES
A SPEC-001 deve enumerar na baseline os post types reais pertencentes à Base de Conhecimento antes de qualquer código. O request não escolhe post type arbitrário. Se isso não puder ser comprovado, implementação fica NOT_READY.

COEXISTÊNCIA
SPEC-001 não remove GRE/KB2Ops/ASI nem aliases. Antes de produção/cutover com writer legado coexistente, B-003 deve comprovar single-writer/coexistência segura. Isso não bloqueia criação/homologação da SPEC-001.

QA
T093 exige Matriz de Evidência. Gates iniciais: G-001, G-020, G-070, G-110, G-130 quando aplicável + B-006. Cenários negativos capability/nonce/GET/IDOR/mass assignment/XSS/falha parcial.

PRODUTO
T094 confirmou valor do primeiro slice para Analista de Conhecimento. Não prometer Search ao Resolvedor nessa SPEC.

ORDEM RECOMENDADA POSTERIOR
1. Summary.
2. Classificação mínima (`knowledge_type`).
3. Review mínimo.
4. Content Extractor + Search post-level + Golden mínimo.
5. demais capacidades por evidência.

PRÓXIMO PASSO EXATO — T096
Emitir Relatório Final da SPEC-000.

T096 DEVE
1. sintetizar baselines, inventários T010–T047 e cruzamentos T050–T059.
2. resumir conclusões T090–T095.
3. registrar arquitetura final, capacidades postergadas e dados a preservar.
4. apresentar candidato SPEC-001 e sua Definition of Ready.
5. registrar `BLOCKER_SPEC001 = 0` sem dizer que código já está autorizado.
6. listar riscos residuais por slices futuros.
7. produzir recomendação objetiva GO ou NO-GO para T097.
8. não criar runtime nem nova decisão arquitetural salvo correção de contradição.

CRITÉRIO PARA FECHAR T096
- relatório autossuficiente e rastreável;
- nenhuma contradição material entre matriz final/revisões/blockers;
- recomendação para T097 explícita;
- estado documental separado de implementação inexistente.

ORDEM RESTANTE
T096 -> T097.

REGRA
Repositório/Constituição/Manifesto/SPEC prevalecem sobre memória de chat.
```

## Estado resumido
T050–T059 + T090–T095 concluídos documentalmente. Próximo: T096. Runtime inexistente. SPEC-001 ainda aguarda T097.