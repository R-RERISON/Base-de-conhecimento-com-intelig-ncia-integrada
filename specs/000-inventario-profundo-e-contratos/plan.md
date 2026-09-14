# Plano — SPEC-000 Inventário Profundo e Contratos

## Objetivo

Ler, decompor, cruzar e revisar os projetos de referência até que a primeira SPEC de runtime possa ser escrita sem adivinhação estrutural nem complexidade antecipada.

## Estado

- [x] inventários KB2Ops/ASI/GRE;
- [x] T050–T059 consolidação arquitetural;
- [x] T090 revisão WordPress-first;
- [x] T091 revisão de simplicidade;
- [ ] T092 segurança;
- [ ] T093 QA/regressão;
- [ ] T094 produto/conhecimento;
- [ ] T095 unknowns/blockers por slice;
- [ ] T096 relatório final;
- [ ] T097 GO/NO-GO SPEC-001.

## Arquitetura consolidada

`matriz-paridade-futura.md` permanece a visão arquitetural T059. As revisões T090/T091 são overlays obrigatórios de execução.

T090 confirmou WordPress-first e manteve apenas uma exceção persistente própria: Search Retrieval Projection reconstruível.

T091 reduziu a estratégia de implementação:

- primeira SPEC não é plataforma completa;
- Core nasce apenas na medida necessária ao primeiro fluxo;
- Summary narrativo é o candidato mais simples ao primeiro vertical slice;
- Review/Classificação entram em slices separados;
- Content Extractor nasce junto do primeiro consumidor real;
- Search customizada é posterior;
- post-level Search precede item/deep-link quando suficiente;
- Search Knowledge só nasce ao corrigir necessidade observada;
- Golden permanece gate de Search, sem obrigar CRUD visual sofisticado inicialmente;
- IA/provider/RAG/vector/agentes permanecem ausentes até casos reais.

## Complexidades descartadas no baseline por T091

- event bus próprio;
- repository layer genérico sobre APIs WordPress;
- service container/DI genérico;
- cache service genérico;
- Operations Center genérico;
- migration orchestrator genérico;
- provider factory multi-vendor sem segundo caso;
- adapter framework de compatibilidade;
- SPA/REST sem consumidor.

## Regra de vertical slice

Cada futura SPEC deve começar pela menor jornada completa e homologável. Infraestrutura compartilhada só entra quando o próprio slice a consome.

Exemplo candidato para SPEC-001, sujeito a T094/T095/T097:

`abrir tela Summary -> ler objective/escalation/important -> editar -> validar capability/nonce -> persistir -> read-after-write -> feedback -> rollback conhecido`.

## Próximo passo — T092

Executar revisão de Segurança sobre T059 + T090 + T091.

Foco:

1. capability model;
2. nonce/CSRF/método HTTP;
3. sanitização/escaping;
4. IDOR/scope;
5. taxonomias internas;
6. shortcodes/aliases;
7. SSRF/provider endpoint/secrets;
8. data egress e prompt injection futuro;
9. Search scope/detail fail-closed;
10. ações destrutivas/migração/purge.

## Gate

Nenhuma SPEC de runtime começa antes de T097. Nenhuma simplificação T091 remove garantias de segurança, integridade, regressão ou rollback.