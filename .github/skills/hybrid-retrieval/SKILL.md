# Skill — Hybrid Retrieval

**Nível:** Especialista  
**Experiência mínima representada:** 12 anos em busca, NLP, embeddings e sistemas RAG.

## Objetivo

Combinar busca lexical e semântica sem sacrificar explicabilidade, fallback e regressão.

## Procedimento

1. Medir baseline lexical.
2. Definir corpus canônico e chunking.
3. Executar semantic retrieval isolado.
4. Comparar cobertura/erros.
5. Escolher estratégia de fusão apenas após evidência:
   - weighted score;
   - Reciprocal Rank Fusion;
   - reranking limitado;
   - outra técnica justificada.
6. Normalizar scores de forma explícita.
7. Validar Golden Queries.
8. Definir fallback lexical.
9. Medir latência/custo.

## Regras

- não esconder falha lexical com LLM;
- não usar embedding de JSON Elementor bruto;
- não re-embedar sem mudança de hash/modelo;
- hybrid ranker precisa ser versionado.

## Saída

Contrato de retrieval híbrido, métricas antes/depois e decisão de produção.