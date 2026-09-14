# SPEC-005 — Search Lexical e Golden Queries

**Status:** Planejada  
**Pré-requisito:** SPEC-004 concluída.

## Problema
A nova plataforma precisa recuperar conhecimento corretamente sem depender de IA ou vetores.

## Resultado esperado
Query normalization, retrieval lexical, ranking mínimo, resultado operacional e Golden Queries bloqueantes.

## WordPress-first
Comparar WP_Query e recursos nativos com necessidade real de FULLTEXT/tabela derivada antes de criar schema próprio.

## Princípio de negação
Não recriar toda a arquitetura ASI se um engine menor atingir as consultas reais do baseline.

## Gate
Conjunto de consultas representativas → ranking esperado → fonte oficial → Golden Queries versionadas e reproduzíveis.
