# Inventário de Telas v0 — UX-001

## Objetivo

Mapear as superfícies de produto para impedir crescimento acidental da interface e orientar mockups priorizados.

## Grupo A — existentes / contrato real

### A01 — Knowledge List

Estado: existente.  
Função: listar artigos e abrir contexto de trabalho.  
Prioridade mockup: ALTA.

### A02 — Knowledge Workspace / artigo

Estado: existe como página administrativa com Summary + Classificação.  
Função: editar domínios estruturados associados ao artigo sem alterar conteúdo editorial.  
Prioridade mockup: CRÍTICA.

### A03 — Summary

Estado: implementado/homologado.  
Campos: objetivo, escalonamento, importante.  
Prioridade mockup: CRÍTICA como parte do Workspace.

### A04 — Classificação

Estado: implementado/homologado.  
Conceitos: audiência, equipe responsável, tipo de conhecimento, item de catálogo.  
Prioridade mockup: CRÍTICA como parte do Workspace.

### A05 — Gestão de Vocabulários

Estado: UI nativa WordPress.  
Prioridade mockup: MÉDIA; primeiro avaliar se a UI nativa é suficiente antes de criar substituta.

## Grupo B — próxima SPEC / antecipação visual controlada

### B01 — Review & Governance

Estado: futura SPEC-003.  
Mockup permitido: estrutura de experiência, estados visuais hipotéticos claramente marcados.  
Mockup proibido: congelar schema, transições ou permissões sem SPEC.

### B02 — Fila de revisão

Estado: hipótese de produto a validar.  
Só entra no baseline se a SPEC-003 comprovar necessidade.

### B03 — Histórico / atividade

Estado: arquitetura futura.  
Pode aparecer como placeholder para validar navegação e densidade.

## Grupo C — roadmap futuro

### C01 — Search
### C02 — Search Results / filtros
### C03 — Content Extractor
### C04 — Telemetria / Inteligência de Busca
### C05 — Operações / Indexação
### C06 — Semantic Search / Vetores
### C07 — IA / Foundry / RAG

Estas telas não recebem mockup de alta fidelidade nesta fase. UX-001 deve apenas garantir espaço arquitetural para que não exijam outro produto/shell no futuro.

## Estados transversais obrigatórios

Toda tela relevante deve prever:

- default;
- empty;
- loading;
- success;
- validation error;
- system error;
- forbidden/no permission;
- read-only;
- stale/conflict quando aplicável;
- largura <=782px.

## Prioridade de prototipação

1. Knowledge Workspace master — desktop.
2. Knowledge Workspace — 782px/estreito.
3. Knowledge List — desktop e estreito.
4. Summary + Classification integrados ao Workspace.
5. Review & Governance conceitual.
6. Component states / empty / error / permission.
7. Vocabulários, somente se a UI nativa mostrar inadequação concreta.

## Regra de cobertura

Uma tela só entra como “canônica” quando sua jornada, ações, estado vazio, erro, responsividade e relação com permissões estiverem documentados. Screenshot bonito isolado não fecha UX.
