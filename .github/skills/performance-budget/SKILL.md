# Skill — Performance Budget

**Nível:** Especialista  
**Experiência mínima representada:** 12 anos em performance, bancos e capacity planning.

## Objetivo

Definir limites mensuráveis para caminhos críticos antes de otimizações arbitrárias.

## Procedimento

1. Definir cenário e volume.
2. Medir baseline.
3. Definir budget de:
   - latência;
   - queries;
   - memória;
   - tamanho de resposta;
   - jobs/lote;
   - chamadas de IA.
4. Identificar gargalo real.
5. Otimizar a camada correta.
6. Repetir medição.
7. Registrar degradação aceitável/fallback.

## Regras

- não otimizar no escuro;
- não usar cache como substituto de query ruim sem entender invalidação;
- dashboards não fazem scans ilimitados;
- indexação em massa é batch/retomável quando necessário.

## Saída

Baseline, budget, benchmark e recomendação de capacidade.