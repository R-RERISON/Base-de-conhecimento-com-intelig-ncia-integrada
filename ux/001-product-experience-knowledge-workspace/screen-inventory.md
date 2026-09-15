# Inventário de Telas v1 — UX-001

**Status:** FECHADO para Gate UX-001  
**Baseline funcional:** `0.2.0-rc.1`

## Objetivo

Mapear superfícies reais, referências históricas e superfícies futuras para impedir crescimento acidental da interface e orientar mockups priorizados.

## Família A — Curadoria / contrato real

### A01 — Knowledge List

Estado: existente.  
Função: listar artigos e abrir contexto de trabalho.  
Evolução UX: incorporar busca/filtros/status apenas quando suportados por contrato; referência forte K02 do benchmark KB2Ops.  
Prioridade mockup: ALTA.

### A02 — Knowledge Workspace / artigo

Estado: existe como página administrativa com Summary + Classificação.  
Função: trabalhar domínios estruturados associados ao artigo sem alterar o conteúdo editorial.  
Evolução UX: substituir a página incremental por workspace com cabeçalho contextual + navegação interna.  
Referência forte: K03 do benchmark KB2Ops.  
Prioridade mockup: CRÍTICA.

### A03 — Summary

Estado: implementado/homologado na SPEC-001.  
Campos canônicos: objetivo, escalonamento, importante.  
Prioridade mockup: CRÍTICA como domínio do Workspace.

### A04 — Classificação

Estado: implementado/homologado na SPEC-002.  
Conceitos canônicos: audiência, equipe responsável, tipo de conhecimento, item de catálogo.  
Prioridade mockup: CRÍTICA como domínio do Workspace.  
Referência K08 deve ser adaptada ao contrato atual, sem reintroduzir campos históricos.

### A05 — Gestão de Vocabulários

Estado: UI nativa WordPress.  
Escopo: quatro taxonomias canônicas.  
Decisão v1: permanecer nativa até evidência concreta de inadequação.  
Prioridade mockup: BAIXA/MÉDIA.

## Família B — Governança / antecipação visual controlada

### B01 — Review & Governance

Estado: futura SPEC-003.  
Mockup permitido: estrutura de experiência e encaixe no Workspace.  
Mockup proibido: congelar estados, schema, transições, SLA ou permissões sem SPEC.

### B02 — Fila de revisão

Estado: hipótese com suporte visual histórico no KB2Ops, ainda sem contrato novo.  
Decisão: reservar arquitetura; não promover a superfície a runtime antes da SPEC-003.

### B03 — Histórico / atividade

Estado: arquitetura futura.  
Pode aparecer como tab/placeholder para validar navegação e densidade, sem definir persistência.

## Família C — Resolvedor / Search futuro

O benchmark KB2Ops mostrou uma experiência de resolvedor visualmente distinta da curadoria. Essa separação é adotada como princípio arquitetural futuro, não como autorização de implementação.

### C01 — Search Home

Referência: K05/K12.  
Função futura: entrada por problema/pergunta, atalhos e descoberta.

### C02 — Search Results

Referência: K06.  
Função futura: query persistente, filtros, relevância e lista de resultados.

### C03 — Knowledge Result / artigo operacional

Referência: K07.  
Função futura: resolução rápida, decisão, escalonamento e detalhes progressivos.

### C04 — Favoritos / Histórico de busca

Observado como padrão no benchmark, mas não autorizado no roadmap funcional atual.  
Estado: NÃO PLANEJADO até caso de uso.

## Família D — Operações / Inteligência futura

### D01 — Dashboard / Visão Geral

Referência: K01.  
Decisão: somente quando existirem métricas canônicas e perguntas operacionais explícitas. Dashboard ornamental é proibido.

### D02 — Relatórios e Métricas

Referência: K09.  
Dependência: futura Telemetria/Inteligência de Busca.

### D03 — Configurações

Referência: K10.  
Decisão: apenas settings com contrato e owner real; não criar central de toggles especulativa.

### D04 — Migração / Limpeza

Referência: K11.  
Decisão: DESCARTADA como superfície permanente. Pode existir apenas como lifecycle/setup temporário quando necessário.

## Família E — Roadmap técnico sem tela canônica nesta fase

- Content Extractor;
- Operações/Indexação;
- Semantic Search/Vetores;
- IA/Foundry/RAG.

Esses domínios devem caber na arquitetura, mas não recebem tela final de alta fidelidade sem SPEC funcional.

## Estados transversais obrigatórios

Toda superfície relevante deve prever:

- default;
- empty;
- loading;
- success;
- validation error;
- system error;
- forbidden/no permission;
- read-only;
- stale/conflict quando aplicável;
- desktop largo;
- largura administrativa <=782px;
- foco/teclado.

## Jornadas v1

### J01 — Curador

`Knowledge List -> artigo -> Knowledge Workspace -> Summary/Classificação -> salvar -> feedback -> permanecer no contexto`

### J02 — Gestor de vocabulário

`Workspace/Classificação -> gerenciar vocabulário -> UI nativa de taxonomia -> voltar ao artigo`

### J03 — Governança futura

`Fila/Lista -> Workspace -> Review & Governance -> decisão -> feedback -> histórico`

A transição só vira contrato na SPEC-003.

### J04 — Resolvedor futuro

`Search -> Results -> Knowledge Result -> resolução rápida / detalhe / escalonamento`

## Prioridade de prototipação

1. Knowledge Workspace master — desktop.
2. Knowledge List — desktop.
3. Summary + Classificação integrados ao Workspace.
4. Workspace 782px/estreito.
5. Review & Governance conceitual.
6. Component states / empty / error / permission.
7. Search/Resolvedor apenas como arquitetura nesta wave.
8. Vocabulários somente se UI nativa falhar em teste concreto.

## Evidência histórica

Referência visual catalogada em `evidence-kb2ops-visual-benchmark.md` com 12 superfícies observadas e matriz PRESERVAR/EVOLUIR/DESCARTAR.

## Gate

**UX-001 — PASS.**

O inventário atual, a referência KB2Ops e as quatro jornadas principais são suficientes para avançar para Design System + Master Mockups sem depender de memória ou de uma tela futura não contratada.