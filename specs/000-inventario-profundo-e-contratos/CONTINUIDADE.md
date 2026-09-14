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
- HEAD confirmado antes do bloco T090: bc5594c48bdabd22f3575477509fb83f53ad2176
- O commit que contém esta versão representa o fechamento documental de T090; confirme o SHA atual antes da próxima escrita.
- SPEC ativa: SPEC-000 — Inventário Profundo e Contratos dos Projetos de Referência
- Estado: T000–T059 + T090 concluídos documentalmente; nenhum runtime novo.

BASELINES FIXADAS
- ASI 4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1
- GRE 0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365
- KB2Ops 0.2.1 hardened @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94

ARTEFATOS CENTRAIS
- matriz-paridade-futura.md — visão executiva final T059.
- revisao-wordpress-t090.md — revisão WordPress-first T090.
- mapa-ownership-dados.md
- catalogo-persistencia.md
- catalogo-integracoes.md
- mapa-contratos-quebrados.md
- matriz-wordpress-first.md
- infraestrutura-propria-minima.md
- catalogo-testes-regressao.md
- matriz-ia-vetor.md
- riscos-e-drifts.md
- research.md

INVARIANTES
- WordPress-first + princípio de negação.
- WP_Post/Elementor são fonte editorial absoluta.
- nunca escrever _elementor_data por pipeline derivado.
- nunca reescrever post_content silenciosamente.
- um conceito canônico = um owner lógico.
- projection/index/cache/vector nunca é fonte da verdade.
- Content Extractor canônico alimenta downstream.
- persistência confirmada precede evento.
- dual-write permanente proibido.
- adapter/dual-read temporário possui gate de remoção.
- IA sugere; humano decide; owner persiste.
- retrieval precede síntese.
- IA/vetor opcionais/degradáveis.
- lexical funciona sem IA/vetor.
- provider externo é adapter, nunca owner.
- nenhum runtime antes de T097.

T059 — PARIDADE FINAL
Classes: PRIMEIRO_RUNTIME | POSTERIOR | POSTERGADO | COMPAT_CUTOVER | DESCARTADO.
“Primeiro runtime” = primeira onda de vertical slices, não big-bang.

Infra própria aprovada: somente Search Retrieval Projection reconstruível `post|item`.
Analytics/queue/vector/semantic/rerank/agentes continuam postergados.

Dados que não podem ser perdidos:
- WP/Elementor/taxonomias editoriais;
- oito valores GRE;
- review/include_ai/notas/revisor/histórico KB2Ops válidos;
- classificações KB2Ops realmente usadas;
- Search Knowledge ASI manual real;
- Golden ASI útil.

Blockers por capacidade:
- B-001 Search/RAG/embedding.
- B-002 profiling/cutover classificatório.
- B-003 retirada plugins/aliases.
- B-004 Analytics/query logging.
- B-005 deep-link item.
- B-006 write composto.
- B-007 durable queue/async.
Blocker contextual não vira NO-GO global.

T090 — REVISÃO WORDPRESS
Arquivo: revisao-wordpress-t090.md.
Status: PASS, zero finding bloqueante.

CONFIRMADO
- Metadata para Summary/Review.
- Taxonomy/Metadata para Classificação.
- Search Knowledge/Golden WordPress-first.
- Site Health para health checks.
- admin-post baseline; AJAX somente live UX; REST sem consumidor negado.
- WP-Cron trigger, não queue.
- WordPress HTTP API para provider externo quando aplicável.
- Search Retrieval Projection própria permanece justificada.

SIMPLIFICAR
1. Não manter bounded review history + meta revisions concorrentes sem requisito real.
2. Não criar tabela/admin CRUD próprio para Search Knowledge/Golden antes de esgotar WP_Post interno + metadata/revisions.

INVESTIGAR
1. versão mínima WordPress se depender de `revisions_enabled` (Core 6.4+).
2. taxonomias sistêmicas começam sem archive/rewrite público salvo jornada real.
3. provider endpoint configurável vai para T092/SPEC IA por SSRF/host allowlist.

O QUE CONTINUA POSTERGADO
- Analytics detalhado/query logging.
- durable queue.
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
- não criar Golden dataset real.
- não implementar aliases/adapters.
- não integrar Foundry/chamar LLM.
- não criar chunks/embeddings/vector store.
- não criar agentes/tools.
- não iniciar SPEC-001.

PRÓXIMO PASSO EXATO — T091
Executar a Revisão do Crítico de Simplicidade.

T091 DEVE
1. reler Constituição, AGENTS, matriz-paridade-futura.md e revisao-wordpress-t090.md.
2. assumir que cada capacidade/camada é removível até provar valor.
3. tentar eliminar classes, stores, endpoints, telas, schedulers e abstrações futuras.
4. revisar se Summary/Review/Classificação podem nascer em slices ainda menores.
5. revisar se Search Knowledge mínimo pode ser adiado sem prejudicar Search lexical inicial.
6. revisar item layer/deep-link separadamente de post-level Search.
7. revisar Site Health checks para não criar painel técnico excessivo.
8. revisar compatibilidade/aliases e exigir consumidor real.
9. revisar IA P1/P2 e manter provider seam inexistente até caso real.
10. classificar findings como MANTER | SIMPLIFICAR | POSTERGAR | DESCARTAR | BLOQUEAR.
11. corrigir documentação se necessário.
12. não criar runtime.

CRITÉRIO PARA FECHAR T091
- cada família relevante passou pelo princípio de negação;
- toda complexidade restante tem benefício explícito;
- capacidades removíveis foram simplificadas/postergadas/descartadas;
- nenhum finding bloqueante ficou sem tratamento;
- T092 fica com entrada objetiva.

ORDEM RESTANTE
T091 -> T092 -> T093 -> T094 -> T095 -> T096 -> T097.

REGRA DE CONTINUIDADE
Repositório/Constituição/Manifesto/SPEC prevalecem sobre memória de chat.
```

## Estado resumido

- SPEC-000 ativa.
- HEAD antes de T090: `bc5594c48bdabd22f3575477509fb83f53ad2176`.
- T050–T059 + T090 concluídos documentalmente.
- Próximo: T091.
- Runtime novo: inexistente.
- SPEC-001: bloqueada até T097.

## Regra de atualização

Atualizar este arquivo ao concluir T091. Não acumular estados contraditórios.
