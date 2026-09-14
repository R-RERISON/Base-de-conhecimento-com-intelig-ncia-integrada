# Riscos, Drifts e Dívidas — SPEC-000

> Estado após T094.

## Riscos anteriores
D-001–D-008 e B-001–B-007 permanecem contextuais. T090–T093 adicionaram guardrails de plataforma, simplicidade, segurança e evidência.

## Novos riscos T094

### X-073 — primeiro slice sem usuário primário
**Risco:** entregar infraestrutura travestida de produto.  
**Tratamento:** SPEC-001 candidata possui usuário explícito: Analista de Conhecimento.

### X-074 — prometer valor ao Resolvedor antes de Search
**Risco:** percepção de produto incompleto ou requisito inflado no Summary.  
**Tratamento:** Summary é jornada de curadoria; Search é entrega posterior específica ao Resolvedor.

### X-075 — owner Summary absorver classificação por herança GRE
**Risco:** reintroduzir duplicidade D-006/B-002.  
**Tratamento:** SPEC-001 limita-se a objective/escalation/important.

### X-076 — AI READY adaptado ao runtime incompleto
**Risco:** alterar regra de confiança de 8/8 para 3/3 apenas por conveniência.  
**Tratamento:** AI READY postergado até owners/pré-requisitos completos; regra histórica não é reduzida silenciosamente.

### X-077 — dashboard antes da pergunta
**Risco:** Cockpit/analytics sem decisão de gestão ou dados maduros.  
**Tratamento:** dashboard só nasce com pergunta/usuário/dado comprovados.

### X-078 — Search atrasada indefinidamente
**Risco:** plataforma concentra valor administrativo e demora a entregar descoberta ao Resolvedor.  
**Tratamento:** após Summary + classificação mínima + Review mínimo, Search post-level é prioridade estratégica; T095/T097 podem antecipar se houver evidência forte sem violar gates.

### X-079 — paridade histórica confundida com jornada
**Risco:** clonar oito campos na mesma tela, menus, shortcodes e layouts por familiaridade.  
**Tratamento:** preservar comportamento/dado necessário; UI e composição seguem jornada futura.

### X-080 — IA antes de esforço comprovado
**Risco:** custo/complexidade para automatizar fluxo ainda instável.  
**Tratamento:** jornada manual primeiro; IA P1 apenas se reduzir esforço observado.

## Estado do candidato SPEC-001
Produto considera `Core mínimo + Summary narrativo` válido e pequeno. Nenhum novo blocker global foi encontrado.

## Próximo passo
T095 deve decidir objetivamente quais blockers/unknowns afetam essa SPEC candidata e quais pertencem a Search/Classificação/Review/Analytics/IA futuros.