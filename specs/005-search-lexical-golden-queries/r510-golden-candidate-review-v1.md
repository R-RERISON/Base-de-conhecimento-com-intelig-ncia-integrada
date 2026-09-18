# R-510 — Golden Candidate Review v1

**Status:** HUMAN REVIEW PENDING  
**Source:** ASI 4.5.0 last-run metadata + T510 environmental discovery  
**Candidate count:** 6

| ID | Query | Expected post | Max rank | Legacy severity | Query class | New severity |
|---|---|---:|---:|---|---|---|
| GQ-LEGACY-001 | pendrive | 527 | 3 | warning | simple term | pending |
| GQ-LEGACY-002 | MSTeams | 579 | 3 | warning | product/brand token | pending |
| GQ-LEGACY-003 | Windows 11 | 583 | 3 | warning | compound/version | pending |
| GQ-LEGACY-004 | Termo de assinatura | 45855 | 3 | warning | phrase | pending |
| GQ-LEGACY-005 | Estrutura | 36620 | 3 | warning | generic/ambiguous term | pending |
| GQ-LEGACY-006 | SCCM | 412 | 3 | warning | acronym | pending |

## Provenance

Todas as seis:
- estavam ativas no ASI;
- foram cadastradas com source `manual`;
- têm expected post existente;
- têm post type `post`;
- estão publicadas;
- não têm item-level expectation;
- não possuem review flag estrutural no T510.

O último run legado registrou 6/6 PASS, zero blocking_failed e zero warning_failed.

## Decisão pendente T513

Para cada linha, o humano deve confirmar:
1. a consulta continua válida;
2. o expected post continua sendo a resposta oficial esperada;
3. `max_rank=3` continua adequado;
4. nova severity = `blocking` ou `warning`.

### Proposta de governança, ainda não aceita

Por serem seed de paridade com um ASI funcional já validado, é razoável considerar as seis como candidatas a `blocking` no novo engine. A mudança de severity **não será aplicada sem aceite humano explícito**.

## Gap de diversidade T514

O seed já cobre:
- termo simples;
- brand/token;
- versão composta;
- frase;
- termo genérico;
- sigla.

Ainda não há evidência suficiente para:
- pergunta em linguagem natural;
- erro de digitação;
- alias/sinônimo;
- consulta cuja resposta dependa principalmente de Summary;
- consulta representativa de gap Elementor.

T514 deve adicionar casos somente com expected post revisado por humano.
