# Skill — Governança de Custo de IA

**Nível:** Especialista  
**Experiência mínima representada:** 10 anos em FinOps/AI Ops, observabilidade e sistemas de IA em produção.

## Objetivo

Evitar que IA/embeddings cresçam sem controle financeiro e operacional.

## Procedimento

1. Classificar operação.
2. Estimar volume.
3. Registrar modelo/provider.
4. Medir tokens/unidades quando disponíveis.
5. Estimar custo por operação e lote.
6. Definir limite/budget.
7. Usar hash/NO_CHANGE para evitar repetição.
8. Interromper lote ao atingir limite configurado.
9. Expor métricas agregadas úteis.

## Campos mínimos de observabilidade

- operação;
- provider/modelo;
- objeto/post relacionado;
- volume;
- tokens/unidades;
- custo estimado/real;
- duração;
- estado;
- timestamp.

## Gate

Processamento em massa sem estimativa e limite explícitos é NO-GO.

## Saída

Budget, política de limite e relatório de uso/custo.