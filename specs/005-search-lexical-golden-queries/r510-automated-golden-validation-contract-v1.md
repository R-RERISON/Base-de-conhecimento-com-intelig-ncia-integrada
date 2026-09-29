# R-510 — Automated Golden Validation Contract v1.1

**Status:** ACTIVE  
**Aplica-se a:** T513/T514  
**ADR complementar:** ADR-005-002  
**Princípio:** automatizar toda validação objetiva sem transformar algoritmo em autoridade de intenção.

## 1. Fronteira de autoridade

O validator não cria nova verdade Golden.

Pode:
- confirmar expectativa humana/histórica já governada;
- detectar quebra objetiva;
- detectar ambiguidade;
- quarentenar ambiguidade;
- derivar Technical Challenge cases explicitamente não-reais.

Não pode:
- inventar Golden real;
- trocar `expected_post_id`;
- transformar concorrente em expected;
- aceitar ambiguidade como verdade;
- rotular caso corpus-derived/synthetic como consulta real;
- usar validation score como Search ranker.

## 2. Estados T513

### AUTO_PASS

Expectativa existente permanece válida para o blocking set quando:
- expected existe/publicado;
- Content Extractor não falha;
- rank > 0 e <= max_rank;
- semantic coverage = 100%;
- não há concorrente material à frente;
- sinais não são inconclusivos.

Não é obrigatório ser Top-1 quando o contrato já permite `max_rank > 1`; Top-1 só é material quando um concorrente à frente cria ambiguidade.

Resultado:
- `active_for_blocking=true`;
- severity recomendada `blocking`.

### AMBIGUOUS_QUARANTINED

Fail-safe para evidência inconclusiva/ambígua.

Gatilhos incluem:
- concorrente material à frente;
- single-token ambiguity;
- cobertura semântica incompleta;
- ausência de sinais diretos suficientes.

Resultado:
- query/expected/provenance preservados;
- expected não é substituído;
- `active_for_blocking=false`;
- severity recomendada `warning`;
- não exige decisão manual para o gate bootstrap.

Quarentena não resolve intenção; adia reativação até existir evidência de Telemetria/curadoria.

### AUTO_FAIL

Quebra objetiva:
- expected ausente;
- expected não publicado;
- extração falhou;
- expected não recuperado;
- expected fora de max_rank.

AUTO_FAIL é NO-GO.

## 3. Matching auxiliar

### exact phrase

Deve respeitar fronteiras lexicais.

Exemplo:
- `estrutura` casa `estrutura ctc`;
- `estrutura` NÃO casa `infraestrutura`.

### product token

Capitalização inicial comum não basta.

O classificador deve distinguir:
- `MSTeams` -> product_token;
- `Estrutura` -> não product_token.

## 4. Validation score

Heurística exclusiva de detecção de ambiguidade:
- semantic coverage: 45%;
- title coverage: 30%;
- Summary coverage: 15%;
- native coverage: 10%;
- exact title phrase: bônus 15.

Não é Ranking v1 e não pode ser promovido para G-530.

## 5. T514 — três estratos

### Golden Relevance Set

Origem humana/curada/histórica.

Cobertura mínima bootstrap:
- simple term;
- product token;
- compound/version;
- phrase;
- acronym.

### Technical Challenge Set

Origem `corpus_derived_challenge|synthetic`.

Objetivo: provar capacidade técnica, não intenção de usuário.

Cobertura mínima:
- natural language;
- Summary-dependent;
- Elementor/Content Extractor semantic gap.

Discovery:
- read-only;
- publish corpus;
- Summary/Elementor gap exige token ausente do native;
- preferência por token único no corpus derivado;
- natural language prefere título publicado já em formato natural;
- fallback sintético permitido, rotulado como `synthetic`.

### Real-world Query Enrichment

- typo/variation real;
- alias/synonym real.

Enquanto Telemetria ainda não existe:
- estado permitido: `PENDING_TELEMETRY`;
- não bloqueia bootstrap lexical;
- não pode ser fabricado para fechar T514.

## 6. Synthetic robustness

Pode derivar uppercase/lowercase/whitespace e transformações futuras aprovadas.

Sempre:
- `origin=synthetic`;
- separado da Golden real;
- não comprova comportamento real de usuário.

## 7. Gate

T513 PASS quando:
- safety PASS;
- AUTO_FAIL=0.

Quarentena é permitida e explicitada.

T514 PASS quando:
- Golden core coberta;
- Technical Challenge coberta;
- synthetic robustness PASS.

`PENDING_TELEMETRY` para typo/alias reais é compatível com PASS T514 nesta fase.

R-510 só fica ready quando T513/T514 + safety estiverem PASS. T515/T516 ainda são necessários para congelar version/hash e fechar R-510.

## 8. Segurança

Runner:
- read-only;
- zero schema;
- zero persistência;
- zero query logging;
- zero network;
- zero mudança global de Search hooks;
- fingerprint editorial before/after;
- independente do ASI.

## 9. Evidência T513.1

O build `0.5.0-r510-t513.1` encontrou:
- 5 AUTO_PASS;
- 1 REVIEW_REQUIRED;
- 0 AUTO_FAIL;
- 16/16 synthetic robustness PASS.

Também revelou D-513-01 e D-513-02, corrigidos em T514.2. Portanto T513.1 permanece evidência histórica válida, mas sua classificação T514 foi superseded para fechamento do gate.
