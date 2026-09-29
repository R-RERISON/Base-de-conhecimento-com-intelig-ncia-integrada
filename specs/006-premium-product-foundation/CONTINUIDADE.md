# Continuidade — SPEC-006 Premium Product Foundation

## Repositório / branch

- repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`;
- branch ativa: `spec006-premium-product-foundation`;
- versão source: `0.6.0-dev`;
- runtime P640 validado em: `de1a051a876e514c5545756c08bcb734b448335a`;
- o HEAD documental é posterior ao commit validado; confirmar o HEAD antes de escrever.

## Estado comprovado

- SPEC-005: CLOSED/main.
- P-600: PASS.
- P-610: PASS / legacy WPCS debt inventoried.
- P-620: PASS inventory/disposition; production package ainda não clean.
- P-630: PASS completo.
- P-640: ACTIVE.
  - P640-01: PASS.
  - P640-02: PASS — Runtime_Module_Registry.
  - P640-03: PASS — módulos de produto extraídos.
  - P640-04: PASS — Engineering_Module_Loader.
  - P640-05: PASS — Core_Runtime_Loader/composition root.
  - P640-06: REABERTO — executar localmente; evidência anterior invalidada.
  - P640-07: resultado ambiental funcional PASS recebido; fechamento bloqueado até package local conforme.
  - P640-08: pendente após evidência ambiental.
- P-650/P-660/P-670: pendentes.

## P-630 fechado

Owners canônicos:

- `Knowledge_Facts_Store`;
- affected_service: `_bdc_es_affected_service`;
- fallback read-only: `_kb2ops_service`;
- systems_involved: `_bdc_es_systems_involved`;
- technologies/keywords/versions: chaves históricas adotadas pelo BDC;
- `Helpful_Tips_Store`;
- `Coverage_Read_Model` oito campos.

Homologação P-630:

- WordPress 6.9.4;
- PHP 8.5.10;
- 606 posts;
- errors = 0;
- no-op writers PASS;
- public reader alignment PASS.

Ledger P-630:

- GRE-001 -> PARITY_VERIFIED;
- GRE-004 -> PARITY_VERIFIED;
- KB2-005 -> SUPERSEDED_WITH_EVIDENCE;
- GRE-003/GRE-005/KB2-002/KB2-006 permanecem abertas.

## P-640 implementação

Arquivos centrais:

- `includes/class-core-runtime-loader.php`;
- `includes/class-runtime-module-registry.php`;
- `includes/class-engineering-module-loader.php`;
- `includes/class-modular-runtime-runner-p640.php`.

Bootstrap:

- composition root reduzido;
- quatro requires diretos;
- Search/Public Preview/Word Cloud passam pelo registry;
- runners/smokes/profilers passam pelo Engineering_Module_Loader;
- Elementor adapter read-only preservado;
- Core Blocks activity preservado.

Princípio de negação preservado:

- sem DI container;
- sem framework externo;
- sem filesystem discovery;
- sem service locator;
- sem banco/options para estado de módulos;
- sem Composer obrigatório em runtime;
- sem endpoint novo.

## P640-06 — correção de governança

A execução anterior via workflow remoto foi invalidada. O padrão do projeto exige gates locais, reproduzíveis e com evidência.

Run PASS:

- a evidência anterior não pode fechar P640-06;
- source commit afetado: `de1a051a876e514c5545756c08bcb734b448335a`;
- é obrigatório refazer static contracts, lint, WPCS, PHPUnit e build localmente.

Resultados:

- static registry contract: 14/14 PASS;
- modular runtime contract: 15/15 PASS;
- PHP lint: 131/131 PASS;
- WPCS affected runtime: PASS;
- PHPUnit foundation: PASS;
- deterministic build: PASS;
- bootstrap direct requires: 4.

Evidência:

- `evidence/spec006-p640-local-validation-pass-20260929.json`.

## Pacote P640-07 — NÃO CONFORME PARA FECHAMENTO

- build: `0.6.0-dev-p640.1`;
- arquivo: `base-conhecimento-inteligencia-integrada-0.6.0-dev-p640.1.zip`;
- SHA-256: `e22e599ae8ae212c8e9c87a522e4f04a63fe187344798191c7f2fe8051f2ef9a`;
- manifest SHA-256: `3588e95412216dc4d24c741e8a3072a86d308e963bfe03955c6297c387798fa4`;
- production_package: false;
- proveniência: não conforme para gate; reconstruir localmente;
- runner P640 habilitado somente no artefato de homologação.

Instruções:

- `specs/006-premium-product-foundation/p640-environmental-acceptance-20260929.md`.

## Próximo passo exato

P640-06/P640-07:

1. refazer P640-06 localmente;
2. reconstruir o ZIP P640 localmente;
3. reconciliar/repetir a homologação ambiental sobre o package local;
4. somente então fechar P640-07.

Após receber o JSON:

1. validar `status=PASS` e assertions;
2. versionar a evidência ambiental;
3. fechar P640-07;
4. executar P640-08 package/runtime inventory;
5. atualizar Master Functional Parity Ledger somente com evidência;
6. fechar P-640 e ativar P-650.

## Invariantes obrigatórios

- sem cutover;
- sem retirement;
- sem bulk migration;
- sem legacy delete;
- sem alteração de Search ranking;
- sem alteração de `post_content`;
- sem escrita em `_elementor_data`;
- sem endpoint novo;
- sem mudança de versão pública;
- package P640 é somente homologação.

## Prompt para novo chat

Continuar a SPEC-006 no repositório `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`, branch `spec006-premium-product-foundation`.

Antes de qualquer alteração:

1. confirmar o HEAD atual no GitHub;
2. ler `AGENTS.md`;
3. ler `.specify/memory/constitution.md`;
4. ler `docs/PREMIUM-PLUGIN-PRODUCT-STANDARD.md`;
5. ler `docs/DEFINITION-OF-DONE.md`;
6. ler `specs/006-premium-product-foundation/spec.md`;
7. ler `specs/006-premium-product-foundation/tasks.md`;
8. ler `specs/006-premium-product-foundation/p640-modular-runtime-contract-v1.md`;
9. ler `specs/006-premium-product-foundation/p640-environmental-acceptance-20260929.md`;
10. ler `specs/MASTER-FUNCTIONAL-PARITY-LEDGER.md`;
11. ler este `CONTINUIDADE.md`.

Estado: P640-02..05 implementados. P640-06 REABERTO por não conformidade do mecanismo de execução; refazer localmente. O JSON ambiental P640 recebido indica PASS funcional, mas P640-07 não deve ser fechado até existir package local conforme. Não reimplementar P640-02..05.

Se o usuário anexar o JSON P640 ambiental, validar o artefato como fonte de verdade, fechar P640-07 somente se todas as assertions aplicáveis passarem, executar P640-08 inventory, atualizar o Master Ledger com evidência e então avançar para P-650.

Nunca autorizar cutover ou retirement por inferência.
