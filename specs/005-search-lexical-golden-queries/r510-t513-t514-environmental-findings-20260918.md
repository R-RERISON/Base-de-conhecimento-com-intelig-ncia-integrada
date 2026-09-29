# R-510 — Findings ambientais T513/T514 — 2026-09-18 22:36:54 UTC

**Build:** `0.5.0-r510-t513.1`  
**Evidence:** `evidence/r510-t513-t514-environmental-20260918T223654Z.json`  
**SHA-256 do arquivo recebido:** `9faf205aed1c4024e2105128728444c7735bb12e301b80a643311c7735afd908`

## Resultado válido

Ambiente:
- WordPress 6.9.4;
- PHP 8.5.10;
- multisite=false.

Safety:
- read-only=true;
- schema/write/persist/network/global search hooks=false;
- fingerprint before/after igual;
- corpus 623 -> 623;
- changed_posts=0;
- errors=[].

T513:
- AUTO_PASS=5;
- REVIEW_REQUIRED=1;
- AUTO_FAIL=0;
- status=`PASS_WITH_REVIEW_ITEMS`.

O único caso ambíguo é `GQ-LEGACY-005 / Estrutura`:
- expected 36620 = rank 2;
- concorrente 516 = rank 1;
- ambos validation_score=100;
- ambos title/semantic/native coverage=100;
- motivos: EXPECTED_NOT_TOP1 + STRONG_COMPETITOR_AHEAD + SINGLE_TOKEN_AMBIGUITY.

Isso comprova ambiguidade objetiva. Não existe base para substituir 36620 por 516 automaticamente.

T514:
- simple_term=true;
- compound_or_version=true;
- phrase=true;
- acronym=true;
- natural_language=false;
- summary_dependent=false;
- elementor_semantic_gap=false;
- real_typo_or_variation=false;
- real_alias_or_synonym=false;
- status=`INCOMPLETE`.

Synthetic robustness:
- 16 casos;
- 16 PASS;
- 0 FAIL.

`r510_ready=false` permanece correto.

## Defeitos encontrados no validator

### D-513-01 — product_token overmatch

A evidência classifica `GQ-LEGACY-005 / Estrutura` também como `product_token`.

Causa: regra mixed-case considera qualquer palavra iniciada em maiúscula + minúsculas como product token.

Correção requerida:
- exigir sinal de marca/token mais forte;
- no mínimo >=2 letras maiúsculas + >=1 minúscula para o padrão atual;
- adicionar regressão: `MSTeams => product_token`; `Estrutura => NOT product_token`.

### D-513-02 — exact phrase sem fronteira lexical

Para query `Estrutura`, o post 36485 recebe `exact_title_phrase=true` com título contendo `Infraestrutura`.

Causa: `str_contains(normalized_title, normalized_query)`.

Correção requerida:
- exact phrase deve operar sobre sequência de tokens/fronteiras lexicais;
- `estrutura` não pode casar `infraestrutura`;
- adicionar unit test explícito.

## Decisão de governança proposta

Não forçar revisão humana para resolver ambiguidade que o sistema não consegue distinguir.

Novo estado:
- `AUTO_PASS`: aceito para Golden blocking;
- `AUTO_FAIL`: bloqueia;
- `AMBIGUOUS_QUARANTINED`: preserva candidate/provenance, mas exclui do conjunto blocking até existir evidência futura suficiente.

`Estrutura` deve ser elegível à quarentena automática, não à troca de expected.

Isso preserva autoridade humana sem obrigar homologador a abrir artigos para decidir uma consulta estruturalmente ambígua.

## T514 — separação necessária

Há uma circularidade no contrato atual: exigir consultas reais de linguagem natural/typo/alias antes da futura camada de Telemetria.

A próxima revisão separará:
1. **Golden Relevance Set** — expectativas humanas/históricas validadas;
2. **Technical Challenge Set** — casos corpus-derived/synthetic para natural language, Summary e Elementor gaps;
3. **Real-world query diversity** — enriquecimento posterior pela futura Telemetria, sem bloquear a criação da engine lexical inicial.

Challenge cases nunca serão representados como consultas reais.
