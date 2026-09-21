# G-590 — Cross-SPEC Regression Contract

**Status:** NORMATIVO

## SPEC-001 Summary
Summary Store e meta keys permanecem inalterados. Search é read-only.

## SPEC-002 Classification
Taxonomias canônicas permanecem. Nenhuma classificação paralela.

## SPEC-003 Review
Comments/event log e governança permanecem intocados.

## SPEC-004 Content/KD
- Content Extractor output não muda neste gate;
- Knowledge Document schema 2.1.0 não muda;
- zero write em post_content/_elementor_data;
- Section Projection é consumidor derivado.

## SPEC-005 fechada
Devem permanecer:
- normalizer v1;
- lexical-ranker-v1.0.0;
- Golden post-level;
- lifecycle sem rebuild implícito;
- fallback WordPress degradado;
- permission/status revalidation;
- budgets G-570 sem regressão material.

## Testes mínimos

- Section Projector;
- Section Ranker;
- repository encode/decode;
- duplicate/unresolved;
- anchor idempotency;
- schema migration 1.0→1.1;
- lifecycle mismatch;
- Search post-level regression;
- Golden post-level;
- package parity;
- PHP lint;
- coverage ambiental;
- deep-link probes;
- fingerprint editorial.

Qualquer regressão não explicada em contrato fechado anterior = NO-GO e reabertura da SPEC afetada.
