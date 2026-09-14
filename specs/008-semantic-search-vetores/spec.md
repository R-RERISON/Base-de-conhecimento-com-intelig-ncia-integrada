# SPEC-008 — Semantic Search e Vetores

**Status:** Planejada  
**Pré-requisito:** SPEC-007 concluída.

## Problema
Busca lexical tem limites para linguagem natural, sinônimos implícitos e consultas sem sobreposição textual.

## Resultado esperado
Chunks, embeddings, capability detection, vector store, semantic retrieval e hybrid ranker com fallback lexical.

## WordPress-first
Semantic/vector é infraestrutura complementar; plugin deve continuar funcional sem suporte vetorial.

## Princípio de negação
Vetores só entram se um conjunto de consultas reais demonstrar lacuna lexical relevante.

## Gate
Baseline lexical → semantic → híbrido → Golden Queries; melhoria comprovada sem regressão bloqueante, latência/custo dentro do budget e fallback lexical aprovado.
