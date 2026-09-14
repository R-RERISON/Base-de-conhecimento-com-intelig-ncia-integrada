# Plano — SPEC-000 Inventário Profundo e Contratos

## Objetivo

Ler, decompor e cruzar os três repositórios de referência em contratos verificáveis antes de qualquer runtime novo.

## Agentes convocados

- Orquestrador Principal;
- Arquiteto WordPress;
- Arquiteto de Conhecimento;
- Especialista em Busca e Retrieval;
- Especialista em MariaDB e Dados;
- Especialista em IA/Foundry;
- Especialista em Segurança WordPress;
- Especialista em UI/UX WordPress;
- Especialista em Qualidade e Regressão;
- Especialista em Performance/Observabilidade;
- Crítico de Simplicidade.

## Estratégia

### Fase A — Topologia

Para cada repositório:

1. árvore completa;
2. bootstrap;
3. namespaces/classes/funções;
4. assets/templates;
5. testes;
6. tools/build;
7. docs relevantes.

### Fase B — Persistência

Extrair tabelas, índices, post meta, options, transients, taxonomias, cron state e caches.

### Fase C — Integrações WordPress

Extrair hooks, shortcodes, admin-post, AJAX, REST, metadata/taxonomy registration, lifecycle e capabilities.

### Fase D — Fluxos de produto

Reconstruir jornadas de gestor, analista de conhecimento, resolvedor, administrador e público/autenticado.

### Fase E — Regressão

Mapear testes, Golden Queries, smoke/browser acceptance, NO-GO e gaps sem cobertura.

### Fase F — Decisão

Aplicar:

`MANTER | REDESENHAR | SUBSTITUIR POR WORDPRESS | EVOLUIR COM IA/VETOR | DESCARTAR | AINDA NÃO SABEMOS`.

### Fase G — Cruzamento

1. ownership lógico dos dados — T052 ✅;
2. duplicação versus complementaridade — T053 ✅;
3. persistência por conceito/owner — T050 ✅;
4. integrações por contrato futuro — T051 ✅;
5. drifts/compatibilidade/blockers — T054 próximo;
6. WordPress-first campo/capacidade — T056;
7. infraestrutura própria mínima — T057;
8. regressões/Golden/gates — T055;
9. IA/vetor — T058;
10. paridade futura final — T059.

## Artefatos de saída

- `inventario-kb2ops.md`;
- `inventario-asi.md`;
- `inventario-resumo-executivo.md`;
- `mapa-ownership-dados.md`;
- `matriz-sobreposicoes.md`;
- `catalogo-persistencia.md`;
- `catalogo-integracoes.md`;
- `catalogo-testes-regressao.md`;
- `matriz-paridade-futura.md`;
- `riscos-e-drifts.md`;
- ADRs necessárias.

## Estado atual da Fase G

T050–T053 estão concluídas documentalmente. O produto futuro já possui:

- owners lógicos definidos;
- sobreposições/fusões/separações explícitas;
- persistência classificada por canônico/projection/observacional/operacional/config/compat;
- regra de dual-write proibido e adapters temporários;
- contratos cross-module preliminares com write confirmado antes de evento;
- política inicial de server-rendered/admin-post, AJAX somente por live UX e REST somente com consumidor real.

### Próximo bloco

**T054 — contratos quebrados/drifts/compatibilidade/blockers.**

T054 deve impedir que “solução arquitetural futura” seja confundida com “compatibilidade já resolvida”. Para cada drift histórico será obrigatório decidir se há adapter/preflight, descarte deliberado ou blocker antes da SPEC-001.

## Gate de conclusão

Nenhuma SPEC de runtime começa enquanto existir item crítico “AINDA NÃO SABEMOS” sem decisão explícita de postergar, risco documentado e gate correspondente.