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
- HEAD confirmado antes do bloco T091: 875be15d50425b71903568e3cac153dd6a67f798
- O commit que contém esta versão representa o fechamento documental de T091; confirme o SHA atual antes da próxima escrita.
- SPEC ativa: SPEC-000 — Inventário Profundo e Contratos dos Projetos de Referência
- Estado: T000–T059 + T090 + T091 concluídos documentalmente; nenhum runtime novo.

BASELINES FIXADAS
- ASI 4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1
- GRE 0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365
- KB2Ops 0.2.1 hardened @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94

ARTEFATOS CENTRAIS
- matriz-paridade-futura.md — arquitetura final T059.
- revisao-wordpress-t090.md — WordPress-first.
- revisao-simplicidade-t091.md — princípio de negação/simplificação.
- catalogo-testes-regressao.md
- matriz-ia-vetor.md
- infraestrutura-propria-minima.md
- riscos-e-drifts.md
- research.md

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
- Site Health -> health checks.
- admin-post baseline; REST sem consumidor negado.
- WP-Cron é trigger, não queue.
- Search Retrieval Projection própria continua justificada.
- não duplicar bounded history + meta revisions.
- taxonomias internas não ganham archive/rewrite público automaticamente.
- provider endpoint configurável precisa revisão SSRF/allowlist.

T091 — SIMPLICIDADE
Arquivo: revisao-simplicidade-t091.md.
Status: PASS, zero bloqueantes.

REGRA PRINCIPAL
- primeira SPEC futura não constrói plataforma completa;
- infraestrutura só nasce quando o próprio vertical slice consome.

RECOMENDAÇÃO PROVISÓRIA DE SPEC-001
- Core mínimo + Summary narrativo (objective/escalation/important), sujeito a T094/T095/T097.
- não incluir Review/Classificação/Search/IA no mesmo slice sem necessidade comprovada.

SIMPLIFICAÇÕES
- Content Extractor só junto do primeiro consumidor Search/IA/qualidade.
- DS incremental por telas reais.
- Settings somente quando consumidos.
- Review em slice próprio.
- Classificação por eixos/slices.
- Site Health somente para capacidades existentes.
- Search post-level antes de item/deep-link quando suficiente.
- Search Knowledge somente quando ranker/Golden comprovar necessidade.
- Golden obrigatório para Search, mas UI CRUD completa não é requisito inicial.
- IA P1 somente após owner estável; adapter mínimo do primeiro provider.

DESCARTADO COMO ABSTRAÇÃO ANTECIPADA
- event bus próprio.
- repository layer genérico.
- service container/DI genérico.
- cache service genérico.
- Operations Center genérico.
- migration orchestrator genérico.
- adapter framework de compatibilidade.
- provider factory multi-vendor sem segundo caso.

CONTINUA POSTERGADO
- Analytics/query logging.
- durable queue.
- item/deep-link até necessidade comprovada.
- RAG.
- embeddings/vector store.
- semantic/hybrid/rerank.
- agentes/tools.
- Foundry File Search como core.
- Word Cloud.
- REST/SPA sem consumidor.

GARANTIAS QUE NÃO PODEM SER REMOVIDAS POR SIMPLICIDADE
- capability + nonce + sanitização + escaping.
- read-after-write.
- rollback/coexistência quando aplicável.
- B-006 em write composto.
- B-001 quando extractor for crítico.
- Golden/benchmark/security quando Search nascer.
- DS/a11y para telas reais.
- preservação de dados canônicos.

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

PRÓXIMO PASSO EXATO — T092
Executar Revisão de Segurança.

T092 DEVE
1. construir threat model das superfícies previstas.
2. revisar capability model por owner e objeto.
3. revisar nonce, método HTTP e CSRF.
4. revisar sanitização, validação e escaping.
5. revisar IDOR/scope de posts e Search detail.
6. revisar taxonomias internas e exposição pública.
7. revisar shortcodes/aliases/compatibilidade legada.
8. revisar provider endpoint/SSRF/host allowlist/secrets/data egress.
9. revisar prompt injection e tool abuse futuros sem criar agentes.
10. revisar migration/purge/ações destrutivas.
11. classificar findings como PASS | ENDURECER | POSTERGAR | BLOQUEAR.
12. não criar runtime.

CRITÉRIO PARA FECHAR T092
- threat model cobre todas as superfícies relevantes.
- cada mutação tem modelo de autorização/CSRF.
- dados/saída/egress têm regras explícitas.
- findings bloqueantes têm tratamento ou são encaminhados objetivamente a T095/T097.
- nenhuma segurança depende de capacidade ainda inexistente.

ORDEM RESTANTE
T092 -> T093 -> T094 -> T095 -> T096 -> T097.

REGRA DE CONTINUIDADE
Repositório/Constituição/Manifesto/SPEC prevalecem sobre memória de chat.
```

## Estado resumido

- SPEC-000 ativa.
- HEAD antes de T091: `875be15d50425b71903568e3cac153dd6a67f798`.
- T050–T059 + T090 + T091 concluídos documentalmente.
- Próximo: T092.
- Runtime novo: inexistente.
- SPEC-001: bloqueada até T097.

## Regra de atualização

Atualizar este arquivo ao concluir T092. Não acumular estados contraditórios.