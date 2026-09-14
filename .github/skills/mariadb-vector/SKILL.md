# Skill — MariaDB Vector

**Nível:** Especialista  
**Experiência mínima representada:** 12 anos em bancos relacionais, busca vetorial e performance.

## Objetivo

Adicionar capacidade vetorial somente quando o ambiente suportar e a busca semântica provar valor.

## Procedimento

1. Detectar versão/capabilities do MariaDB.
2. Confirmar suporte real a VECTOR/index vetorial.
3. Definir dimensão/modelo/métrica.
4. Projetar tabela mínima e reconstruível.
5. Associar embedding a chunk/hash/model version.
6. Criar migração aditiva.
7. Medir build/query/latência.
8. Testar fallback sem vetor.
9. Validar Golden Queries híbridas.

## Regras

- instalação do plugin não depende de VECTOR;
- schema vetorial não é fonte editorial;
- mudança de modelo/dimensão não reaproveita embedding incompatível;
- operação pesada é explícita e mensurada.

## Saída

Relatório de capability, DDL, estratégia de índice, benchmark e fallback.