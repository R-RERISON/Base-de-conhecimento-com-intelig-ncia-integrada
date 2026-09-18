# T100E — Engineering Consolidation Contract v1

**Status:** PLANNED / READ-ONLY-FIRST  
**ADR base:** ADR-004-001 + ADR-004-002  
**Trigger:** T100D persistent migration PASS

## Objetivo

Consolidar a engenharia acumulada em G-245 antes de ampliar funcionalidades de IA, migração em escala ou retirada de Elementor.

O objetivo não é adicionar features. É reduzir complexidade acidental, separar produto de homologação e tornar o runtime mais previsível, testável e reproduzível.

## Princípios

1. **Produto e laboratório são coisas diferentes.**
2. Gates T09x/T100x de engenharia não devem vazar para a UX final.
3. Contratos aprovados permanecem estáveis enquanto implementação interna pode ser consolidada.
4. Nenhuma consolidação pode alterar comportamento editorial sem gate próprio.
5. Primeiro inventário e testes; depois remoção/refatoração.
6. Nenhum batch writer será introduzido.
7. Writes continuam post-scoped.
8. Core Blocks continuam destino canônico.
9. Elementor continua preservado até dependência residual zero.
10. PR #4 permanece DRAFT durante a consolidação.

## Escopo

### E1 — Runtime Inventory

Inventariar:
- classes carregadas incondicionalmente;
- classes carregadas somente por flags;
- telas/smokes de homologação;
- contratos históricos Elementor;
- classes duplicadas entre Elementor e Core Blocks;
- writers ativos/inativos;
- hooks admin;
- assets;
- dependências entre classes;
- flags de build.

Saída: mapa de runtime + classificação `product`, `engineering`, `legacy_compat`, `test_only`.

### E2 — Regression Runner

Criar um único runner reutilizável capaz de validar:
- PHP lint;
- requires;
- flags proibidas;
- writers inesperados;
- network calls;
- shortcode execution;
- block rendering;
- UX baseline hashes;
- Post Workspace route/post_id;
- Core Blocks contracts;
- journal/lock/stale/auth invariants.

Sem write ambiental.

### E3 — Product vs Engineering Boundary

Mover progressivamente artefatos de smoke/diagnóstico para camada de engenharia sem remover contratos ainda usados por evidência.

Nenhuma tela técnica é removida até:
- função equivalente existir no runner;
- paridade comprovada;
- rollback simples;
- zero dependência funcional.

### E4 — Build / Release Reproducibility

Formalizar:
- versão;
- ZIP reproduzível;
- checksum;
- manifest;
- relatório de validação;
- upgrade path;
- downgrade/rollback de plugin;
- instalação sobre versão anterior.

### E5 — Defensive Services Consolidation

Avaliar consolidação de:
- journal;
- lock;
- stale guard;
- authorization identity;
- snapshot/hash;
- verification;
- rollback.

Objetivo: remover duplicação entre famílias históricas sem mudar semântica.

### E6 — Workspace Regression Matrix

Cobrir pelo menos:
- Gutenberg nativo;
- legacy_html;
- plain_text;
- Elementor;
- mixed;
- post com journal terminal;
- post sem journal;
- post locked;
- source drift;
- review_required.

### E7 — Production Readiness Exit

T100E só fecha quando:
- runner único PASS;
- inventário completo;
- runtime product/engineering classificado;
- build reproduzível;
- upgrade/downgrade testados;
- nenhuma regressão UX;
- nenhuma mudança editorial;
- dívida técnica residual explicitamente listada.

## Proibições neste gate

- não ampliar migração para múltiplos posts;
- não habilitar IA;
- não remover Elementor;
- não alterar conteúdo editorial;
- não promover PR #4;
- não apagar evidências históricas;
- não refatorar sem teste de equivalência.

## Próximo gate depois de T100E

Somente após consolidação:
- T101: dependência residual Elementor / estratégia de retirada; ou
- T102: atividade Inteligência/IA post-scoped.

A ordem será decidida com base no inventário T100E.
