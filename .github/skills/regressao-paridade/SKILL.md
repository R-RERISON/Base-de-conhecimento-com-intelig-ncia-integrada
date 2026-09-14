# Skill — Regressão e Paridade

**Nível:** Especialista  
**Experiência mínima representada:** 15 anos em QA, modernização e proteção de sistemas legados.

## Objetivo

Converter funcionalidades comprovadas dos projetos de referência em contratos de comportamento independentes da implementação antiga.

## Procedimento

1. Descrever comportamento atual observável.
2. Identificar entradas, saídas, erros e efeitos colaterais.
3. Localizar teste existente ou criar caso de reprodução.
4. Definir expectativa do novo produto.
5. Marcar como:
   - paridade obrigatória;
   - evolução deliberada;
   - comportamento descartado.
6. Executar antigo e novo sobre fixture equivalente quando possível.
7. Registrar diferença e justificativa.

## Gate

Nenhuma função crítica dos legados é aposentada sem item correspondente na matriz de paridade.

## Saída

Matriz `comportamento → baseline → expectativa nova → teste → resultado`.