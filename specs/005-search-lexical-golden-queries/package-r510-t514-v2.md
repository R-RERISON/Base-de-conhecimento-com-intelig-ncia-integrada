# Package — R-510 T513/T514 v2

**Build:** `0.5.0-r510-t514.2`  
**SHA-256:** `9fb9e20828b1e5162db5fa924ed3b2a3b76d85531d1c61f2847205ebdd00ae2c`

## Alterações sobre T513.1

- corrige `product_token` overmatch: `MSTeams` continua válido; `Estrutura` deixa de ser classificado como produto;
- corrige exact phrase por fronteira lexical: `estrutura` não casa mais `infraestrutura`;
- substitui REVIEW_REQUIRED obrigatório por `AMBIGUOUS_QUARANTINED` fail-safe;
- adiciona Technical Challenge Discovery read-only;
- separa Golden real/histórica, Technical Challenge e real-world enrichment;
- typo/alias reais permanecem `PENDING_TELEMETRY`.

## Validação local

- unit tests: **14/14 PASS**;
- PHP lint pré/pós ZIP: **44/44 PASS**;
- active requires: **43/43**;
- missing requires: **0**;
- Git blob parity: **7/7**;
- deterministic rebuild: **PASS**;
- ASI forbidden technical identifiers: **0**;
- legacy diagnostic files no ZIP: **0**;
- forbidden write/network calls nos runners: **0**;
- Elementor writer: **OFF**.

## Homologação

Instalar sobre T513.1.

Executar:

`Base de Conhecimento -> Golden Auto Validator -> Executar validação automática completa e baixar JSON`.

Resultado esperado estruturalmente, sem presumir dados ambientais:
- 5 AUTO_PASS são esperados pela evidência anterior;
- `Estrutura` deve tender a `AMBIGUOUS_QUARANTINED`, não a troca automática;
- Challenge Discovery tentará produzir casos para natural_language, Summary e Elementor gap;
- `r510_ready` só será true se safety, T513, challenge discovery, diversity e synthetic robustness passarem.

Nenhum PASS ambiental desta versão é presumido.
