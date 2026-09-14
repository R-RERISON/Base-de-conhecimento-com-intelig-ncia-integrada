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
- HEAD confirmado antes do bloco T092: 0e9feabc8607f6bb599e8c6cb0621599e1896416
- O commit que contém esta versão representa o fechamento documental de T092; confirme o SHA atual antes da próxima escrita.
- SPEC ativa: SPEC-000 — Inventário Profundo e Contratos dos Projetos de Referência.
- Estado: T000–T059 + T090 + T091 + T092 concluídos documentalmente; nenhum runtime novo.

BASELINES FIXADAS
- ASI 4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1
- GRE 0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365
- KB2Ops 0.2.1 hardened @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94

ARTEFATOS CENTRAIS
- matriz-paridade-futura.md — arquitetura final T059.
- revisao-wordpress-t090.md — WordPress-first.
- revisao-simplicidade-t091.md — princípio de negação.
- revisao-seguranca-t092.md — threat model e condições NO-GO.
- catalogo-testes-regressao.md — gates e Golden.
- matriz-ia-vetor.md.
- infraestrutura-propria-minima.md.
- riscos-e-drifts.md.
- research.md.

INVARIANTES
- WordPress-first + princípio de negação.
- WP_Post/Elementor são fonte editorial absoluta.
- nunca escrever _elementor_data por pipeline derivado.
- nunca reescrever post_content silenciosamente.
- um conceito canônico = um owner lógico.
- projection/index/cache/vector nunca é fonte da verdade.
- Content Extractor único quando houver consumidor.
- persistência confirmada precede evento.
- dual-write permanente proibido.
- adapter/dual-read temporário possui gate de remoção.
- IA sugere; humano decide; owner persiste.
- retrieval precede síntese.
- lexical funciona sem IA/vetor.
- provider externo é adapter, nunca owner.
- nenhum runtime antes de T097.

T059 — PARIDADE FINAL
- única família persistente própria aprovada: Search Retrieval Projection reconstruível.
- Analytics, durable queue, vector/semantic/rerank/agentes postergados.
- blockers B-001–B-007 são contextuais.

T090 — WORDPRESS-FIRST
Status: PASS, zero bloqueantes.
- Summary/Review -> Metadata.
- Classificação -> Taxonomy/Metadata.
- Search Knowledge/Golden -> WordPress-first.
- Site Health para health checks reais.
- admin-post baseline; REST sem consumidor negado.
- WP-Cron é trigger, não queue.
- Search Retrieval Projection própria continua justificada.
- não duplicar bounded history + meta revisions.
- taxonomias internas não ganham archive/rewrite público automaticamente.

T091 — SIMPLICIDADE
Status: PASS, zero bloqueantes.
- primeira SPEC futura não constrói plataforma completa.
- infraestrutura só nasce quando o vertical slice consome.
- candidato provisório SPEC-001: Core mínimo + Summary narrativo (`objective`, `escalation`, `important`), sujeito a T094/T095/T097.
- Content Extractor só junto do primeiro consumidor.
- DS incremental.
- Settings somente se consumidos.
- Review em slice próprio.
- Classificação por eixos/slices.
- Search post-level antes de item/deep-link quando suficiente.
- Search Knowledge somente quando Golden/ranker comprovar necessidade.
- IA P1 após owner estável; sem abstraction multi-provider antecipada.
- descartados no baseline: event bus, repository genérico, service container/DI, cache service, Operations Center, migration orchestrator, adapter framework, provider factory antecipada.

T092 — SEGURANÇA
Arquivo: revisao-seguranca-t092.md.
Status: PASS de arquitetura com endurecimentos obrigatórios e zero blockers globais.

REGRAS CENTRAIS
- capability deve ser verificada no handler e no objeto.
- nonce não substitui autenticação/autorização e não é exactly-once.
- mutações usam POST; GET de leitura é side-effect free.
- `post_id`, scope e estado vindos do cliente são não confiáveis e revalidados server-side.
- mass assignment é NO-GO; usar allowlist e validação/sanitização por campo.
- output usa escaping contextual e tardio.
- projection/index/cache/vector nunca autoriza visibilidade; Search revalida WordPress canônico.
- taxonomias internas começam fail-closed para exposição pública.
- aliases/shortcodes continuam sob B-003.
- provider endpoint arbitrário é NO-GO; URL variável exige HTTPS, host allowlist/validação, redirects controlados e HTTP API segura.
- secrets não entram em repo/log/export/prompt.
- data egress/IA exigem finalidade/campos/provider/capability explícitos.
- prompt injection não concede tool/capability; agentes futuros read-only por default.
- query text/IP/UA/identity não são coletados por default; B-004 continua.
- activation/uninstall não destroem dados; purge é ação separada, autorizada e deliberada.
- replay/duplicate submit usa expected-state/hash/idempotência de domínio quando necessário.

NO-GO T092
- mutação via GET.
- nonce sem capability.
- capability apenas no menu.
- objeto controlado pelo cliente sem autorização por objeto.
- mass assignment.
- output não escapado.
- SQL dinâmico inseguro.
- projection usada como authority de acesso.
- endpoint HTTP arbitrário/SSRF.
- secrets em logs/exports/repo/prompt.
- telemetria detalhada sem B-004.
- purge automático em activation/uninstall default.
- IA/tool persistindo owner canônico sem humano/handler autorizado.

CANDIDATO DE PRIMEIRO SLICE — THREAT MODEL
- abrir Summary: GET, post_id validado, capability `edit_post` no objeto, leitura sem side effect, escaping.
- salvar Summary: POST + nonce + `edit_post` no objeto; allowlist objective/escalation/important; validação/sanitização/limits; Metadata API; B-006; read-after-write; feedback baseado no estado relido.
- não precisa tabela/REST/AJAX/Search/provider/telemetria.

BLOCKERS CONTEXTUAIS
- B-001 Search/RAG/embedding.
- B-002 profiling/cutover classificatório.
- B-003 retirada plugins/aliases.
- B-004 Analytics/query logging.
- B-005 deep-link item.
- B-006 write composto.
- B-007 durable queue/async.

CONTINUA POSTERGADO
- Analytics/query logging.
- durable queue.
- item/deep-link até necessidade comprovada.
- Search Knowledge até necessidade observada.
- RAG.
- embeddings/vector store.
- semantic/hybrid/rerank.
- agentes/tools.
- Foundry File Search como core.
- Word Cloud.
- REST/SPA sem consumidor.

O QUE NÃO DEVE SER FEITO AGORA
- não criar runtime/bootstrap.
- não criar schema/tabela/migration.
- não registrar taxonomies/CPT/meta finais.
- não criar Search index/Golden runtime.
- não implementar aliases/adapters.
- não integrar Foundry/chamar LLM.
- não criar provider abstraction runtime.
- não criar chunks/embeddings/vector store.
- não criar agentes/tools.
- não iniciar SPEC-001.

PRÓXIMO PASSO EXATO — T093
Executar Revisão de QA/Regressão.

T093 DEVE
1. reler catalogo-testes-regressao.md e revisões T090–T092.
2. transformar contratos em matriz de evidência executável por slice.
3. definir pacote mínimo de testes do candidato Core+Summary.
4. mapear testes unitários, integração WordPress, browser/manual, lifecycle/package e rollback.
5. converter NO-GO T092 em testes negativos obrigatórios.
6. definir estados PASS | FAIL | NOT_TESTED | NOT_APPLICABLE com regras claras.
7. impedir suíte vazia/ausente/stale de virar PASS.
8. preservar Golden como gate apenas quando Search for afetada.
9. manter benchmark somente para caminhos críticos aplicáveis; não inventar números.
10. definir evidência de código/pacote testado igual ao publicado.
11. classificar findings como PASS | COBRIR | POSTERGAR | BLOQUEAR.
12. não criar runtime.

CRITÉRIO PARA FECHAR T093
- candidato de primeiro slice possui plano de teste completo e pequeno.
- controles T092 possuem testes negativos correspondentes quando aplicáveis.
- cada gate futuro tem evidência mínima ou estado N/A/POSTERGADO explícito.
- nenhum NOT_TESTED é interpretado como PASS.
- findings bloqueantes têm tratamento ou encaminhamento objetivo a T095/T097.

ORDEM RESTANTE
T093 -> T094 -> T095 -> T096 -> T097.

REGRA DE CONTINUIDADE
Repositório/Constituição/Manifesto/SPEC prevalecem sobre memória de chat.
```

## Estado resumido

- SPEC-000 ativa.
- HEAD antes de T092: `0e9feabc8607f6bb599e8c6cb0621599e1896416`.
- T050–T059 + T090–T092 concluídos documentalmente.
- Próximo: T093.
- Runtime novo: inexistente.
- SPEC-001: bloqueada até T097.

## Regra de atualização

Atualizar este arquivo ao concluir T093. Não acumular estados contraditórios.