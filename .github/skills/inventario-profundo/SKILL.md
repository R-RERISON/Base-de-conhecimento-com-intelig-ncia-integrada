# Skill — Inventário Profundo

**Nível:** Especialista  
**Experiência mínima representada:** 15 anos em análise de sistemas legados, engenharia reversa e modernização sem regressão.

## Quando usar

Antes de reescrever, integrar, migrar ou desativar um sistema existente.

## Objetivo

Transformar código legado em um mapa de comportamentos, dados, contratos e riscos.

## Procedimento

1. Fixar versão/SHA baseline.
2. Ler árvore completa.
3. Identificar bootstrap/lifecycle.
4. Catalogar persistência.
5. Catalogar hooks/rotas/actions/filters.
6. Reconstruir jornadas de usuário.
7. Mapear segurança e privacidade.
8. Mapear testes/gates/build.
9. Relacionar cada componente ao comportamento protegido.
10. Classificar:
   - MANTER;
   - REDESENHAR;
   - SUBSTITUIR POR WORDPRESS;
   - EVOLUIR COM IA/VETOR;
   - DESCARTAR;
   - AINDA NÃO SABEMOS.

## Regra de qualidade

README não é evidência suficiente. Contratos documentados devem ser verificados contra runtime.

## Saída

Inventário rastreável por arquivo/contrato, gaps, drifts e matriz de paridade futura.