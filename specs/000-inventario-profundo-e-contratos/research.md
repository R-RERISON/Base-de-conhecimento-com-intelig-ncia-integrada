# Pesquisa Consolidada — SPEC-000

## Estado
T050–T059 consolidaram arquitetura; T090 validou WordPress-first; T091 reduziu complexidade; T092 endureceu segurança; T093 fechou a estratégia de evidência por slice.

## T093 — QA/Regressão
Artefato: `revisao-qa-t093.md`.

Princípios confirmados pelo agente QA:
- teste protege comportamento, não implementação histórica;
- mudança de ranking exige Golden;
- UI crítica exige browser/manual quando necessário;
- PASS vazio não é PASS;
- não testado permanece não testado;
- release não afirma certeza sem evidência;
- bug reproduzido ganha regressão quando viável.

## Estados de evidência
PASS, FAIL, NOT_RUN, NOT_CONFIGURED, STALE, N/A, POSTERGADO, WAIVED.

Regras:
- gate ativo NOT_RUN/NOT_CONFIGURED/STALE = NO-GO;
- N/A sem justificativa = NO-GO documental;
- feature POSTERGADA implementada sem reabertura = NO-GO arquitetural;
- waiver permanece visível e não vira PASS.

## Candidato SPEC-001
`Core mínimo + Summary narrativo` continua pequeno e testável.

Matriz mínima:
- G-001: zero write editorial;
- G-020: CRUD/partial/empty/read-after-write/B-006;
- G-070: capability, nonce, IDOR, mass assignment, XSS;
- G-110: browser/a11y básica/feedback;
- G-130: activation/deactivation/uninstall/package quando aplicável.

## Search
Primeiro release Search exige G-010/G-050/G-060/Golden/G-070/G-120/G-130 e B-001. Golden não se aplica ao Summary isolado.

## Capacidade postergada
Analytics, queue, item/deep-link, IA/vector/agentes não exigem suites executáveis antes de existirem; seus gates permanecem documentados e tornam-se MUST/CONDICIONAL quando ativados.

## Defect taxonomy
- P0: perda/corrupção, bypass de autorização, write editorial indevido, secret leak grave -> NO-GO.
- P1: fluxo principal, Golden blocking, XSS/IDOR relevante, rollback crítico ausente -> NO-GO.
- P2: secundário com workaround seguro -> decisão explícita.
- P3: cosmético/documental -> backlog explícito.

## Próximo passo
T094 deve validar a ordem de produto e se Summary isolado entrega valor suficiente para ser realmente a SPEC-001 recomendada.