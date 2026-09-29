# ADR-005-001 — Zero Runtime Dependency on ASI

**Status:** ACCEPTED  
**Data:** 2026-09-18  
**Escopo:** SPEC-005 e evoluções Search/Vector/IA.

## Contexto

O Advanced Search Intelligence (ASI) funciona bem e é uma referência funcional importante, mas será removido do ambiente após a conclusão da nova plataforma.

Portanto, o novo plugin não pode depender de:
- plugin ASI ativo;
- classes/funções ASI;
- tabelas `asi_*`;
- options `asi4_*`;
- hooks/actions/filters ASI;
- cron/queue ASI;
- ranking ASI;
- índice ASI;
- embeddings/vector stores ASI;
- telemetria ASI.

## Decisão

**Zero dependência runtime.**

ASI é permitido somente como:
1. fonte histórica durante discovery/migração controlada;
2. benchmark funcional;
3. origem de fixtures/evidências versionadas no repositório.

Depois que um artefato histórico é capturado e versionado, runners posteriores devem consumir a cópia governada pelo novo projeto, nunca o storage ASI.

## Ownership da nova solução

Quando necessários e aprovados por gate, todos os componentes serão próprios do novo plugin:

- Query Normalizer;
- Search Document;
- índice/projection lexical;
- tabelas Search;
- Golden Query repository;
- ranker;
- ranking rules;
- cache;
- queue/job infrastructure;
- telemetry;
- embeddings;
- vector index/store;
- hybrid retrieval;
- reranking;
- AI/RAG integration.

Os nomes/schema e lifecycle pertencem ao namespace BDC, não ao ASI.

## Persistência

"WordPress-first" não significa "sem tabelas próprias".

Significa:
- WordPress continua fonte editorial e autoridade de acesso;
- usar APIs Core quando elas resolvem corretamente o problema;
- criar schema próprio quando retrieval/performance/governança exigirem;
- qualquer tabela própria deve ser mínima, versionada, rebuildable quando derivada, e possuir migration/rollback/lifecycle.

## Search lexical

R-500 já provou gap de cobertura e ranking. Portanto uma Search Retrieval Projection própria é tecnicamente justificável.

G-520 decidirá o schema exato, mas qualquer tabela aprovada deverá usar namespace do novo plugin, por exemplo `{prefix}bdc_kb_*` ou outro prefixo canônico definido pelo projeto — nunca `asi_*`.

## Golden Queries

As 6 Golden recuperadas no T510 foram copiadas para:
`fixtures/golden-candidates-legacy-v1.json`.

A partir desse ponto:
- são dados do projeto;
- ASI não é mais necessário para executar T511+;
- provenance `legacy_asi` permanece somente como metadado histórico.

## Embeddings e vetores

Fora da SPEC-005, mas a mesma regra já fica estabelecida:

- nenhuma dependência de index/vector store do ASI;
- embeddings serão gerados pela nova arquitetura;
- armazenamento será decidido pela SPEC futura responsável;
- lifecycle e rebuild serão próprios;
- remoção física do ASI não pode afetar retrieval lexical, semantic, hybrid ou IA da nova solução.

## Gate de independência

Antes do RC final da Search haverá **G-585 — ASI Independence / Decommission Readiness**:

1. ASI desativado em homologação;
2. busca nova funcional;
3. Golden Suite PASS;
4. static scan do runtime produtivo sem referência a `asi_*`, `asi4_*` ou classes/functions ASI;
5. nenhuma query SQL em tabela ASI;
6. nenhum hook/option ASI;
7. rebuild do índice próprio funciona;
8. rollback do novo plugin não depende do ASI.

A remoção física das tabelas ASI é operação separada e somente ocorrerá após autorização explícita.

## Consequência

Qualquer build que consulte ASI após o estágio de discovery é inválido para evolução da solução.

O build `0.5.0-r510-t511.1` fica **SUPERSEDED / NÃO INSTALAR** porque seu baseline runner lia `asi_golden_queries` em runtime.

O T511 será republicado consumindo exclusivamente a Golden Candidate Fixture própria.
