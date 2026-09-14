# Pesquisa Consolidada — SPEC-000

## Estado consolidado
T050–T059 fecharam a arquitetura. T090–T093 validaram WordPress-first, simplicidade, segurança e QA. T094 validou valor e ordem de produto.

## T094 — Produto/Conhecimento
Artefato: `revisao-produto-t094.md`.

### Primeiro slice
`Core mínimo + Summary narrativo` permanece o candidato de SPEC-001.

Usuário primário: Analista de Conhecimento.

Valor: registrar e manter `objective`, `escalation`, `important` como conhecimento sistêmico estruturado, sem reescrever o artigo.

### Limites
- não incluir os cinco campos classificatórios históricos no owner Summary;
- não prometer benefício de Search ao Resolvedor ainda;
- não implementar AI READY antes dos pré-requisitos históricos completos;
- não criar cockpit/dashboard/portal completo no primeiro slice.

### Ordem de produto recomendada
1. Summary narrativo.
2. Classificação mínima, começando por `knowledge_type` salvo nova evidência.
3. Review mínimo.
4. Search post-level com Content Extractor e Golden mínimo.
5. capacidades adicionais por evidência.

### Search
Search é reconhecida como capacidade estratégica de maior valor direto ao Resolvedor, mas não deve furar os gates B-001/Golden/security/benchmark. Post-level precede item/deep-link quando suficiente.

### Analytics
Não é necessário para provar valor do primeiro slice. Homologação humana + integridade do estado + segurança são evidência suficiente. B-004 continua fechado para Analytics detalhado.

### IA
IA P1 só entra após jornada manual estável e esforço real observado. RAG/vector/agentes continuam postergados.

## Próximo passo
T095 deve separar definitivamente blockers da SPEC-001 candidata dos blockers de slices futuros.