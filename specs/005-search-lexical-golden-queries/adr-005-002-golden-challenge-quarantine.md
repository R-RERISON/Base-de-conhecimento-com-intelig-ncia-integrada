# ADR-005-002 — Golden Relevance, Technical Challenge e Ambiguity Quarantine

**Status:** ACCEPTED  
**Data:** 2026-09-18  
**Escopo:** SPEC-005 / R-510

## Contexto

A execução ambiental do build `0.5.0-r510-t513.1` confirmou 5 AUTO_PASS, 1 ambiguidade real (`Estrutura`) e zero AUTO_FAIL. Também mostrou que exigir ao homologador uma escolha semântica entre dois resultados com sinais equivalentes reintroduziria trabalho manual subjetivo no gate.

T514, por sua vez, exigia classes que a plataforma ainda não tem como provar como uso real antes da futura Telemetria: linguagem natural, typo/alias observados e gaps de projeção.

## Decisão

Separar três conjuntos.

### 1. Golden Relevance Set

Somente expectativas com origem humana/curada/histórica governada.

Estados:
- `AUTO_PASS`: ativo para blocking;
- `AMBIGUOUS_QUARANTINED`: preservado, não ativo para blocking;
- `AUTO_FAIL`: NO-GO.

O validator nunca cria nem troca `expected_post_id`.

### 2. Technical Challenge Set

Casos derivados deterministicamente do corpus, exclusivamente para provar capacidade técnica:
- natural language;
- Summary-dependent retrieval;
- Elementor/Content Extractor semantic gap;
- normalizer robustness.

Origem obrigatória: `corpus_derived_challenge` ou `synthetic`.

Esses casos **não são consultas reais de usuário** e nunca podem ser representados como tal.

### 3. Real-world Query Enrichment

Typo/variation, alias/synonym e demais padrões observacionais reais são enriquecimento da futura camada de Telemetria.

A ausência desses sinais antes da Telemetria não bloqueia a engine lexical bootstrap, desde que:
- Golden core esteja coberta;
- Technical Challenge esteja coberta;
- a lacuna real-world esteja explicitamente `PENDING_TELEMETRY`.

## Ambiguidade

Quando dois ou mais resultados possuem evidência material equivalente e a intenção não pode ser inferida objetivamente:
- não escolher vencedor;
- não alterar expected;
- não pedir homologação manual obrigatória;
- marcar `AMBIGUOUS_QUARANTINED`;
- manter provenance para revisão futura por telemetria/curadoria.

## Consequências

Benefícios:
- elimina validação manual mecânica;
- evita truth-by-ranking;
- evita obrigar humano a decidir sem evidência;
- preserva casos ambíguos sem bloquear indevidamente;
- permite desenvolver Search antes da Telemetria sem fabricar query logs.

Limites:
- Challenge Set não mede intenção de usuário;
- quarantine não resolve a intenção; apenas impede falsa certeza;
- telemetria futura deve enriquecer/reativar casos quando houver evidência real.

## Relação com ADR-005-001

Continua obrigatório zero runtime dependency on ASI. Golden/Challenge são recursos próprios da SPEC-005.
