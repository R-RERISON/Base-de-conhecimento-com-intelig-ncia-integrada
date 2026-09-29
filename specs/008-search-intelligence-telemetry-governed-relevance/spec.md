# SPEC-008 — Search Intelligence, Telemetry, Privacy & Governed Relevance

**Status:** PLANEJADA  
**Pré-requisito:** Search lexical confiável + jornada pública suficiente para gerar fatos reais.

## Problema

Sem outcomes não sabemos se Search resolve. Sem governança, relevância mutável vira tuning opaco.

## Escopo

### Telemetry
- Search Events;
- Interactions;
- Outcomes;
- live-typing journey collapse;
- zero-result;
- no-engagement;
- latency/state/source.

### Privacy
- data classification;
- minimal/pseudonymous/audit apenas se justificados;
- retention;
- access control;
- privacy policy text;
- raw query não persistida por default.

### Governed relevance
- vocabulary;
- aliases;
- bindings;
- relevance rules;
- diagnostics;
- deterministic suggestions;
- simulation;
- expected-state/HMAC Apply;
- audit e reindex/cache invalidation.

### Intelligence
- frequent;
- emerging;
- zero-result;
- unresolved journeys;
- Word Cloud consumindo sinais canônicos sem segundo pipeline lexical.

## Fora

Vetores, LLM reranking e IA autônoma.

## Gates

SI-800 Privacy Contract  
SI-810 Event Model  
SI-820 Interaction Integrity  
SI-830 Outcomes  
SI-840 Search Intelligence  
SI-850 Vocabulary/Bindings  
SI-860 Rules/Simulation/Apply  
SI-870 Word Cloud Convergence  
SI-880 Performance/Retention  
SI-890 Parity Acceptance
