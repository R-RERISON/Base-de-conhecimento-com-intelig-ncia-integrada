# Continuidade — SPEC-006 Premium Product Foundation

## Repositório / branch

- repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`;
- branch ativa: `spec006-premium-product-foundation`;
- commit de referência: `4de05a0ea200419ed7dc890585326296435f53c4`;
- versão source: `0.6.0-dev`.

## Estado comprovado

- SPEC-005: CLOSED/main.
- P-600: PASS.
- P-610: PASS / dívida WPCS histórica inventariada.
- P-620: PASS de inventory/disposition; package production ainda não clean.
- P-630: PASS completo.
- P-640: ACTIVE; P640-01 baseline/contract concluído.
- P-650/P-660/P-670: pendentes.

## P-630 fechado

Implementado e comprovado:

- `Knowledge_Facts_Store` como owner canônico;
- affected_service em `_bdc_es_affected_service`, com `_kb2ops_service` apenas como fallback read-only;
- systems_involved em `_bdc_es_systems_involved`;
- technologies/keywords/versions adotam chaves históricas sem dependência runtime KB2Ops;
- `Helpful_Tips_Store` read/write canônico;
- `Coverage_Read_Model` com oito campos;
- Public Article Reader consome owner canônico;
- Knowledge Workspace possui writer governado.

Homologação ambiental:

- WordPress 6.9.4;
- PHP 8.5.10;
- 606 posts;
- 20 amostras;
- store errors = 0;
- noop writers PASS;
- domain hash preservado;
- public reader alignment PASS;
- cutover não autorizado;
- retirement não autorizado.

Evidências:

- `evidence/spec006-p630-local-domain-closure-pass-20260929.json`;
- `evidence/spec006-p630-environmental-package-20260929.json`;
- `evidence/spec006-p630-domain-closure-pass-20260929.json`;
- `specs/006-premium-product-foundation/p630-closeout-20260929.md`.

Master Ledger disposition:

- GRE-001 -> PARITY_VERIFIED;
- GRE-004 -> PARITY_VERIFIED;
- KB2-005 -> SUPERSEDED_WITH_EVIDENCE;
- GRE-003/GRE-005/KB2-002/KB2-006 permanecem abertas.

## P-640 ativo

Contrato criado:

- `specs/006-premium-product-foundation/p640-modular-runtime-contract-v1.md`.

Baseline:

- `base-conhecimento-inteligencia-integrada.php` ainda concentra flags, requires e registrations de produto + engenharia;
- `class-plugin.php` já é pequeno e deve permanecer como orquestrador de hooks permanentes;
- não existe `Runtime_Module_Registry`.

Princípio de negação aplicado:

- sem DI container;
- sem framework externo;
- sem filesystem discovery;
- sem service locator;
- sem banco/options para feature flags;
- sem Composer obrigatório em runtime.

## Próximo passo exato

Implementar P640-02:

1. criar `includes/class-runtime-module-registry.php`;
2. modelar apenas os módulos de produto atualmente necessários;
3. preservar exatamente as flags e comportamento existentes;
4. não mover runners de engenharia ainda além do mínimo necessário;
5. criar teste estático/unitário do registry;
6. executar PHP lint e WPCS nos arquivos afetados;
7. só então seguir para P640-03.

## Critério de conclusão do próximo passo

P640-02 termina quando:

- registry existe e é pequeno/coeso;
- nenhuma feature habilitada muda de estado;
- módulos desabilitados continuam sem carregar;
- bootstrap ainda funciona;
- nenhum dado, ranking, conteúdo editorial ou endpoint muda;
- lint/WPCS/teste do slice passam.

## Invariantes

- sem cutover;
- sem retirement;
- sem bulk migration;
- sem legacy delete;
- sem alteração de Search ranking;
- sem escrita em `post_content`;
- sem escrita em `_elementor_data`;
- sem mudança de versão pública.

## Instrução para novo chat

Antes de modificar runtime, reler:

1. `AGENTS.md`;
2. `.specify/memory/constitution.md`;
3. `docs/PREMIUM-PLUGIN-PRODUCT-STANDARD.md`;
4. `docs/DEFINITION-OF-DONE.md`;
5. `specs/006-premium-product-foundation/spec.md`;
6. `specs/006-premium-product-foundation/tasks.md`;
7. `specs/006-premium-product-foundation/p640-modular-runtime-contract-v1.md`;
8. `specs/MASTER-FUNCTIONAL-PARITY-LEDGER.md`.

Confirmar o HEAD da branch no GitHub antes de escrever.


## Atualização P-640 — 2026-09-29

Implementação concluída até P640-05:

- `Runtime_Module_Registry` criado;
- Search, Public Experience Preview e Word Cloud roteados pelo registry;
- `Engineering_Module_Loader` criado;
- runners/smokes/profilers removidos do bootstrap;
- `Core_Runtime_Loader` criado;
- bootstrap reduzido para 89 linhas e quatro requires diretos;
- flags de Search/Public Preview/Word Cloud/H-030 preservadas;
- nenhum runner removido do source/package ainda;
- nenhuma mudança em dados, ranking, conteúdo editorial, endpoint, cutover ou retirement.

Testes adicionados:

- `tests/unit/spec006-p640-runtime-module-registry.php`;
- `tests/unit/spec006-p640-modular-runtime.php`.

Validação disponível nesta sessão:

- PHP lint + smoke independente do Runtime_Module_Registry: PASS;
- inspeção estrutural do bootstrap/ownership: PASS;
- WPCS/PHPUnit completos: NOT_RUN nesta sessão por ausência local das dependências e ausência de workflow ativo no branch.

Próximo passo exato:

P640-06 — executar static contracts, full plugin PHP lint, WPCS nos arquivos alterados e PHPUnit foundation. Somente após PASS avançar para P640-07 e considerar atualização de PROD-005.
