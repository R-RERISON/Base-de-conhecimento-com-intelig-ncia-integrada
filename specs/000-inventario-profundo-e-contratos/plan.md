# Plano — SPEC-000 Inventário Profundo e Contratos

## Objetivo

Ler, decompor, cruzar e revisar os projetos de referência até que a primeira SPEC de runtime possa ser escrita sem adivinhação estrutural, complexidade antecipada ou lacuna de segurança conhecida.

## Estado

- [x] inventários KB2Ops/ASI/GRE;
- [x] T050–T059 consolidação arquitetural;
- [x] T090 revisão WordPress-first;
- [x] T091 revisão de simplicidade;
- [x] T092 revisão de segurança;
- [ ] T093 QA/regressão;
- [ ] T094 produto/conhecimento;
- [ ] T095 unknowns/blockers por slice;
- [ ] T096 relatório final;
- [ ] T097 GO/NO-GO SPEC-001.

## Arquitetura consolidada

`matriz-paridade-futura.md` permanece a visão arquitetural T059. T090–T092 são overlays obrigatórios para execução.

T090 confirmou WordPress-first; T091 reduziu a execução a vertical slices mínimos; T092 tornou segurança fail-closed e contextual por superfície.

## Candidato de primeiro slice

Continua provisoriamente:

`Core mínimo + Summary narrativo (objective/escalation/important)`

Fluxo:

`abrir tela -> validar objeto/capability -> ler meta -> editar -> POST + nonce -> allowlist/validar/sanitizar -> persistir -> read-after-write -> escapar saída -> feedback`

Esse slice não precisa de tabela, REST, AJAX, Search, extractor, taxonomy, Analytics, queue, Foundry ou IA.

## Regras de segurança T092

- capability é verificada no handler e no objeto;
- nonce protege CSRF, não autorização;
- mutação via GET é NO-GO;
- IDs e estados enviados pelo cliente são não confiáveis;
- mass assignment é NO-GO;
- escaping é contextual e tardio;
- projection/cache/vector nunca autorizam acesso;
- HTTP externo variável exige política SSRF/allowlist e API segura;
- secrets nunca entram em logs/exports/repositório/prompt;
- Analytics/identidade/query logging continuam negados sem B-004;
- activation/uninstall não fazem limpeza destrutiva por default.

## Próximo passo — T093

QA/Regressão deve:

1. revisar `catalogo-testes-regressao.md` contra T090–T092;
2. definir evidência mínima do candidato Summary;
3. mapear testes unitários, integração WordPress, browser/manual, package e rollback;
4. converter os NO-GO de segurança em testes negativos;
5. preservar Golden como gate somente quando Search existir;
6. impedir PASS vazio, `NOT_TESTED` disfarçado ou evidência stale;
7. não criar runtime.

## Gate

Nenhuma SPEC de runtime começa antes de T097. T092 não autorizou código; apenas tornou explícitas condições de segurança para cada futura capacidade.