# R-510 — Golden Candidate Automated Review v2

**Estado:** T513/T514 PASS LOCAL / execução ambiental pendente; R-510 OPEN.

O processo de revisão manual generalizada foi **superseded** pelo contrato:
`r510-automated-golden-validation-contract-v1.md`.

## Regra atual

Os seis candidates já possuem origem manual/histórica governada. Portanto o sistema deve validar automaticamente tudo que for objetivo.

### AUTO_PASS
Fecha a revisão objetiva do candidate quando:
- expected existe e está publicado;
- Content Extractor funciona;
- expected está dentro de max_rank;
- expected é Top-1;
- query possui 100% de cobertura semântica;
- não há concorrente material à frente;
- existe sinal suficiente.

AUTO_PASS preserva expected/max_rank e recomenda `blocking`.

### REVIEW_REQUIRED
Somente este estado pede decisão humana. O relatório já entrega expected, concorrente, ranks, sinais e reason codes.

### AUTO_FAIL
Quebra objetiva; não pedir “aprovação” humana para mascarar falha.

## T514

O Diversity Validator classifica automaticamente:
- termo simples;
- product token;
- composto/versão;
- frase;
- sigla;
- linguagem natural;
- Summary dependent;
- Elementor/mixed semantic gap.

Typo/variation e alias/synonym só contam como uso real com provenance real/curada.

Synthetic robustness testa lowercase/uppercase/whitespace e permanece rotulado `origin=synthetic`.

## Build de homologação

`0.5.0-r510-t513.1`  
SHA-256 `e5c10eba2f831584536e0f3e0c2ab50102c87c504424117530c1eafd64cbe0bc`.

Validação local: 9/9 unit, 43/43 PHP lint pré/pós ZIP, 42/42 active requires, Git parity 5/5, deterministic rebuild PASS, zero dependência técnica ASI.

## Próximo passo

Executar `Base de Conhecimento -> Golden Auto Validator`.

Não abrir artigos manualmente antes do relatório. Se o relatório retornar `REVIEW_REQUIRED`, revisar exclusivamente esses casos.
