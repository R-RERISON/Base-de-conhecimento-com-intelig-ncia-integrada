# R-510 — Golden Candidate Automated Review v3

**Estado:** T513.1 ambiental analisado; T514.2 PASS LOCAL / ambiental pendente; R-510 OPEN.

## Evidência T513.1

Build `0.5.0-r510-t513.1`.

Resultado ambiental:
- 5 AUTO_PASS;
- 1 REVIEW_REQUIRED;
- 0 AUTO_FAIL;
- synthetic robustness 16/16 PASS;
- safety/fingerprint PASS;
- corpus 623 -> 623;
- errors=[].

Única ambiguidade:
- query `Estrutura`;
- expected 36620 rank 2;
- concorrente 516 rank 1;
- ambos validation_score=100.

O relatório também revelou D-513-01 e D-513-02 no validator. Portanto não se fecha R-510 usando a classificação T513.1.

## Política v2

ADR-005-002 elimina decisão manual obrigatória sem evidência suficiente.

Estados:
- `AUTO_PASS`: ativo/blocking;
- `AMBIGUOUS_QUARANTINED`: preservado, warning, fora do blocking;
- `AUTO_FAIL`: NO-GO.

O sistema nunca troca expected automaticamente.

Para `Estrutura`, a expectativa é que v2 preserve 36620 em quarantine se a ambiguidade continuar. Isso não é presumido como resultado ambiental antes da execução.

## T514

Três estratos:
1. Golden Relevance: origem humana/histórica;
2. Technical Challenge: corpus-derived/synthetic para natural language, Summary e Elementor gap;
3. Real-world enrichment: typo/alias reais via Telemetria futura.

`PENDING_TELEMETRY` não é equivalente a dado real inventado.

## Build atual

`0.5.0-r510-t514.2`  
SHA-256 `9fb9e20828b1e5162db5fa924ed3b2a3b76d85531d1c61f2847205ebdd00ae2c`.

Local:
- 14/14 unit PASS;
- 44/44 PHP lint;
- 43/43 active requires;
- Git parity 7/7;
- deterministic rebuild PASS;
- ASI dependency hits 0;
- writer/network hits 0.

## Próximo passo

Executar somente T514.2 e fornecer o JSON. Se `r510_ready=true`, seguir para T515/T516.
