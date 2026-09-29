# SPEC-010 — Semantic Search & Vectors

**Status:** PLANEJADA / EVIDENCE-GATED

## Entrada

- lexical/Golden estáveis;
- telemetry suficiente;
- corpus de gaps reais;
- target de melhoria definido antes da implementação.

## Escopo possível

- chunk contract;
- embeddings;
- vector storage;
- capability detection;
- semantic retrieval;
- hybrid fusion;
- fallback lexical;
- benchmark;
- drift/re-embedding;
- cost/latency;
- privacy/data egress.

## WordPress moderno

Antes de escolher tecnologia:
- reavaliar AI/embedding primitives disponíveis na versão WordPress corrente;
- compatibilidade com WordPress mínimo;
- capability real de MariaDB/vector;
- serviço externo apenas com benefício comprovado.

## Gate de negação

Se semantic/hybrid não superar baseline lexical nos gaps pré-definidos sem regressão relevante, não entra.

## Gates

SEM-1000 Gap Corpus  
SEM-1010 Chunk Contract  
SEM-1020 Embedding Provider  
SEM-1030 Store Benchmark  
SEM-1040 Semantic Retrieval  
SEM-1050 Hybrid  
SEM-1060 Golden Delta  
SEM-1070 Cost/Latency  
SEM-1080 Failure/Fallback  
SEM-1090 Go/No-Go
