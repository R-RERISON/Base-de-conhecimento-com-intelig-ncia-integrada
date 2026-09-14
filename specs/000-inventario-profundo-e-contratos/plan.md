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

Extrair:

- tabelas;
- índices;
- post meta;
- options;
- transients;
- taxonomias;
- cron state;
- caches;
- dados de browser/local state quando houver.

### Fase C — Integrações WordPress

Extrair:

- `add_action`;
- `add_filter`;
- `add_shortcode`;
- `admin_post_*`;
- `wp_ajax_*`;
- `register_rest_route`;
- `register_post_meta`;
- `register_taxonomy`;
- activation/deactivation/uninstall;
- capabilities e roles.

### Fase D — Fluxos de produto

Reconstruir jornadas:

- gestor;
- analista de conhecimento;
- resolvedor;
- administrador;
- visitante/autenticado quando aplicável.

### Fase E — Regressão

Mapear:

- testes automatizados;
- Golden Queries;
- smoke tests;
- browser acceptance;
- condições NO-GO;
- gaps sem teste.

### Fase F — Decisão

Aplicar para cada item:

`MANTER | REDESENHAR | SUBSTITUIR POR WORDPRESS | EVOLUIR COM IA/VETOR | DESCARTAR | AINDA NÃO SABEMOS`.

### Fase G — Cruzamento

Depois dos inventários individuais:

1. definir ownership lógico dos dados;
2. identificar duplicação versus complementaridade;
3. consolidar persistência e integrações por conceito futuro;
4. fechar drifts/compatibilidade;
5. aplicar WordPress-first;
6. justificar infraestrutura própria mínima;
7. congelar regressões/gates;
8. priorizar IA/vetor;
9. fechar paridade futura.

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

- T052 ownership: concluída.
- T053 sobreposição: concluída.
- Próximo bloco: T050/T051 — consolidar persistência e integrações usando os dois artefatos anteriores.

## Gate de conclusão

Nenhuma SPEC de runtime começa enquanto existir um item crítico classificado como “AINDA NÃO SABEMOS” sem decisão explícita de postergar e risco documentado.
