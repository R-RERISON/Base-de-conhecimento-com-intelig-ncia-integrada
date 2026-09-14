# Skill — Search e Golden Queries

**Nível:** Especialista / Principal  
**Experiência mínima representada:** 15 anos em Information Retrieval e avaliação de relevância.

## Objetivo

Proteger a qualidade da busca contra regressões de ranking e evolução lexical/semântica.

## Procedimento

1. Selecionar consultas reais representativas.
2. Definir expectativa por consulta:
   - post esperado;
   - posição mínima/máxima;
   - item/trecho esperado quando aplicável;
   - severidade bloqueante ou warning.
3. Versionar conjunto e hash.
4. Executar explicitamente; dashboard não roda suíte silenciosamente.
5. Registrar algoritmo/configuração/modelo.
6. Comparar antes/depois.
7. Bloquear release em falha bloqueante vigente.

## Regras

- suíte vazia = `não configurada`, nunca PASS;
- erro técnico != zero result;
- semantic/hybrid só entra em produção se melhora ou preserva conjunto aprovado;
- consultas difíceis devem incluir linguagem natural, siglas, erros e variações.

## Saída

Suite de Golden Queries reproduzível e relatório de qualidade de ranking.