# G-590 — Evidence Contract Addendum v1.3

**Status:** FROZEN PARA RE-HOMOLOGAÇÃO  
**Data:** 2026-09-24  
**Origem:** evidência ambiental RC4 `bdc-kb-spec005-g590-section-20260924-142106.json`

## Escopo

Este addendum corrige exclusivamente o **runner/validator de evidência ambiental**.

Não altera:
- Content Extractor;
- Numbered Hierarchy Resolver;
- Search Section Projector;
- Search Section Ranker;
- Search Section Service;
- Anchor Manager;
- parent ranker;
- Search Projection schema;
- Golden post-level.

## Gap 1 — hierarchy coverage

### Evidência RC4

O RC4 reportou:
- `numbered_non_heading_nodes=9639`;
- `numbered_with_heading_context=3585`;
- `numbered_without_heading_context=6054`.

O gate interpretou qualquer node não-heading retornado por `Numbered_Hierarchy_Resolver` como sinal hierárquico forte.

Essa interpretação é excessiva.

O resolver também retorna nodes de `hierarchy_source=explicit_dom`, incluindo list items cuja hierarquia já é autoritativamente representada pelo DOM. Esses nodes são observabilidade estrutural, não evidência automática de heading perdido.

### Regra v1.3

Os contadores legados `numbered_*` permanecem como telemetria.

O blocker de navegabilidade passa a usar somente:

```text
hierarchy_source == numbering_inferred
AND hierarchy depth > 1
AND fragment kind != heading
```

Campos canônicos:
- `strong_numbered_non_heading_nodes`;
- `strong_numbered_with_heading_context`;
- `strong_numbered_without_heading_context`;
- `strong_numbered_samples`;
- `hierarchy_node_source_counts`.

T590-15 falha somente quando:
- corpus não foi integralmente analisado;
- extractor possui erros;
- ou `strong_numbered_without_heading_context > 0`.

Se houver strong gaps, amostras concretas são obrigatórias antes de decidir reabertura de SPEC-004.

## Gap 2 — source-kind probe coverage

### Evidência RC4

O corpus possui anchors generated em:
- Elementor;
- Gutenberg;
- legacy HTML.

O runner formulou probes Elementor/legacy, mas não conseguiu formular Gutenberg segundo v1.2.

Isso prova uma limitação do **probe selector**, não automaticamente do runtime Search.

### Regra v1.3

Estratégias em ordem:

1. `unique_title`;
2. `title_plus_rare_section_token`;
3. `repeated_title_runtime_probe`.

A terceira estratégia é somente fallback de evidência.

Ela **não auto-aprova** o probe.

O mesmo runtime Search precisa retornar a `section_key` esperada e o mesmo conjunto de checks permanece obrigatório:
- expected Section encontrada;
- deep-link materializado;
- texto visível preservado.

Se o repeated title não recuperar a Section esperada, T590-16 falha normalmente.

Novos diagnósticos:
- `probe_candidate_source_kinds`;
- `probe_strategy_counts`.

## Compatibilidade

Este addendum supersede somente:
- a interpretação de `numbered_without_heading_context` como blocker universal;
- o descarte automático de repeated-title candidates sem rare token.

Todo o restante dos contratos G-590 v1/v1.1/v1.2 permanece válido.

## Critério RC5

RC5 pode fechar T590-15/T590-16 somente se:
- `strong_numbered_without_heading_context=0`;
- `unprobed_source_kinds=[]`;
- `section_query_failed=0`;
- `deep_link_failed=0`;
- `visible_text_changed=0`;
- demais gates fechados continuarem PASS.

Se qualquer strong hierarchy gap permanecer, G-590 continua OPEN e a evidência deve fornecer amostras para decisão de reabertura da SPEC-004.
