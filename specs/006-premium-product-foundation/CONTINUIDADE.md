# Continuidade — SPEC-006 Premium Product Foundation

## Repositório / branch

- repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`;
- branch ativa: `spec006-premium-product-foundation`;
- versão source: `0.6.0-dev`;
- runtime P640 validado em: `de1a051a876e514c5545756c08bcb734b448335a`;
- confirmar o HEAD antes de qualquer nova alteração; gates P640 devem ser executados LOCALMENTE, nunca por GitHub Actions.

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
  - P640-06: REABERTO — executor local canônico implementado; static/lint local parcial PASS, WPCS/PHPUnit bloqueados por tooling ausente nesta sessão.
  - P640-07: resultado ambiental funcional PASS recebido e versionado; fechamento bloqueado até package reconstruído/validado localmente.
  - P640-08: ferramenta de inventory implementada; fechamento aguarda P640-06/P640-07 conformes.
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

## P640-06 — execução local obrigatória

Executor canônico:

- `tools/homologation/spec006/validate-p640-local.py`;
- falha fechado se `vendor/bin/phpcs` ou `vendor/bin/phpunit` não existirem;
- executa contratos estáticos, lint completo, WPCS afetado, PHPUnit e build determinístico;
- grava evidência local;
- não usa GitHub Actions.

Evidência parcial real desta sessão:

- `evidence/spec006-p640-local-revalidation-partial-20260929.json`;
- registry static contract: 14/14 PASS;
- modular runtime contract: 15/15 PASS;
- PHP lint: 131/131 PASS;
- Composer/PHPCS/PHPUnit: indisponíveis neste runtime;
- P640-06 permanece ABERTO.

## Correção de governança

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

## P640-07 — ambiental

JSON recebido do ambiente:

- `evidence/spec006-p640-environmental-functional-pass-pending-local-package-20260929.json`;
- WordPress 6.9.4;
- PHP 8.5.10;
- plugin 0.6.0-dev;
- Search/Public Preview/Word Cloud PASS;
- todas as assertions true;
- content_mutation=false;
- data_migration=false;
- cutover/retirement=false.

Esse resultado é funcionalmente PASS, mas o gate não fecha até reconciliar com package construído pelo executor local.

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


## P640-08 — inventory preparado

Ferramenta:
- `tools/homologation/spec006/inventory-p640-package-runtime.py`.

Diagnóstico atual:
- 150 arquivos no plugin;
- 131 PHP;
- 42 PHP core declarados;
- 25 PHP de módulos de produto;
- 46 PHP de engenharia/homologação;
- zero arquivos de engenharia ausentes;
- zero overlap core/product/engineering;
- 15 PHP fora das três fronteiras, agora classificados para disposition em P-650;
- nenhuma exclusão física autorizada em P-640.

## Próximo passo exato atualizado

1. em um checkout local com Composer dependencies instaladas, executar:
   `python tools/homologation/spec006/validate-p640-local.py`;
2. exigir status PASS e JSON local;
3. usar o ZIP gerado localmente para reconciliar/repetir P640-07;
4. executar:
   `python tools/homologation/spec006/inventory-p640-package-runtime.py`;
5. fechar P640-08 apenas após PASS;
6. somente então considerar promoção de PROD-005 e ativar P-650.

Regra operacional explícita:
- NÃO usar GitHub Actions como executor de gate neste projeto.


## P-650 — implementação iniciada sem fechar P-640

Contrato:
- `specs/006-premium-product-foundation/p650-packaging-install-upgrade-contract-v1.md`.

Implementado:
- `tools/homologation/spec006/build-p650-production.py`;
- `tests/unit/spec006-p650-package-contract.php`;
- `tools/homologation/spec006/validate-p650-local.py`.

Arquitetura de distribuição:
- source permanece homologável e conserva ferramentas de engenharia;
- package candidato remove fisicamente o Engineering_Module_Loader e os arquivos declarados por ele;
- bootstrap do ZIP não contém flags/calls de engenharia;
- Search/Public Preview/Word Cloud permanecem;
- build duplo obrigatório;
- root único;
- manifest + checksum;
- specs/evidence/tests/tools/vendor proibidos no ZIP;
- classes históricas Elementor permanecem até disposition com evidência.

Gate fail-closed:
- P650 local validator exige `evidence/spec006-p640-local-validation-current.json` com `status=PASS` e `execution_mode=LOCAL_ONLY`;
- sem esse artefato P650 retorna `BLOCKED_P640`;
- portanto P650 não contorna P640-06.

Próximo passo:
1. obter ambiente local com Composer deps;
2. executar `python tools/homologation/spec006/validate-p640-local.py`;
3. após PASS, executar `python tools/homologation/spec006/validate-p650-local.py`;
4. instalar exatamente o ZIP p650.1 em homologação;
5. validar fresh install, upgrade sobre 0.5.x/0.6.0-dev, preservação de dados e rollback;
6. executar Plugin Check no mesmo ZIP;
7. somente então fechar P650 e atualizar PROD-005/PROD-006 conforme evidência.


## P650.1 — pacote pronto para homologação ambiental (2026-09-30)

Artefato:
- `base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.1.zip`;
- SHA-256: `503234ece2620d01bb556e5810a374f07e2153221171f21755bbe7c1b4f5b926`;
- 103 arquivos / 84 PHP;
- manifest SHA-256: `4df8aaf2142d1572cfae4c67fbe54d763d56a8256c4b78bc4b2b73a9303086f4`.

Proveniência:
- source HEAD usado para equivalência: `b45386a4f173bdafd7b44d2a56c43f4883c49e55`;
- plugin subtree SHA esperado: `ddb30b150b78c08e9163f7324c5f911c4ead13d0`;
- snapshot local reconstruído confirmou exatamente o mesmo Git tree SHA;
- build executado localmente;
- GitHub Actions não foi usado como executor de gate/package.

Validação local do ZIP:
- deterministic build PASS;
- single root PASS;
- engineering files = 0;
- engineering bootstrap tokens = 0;
- forbidden repository paths = 0;
- missing declared Core/Product runtime files = 0;
- PHP lint dentro do ZIP = 84/84 PASS.

Status:
- autorizado para HOMOLOGAÇÃO AMBIENTAL;
- NÃO autorizado para produção;
- P640-06 completo ainda depende de Composer/PHPCS/PHPUnit local;
- P650 install/upgrade/rollback e Plugin Check ainda pendentes.

Evidência:
- `evidence/spec006-p650-homologation-package-20260930.json`.

### Próxima ação no ambiente

Instalar exatamente o ZIP `p650.1` e registrar:

1. versão WordPress/PHP;
2. instalação/upgrade sem fatal error;
3. Knowledge Workspace acessível;
4. Search funcional;
5. Public Experience Preview funcional;
6. Word Cloud funcional;
7. ausência dos menus/runners de engenharia;
8. ausência de alteração automática de `post_content`;
9. ausência de escrita em `_elementor_data`;
10. comportamento de rollback, se executado.

Não reconstruir o ZIP antes da homologação; o SHA acima identifica o artefato sob teste.


## P-660 iniciado — security/privacy baseline (2026-09-30)

Contrato:
- `specs/006-premium-product-foundation/p660-security-privacy-baseline-contract-v1.md`.

Tooling:
- `tools/homologation/spec006/inventory-p660-security-privacy.py`;
- `tests/unit/spec006-p660-security-baseline.php`.

Baseline sobre o ZIP p650.1:
- 84 PHP;
- 25 arquivos sinalizados para revisão;
- SECRET_CRITICAL=0;
- NETWORK_HIGH=0;
- MUTATION_HIGH=0;
- DB_HIGH=0;
- mutation/admin handlers principais possuem POST + capability + nonce + unslash/sanitization;
- Search/Word Cloud dynamic SQL revisado usa prepare quando há parâmetros.

Correções:
1. `Public_Auth_Bridge::current_url()` não usa mais `HTTP_HOST`; usa `home_url()` + path/query derivados de `REQUEST_URI`.
2. `class-real-content-acceptance.php`, ferramenta temporária G-240 não referenciada, foi classificada como engineering orphan e excluída do package.

Novo pacote:
- `base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.2.zip`;
- SHA-256 `1d48ecc25d2f1bbe173ab83e6368f46306c5de054cdd19b411945c328b6051f7`;
- 102 arquivos;
- 83 PHP;
- lint 83/83 PASS;
- deterministic_equal=true;
- single root PASS;
- engineering loader absent;
- orphan acceptance absent;
- repo-only paths absent;
- HTTP_HOST trust absent.

Próximo passo:
1. instalar exatamente p650.2 em homologação;
2. validar login/fallback público, Search, Workspace, Public Preview e Word Cloud;
3. confirmar ausência de erro;
4. executar Plugin Check oficial no mesmo ZIP quando tooling local estiver disponível;
5. continuar disposition P660 dos sinais review-level;
6. não promover PROD-004/005/006 antes das evidências completas.

GitHub Actions continua proibido como executor de gate.


## P650.2 environmental PASS / P660-06 fechado

Aceite humano:
- package testado: `base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.2.zip`;
- SHA-256: `1d48ecc25d2f1bbe173ab83e6368f46306c5de054cdd19b411945c328b6051f7`;
- resultado reportado: tudo funcionando aparentemente sem problemas;
- P660 hardened environmental smoke: PASS.

Evidência:
- `evidence/spec006-p6502-environmental-smoke-pass-20260930.json`.

Residual P660:
- `specs/006-premium-product-foundation/p660-residual-security-disposition-20260930.md`;
- SECRET_CRITICAL=0;
- NETWORK_HIGH=0;
- MUTATION_HIGH=0;
- DB_HIGH=0;
- sinais restantes classificados como REVIEW/JUSTIFIED até Plugin Check oficial;
- nenhum waiver global criado.

Plugin Check:
- executor local: `tools/homologation/spec006/run-p650-p660-plugin-check-local.py`;
- deve operar sobre o MESMO ZIP p650.2;
- GitHub Actions não é permitido;
- não gerar p650.3 antes do resultado do Plugin Check, salvo correção concreta necessária.

Estado:
- P660-06 PASS;
- P660-07 pendente Plugin Check oficial;
- P650-06 ainda não fecha porque rollback não foi executado;
- P650-07 pendente Plugin Check;
- P640-06 continua pendente pelo tooling Composer/PHPCS/PHPUnit local completo.

Próximo passo lógico:
1. executar Plugin Check oficial local sobre p650.2;
2. versionar JSON do resultado;
3. corrigir somente findings reais de produção;
4. se houver alteração de código, gerar novo package e repetir smoke ambiental;
5. se não houver blocker, executar/registrar rollback P650;
6. fechar P650/P660 somente após evidências completas;
7. então preparar P670 Premium Foundation Acceptance.


## P-670 preparado

Contrato:
- `specs/006-premium-product-foundation/p670-premium-foundation-acceptance-contract-v1.md`.

P-670 está apenas PREPARED. Não pode fechar enquanto:
- P640 local admissível não fechar;
- P650 rollback + Plugin Check não fecharem;
- P660 Plugin Check/disposition final não fechar;
- Master Ledger não for atualizado por evidência.

O contrato já consolida DoD, Premium Product Standard, package integrity, security/privacy, runtime modular, editorial invariants e limites de cutover.


## P650.3 — request-boundary hardening (2026-09-30)

Motivo:
- revisão residual P660 identificou request boundaries que estavam funcionalmente protegidos, porém sem sanitização explícita suficiente para WPCS/Plugin Check;
- o mesmo ciclo corrigiu o runner local do Plugin Check para o modo oficial com runtime checks.

Alterações de runtime:
- Admin Page: REQUEST_METHOD e nonce sanitizados;
- Classification Admin: REQUEST_METHOD e nonce sanitizados;
- Review Admin: REQUEST_METHOD e nonce sanitizados;
- Core Blocks Authorization: REQUEST_METHOD/nonce sanitizados e JSON output documentado;
- Public Experience: preview nonce sanitizado;
- Public Search: preview flag sanitizada; REMOTE_ADDR sanitizado e limitado antes do HMAC transient;
- Word Cloud Admin: textarea é unslashed explicitamente no request boundary.

Artefato:
- `base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.3.zip`;
- SHA-256: `985091a289f11c0ae449e6f93e2f4090ddd3790762df42cff4a8a97fc775c231`;
- 102 arquivos / 83 PHP;
- deterministic build PASS;
- PHP lint 83/83 PASS;
- repo-only paths = 0;
- engineering/lab named files = 0;
- bootstrap engineering tokens = 0;
- changed runtime blob provenance = MATCH com source GitHub.

P660 inventory p650.3:
- status PASS;
- 83 PHP scanned;
- 24 arquivos review-level;
- MUTATION_REVIEW=9;
- OUTPUT_MEDIUM=21;
- DB_REVIEW=4;
- READ_MEDIUM=3;
- SECRET_CRITICAL=0;
- NETWORK_HIGH=0;
- MUTATION_HIGH=0;
- DB_HIGH=0.

Plugin Check runner:
- `tools/homologation/spec006/run-p650-p660-plugin-check-local.py`;
- target passa a ser o caminho do ZIP diretamente;
- `--format=strict-json`;
- `--mode=update`;
- `--require=.../plugin-check/cli.php` para habilitar runtime checks;
- GitHub Actions continua proibido.

Importante:
- p650.2 permanece evidência histórica de smoke PASS;
- como runtime mudou, p650.3 exige novo smoke ambiental antes do Plugin Check final;
- depois do PASS ambiental, o Plugin Check deve usar exatamente o SHA do p650.3;
- P650/P660 continuam abertos até Plugin Check + rollback/evidências restantes.


## p650.3 — environmental PASS confirmado

Aceite humano:
- artefato: `base-conhecimento-inteligencia-integrada-0.6.0-dev-p650.3.zip`;
- SHA-256: `985091a289f11c0ae449e6f93e2f4090ddd3790762df42cff4a8a97fc775c231`;
- resultado: tudo continua funcionando aparentemente;
- runtime smoke: PASS.

Estado do artefato:
- FROZEN para os gates finais;
- não gerar p650.4 sem finding concreto;
- Plugin Check runner rejeita SHA diferente;
- P650 local validator atualizado para p650.3.

Rollback:
- runner: `tools/homologation/spec006/run-p650-rollback-local.py`;
- fluxo previous -> p650.3;
- compara contagem e SHA-256 determinístico de post_content;
- compara contagens e SHA-256 das metas BDC;
- não exporta conteúdo editorial.

Runbook final:
- `specs/006-premium-product-foundation/p650-p660-final-local-gates-runbook.md`.

Próximos blockers:
1. Plugin Check oficial local do p650.3;
2. rollback local com fingerprints PASS;
3. P640 local Composer/PHPCS/PHPUnit admissível;
4. então P650/P660 closeout e P670.


## Final local gates — executor e proveniência endurecidos

Alterações:
- novo build P640 admissível: `p640.2`;
- `p640.1` permanece somente como histórico invalidado;
- P640 grava `source_commit` + `plugin_tree_sha`;
- P650 exige o mesmo `plugin_tree_sha` do P640 e falha `BLOCKED_STALE_P640_EVIDENCE` em divergência;
- P650 exige SHA congelado do p650.3: `985091a289f11c0ae449e6f93e2f4090ddd3790762df42cff4a8a97fc775c231`;
- P660 local validator: `tools/homologation/spec006/validate-p660-local.py`;
- executor Windows: `tools/homologation/spec006/run-spec006-final-gates.ps1`.

Executor Windows roda:
1. P640 local completo / p640.2;
2. P650 local package quality / p650.3;
3. P660 local security/privacy quality;
4. Plugin Check oficial;
5. rollback;
6. summary JSON.

Preflight deste runtime:
- PHP 8.4.23 disponível;
- Composer ausente;
- PHPCS ausente;
- PHPUnit ausente;
- WP-CLI ausente;
- portanto gates ambientais/quality finais não podem ser legitimamente executados aqui;
- evidência: `evidence/spec006-final-local-gates-preflight-blocked-runtime-20260930.json`.

Composer:
- `composer.lock` não está versionado;
- bootstrap de dependências no executor é opt-in;
- não resolver dependências silenciosamente.

Próxima evidência esperada:
- `evidence/spec006-p640-local-validation-current.json` = PASS;
- `evidence/spec006-p650-local-package-validation-current.json` = PASS;
- `evidence/spec006-p660-local-validation-current.json` = PASS;
- `evidence/spec006-p650-p660-plugin-check-current.json`;
- `evidence/spec006-p650-rollback-current.json`;
- `evidence/spec006-final-local-gates-summary-current.json`.

Somente depois reconciliar P650/P660, Master Ledger e P670.


## P670 — preflight e closeout candidate automatizados

Implementado:
- `tools/homologation/spec006/validate-p670-preflight.py`;
- `tests/unit/spec006-p670-preflight-contract.php`;
- `tools/homologation/spec006/generate-p670-closeout-candidate.py`;
- `specs/006-premium-product-foundation/p670-master-ledger-disposition-plan.md`.

O executor Windows agora continua após Plugin Check/rollback:
1. grava summary local;
2. executa contrato estático P670;
3. executa P670 preflight;
4. somente com `PASS_PRECONDITIONS`, gera closeout candidate;
5. closeout candidate permanece `READY_FOR_HUMAN_LEDGER_REVIEW`.

P670 exige coerência de:
- P640 LOCAL_ONLY PASS;
- P640 plugin_tree_sha = source atual;
- P640 build p640.2;
- P650 LOCAL_ONLY PASS;
- frozen SHA p650.3;
- P660 LOCAL_ONLY PASS;
- smoke ambiental p650.3;
- Plugin Check oficial LOCAL_ONLY no mesmo SHA;
- rollback PASS + data_preserved=true;
- final local summary no mesmo source/package.

Política histórica:
- P600/P610/P620 remotos são preservados como histórico;
- não fecham os gates atuais;
- claims materiais são reprovadas pelos gates locais P640/P650/P660/Plugin Check.

Master Ledger:
- nenhum update automático;
- candidatos condicionais após PASS_PRECONDITIONS: PROD-003/004/005/006 -> IMPROVED_VERIFIED;
- aplicar somente após revisão explícita das evidências;
- nenhuma promoção GRE/KB2/ASI/ENV por inferência.

Próximo passo externo indispensável:
- executar `run-spec006-final-gates.ps1` em checkout Windows com tooling + WP-CLI + WordPress;
- retornar os JSONs gerados para revisão e closeout final.


## P640 environmental reconciliation sem novo ZIP

Novo runner:
- `tools/homologation/spec006/run-p640-environmental-reconciliation-local.py`.

Decisão:
- não instalar p640.2 apenas para provar runtime;
- p640.2 continua artefato local de quality/proveniência;
- runtime P640 será provado no próprio p650.3 congelado via WP-CLI read-only;
- valida Core_Runtime_Loader, Runtime_Module_Registry, Plugin, Search, Public Experience, Word Cloud, hooks registrados e ausência da Engineering_Module_Loader;
- nenhuma mutation/migration;
- evidence: `evidence/spec006-p640-environmental-reconciliation-current.json`.

P670 foi endurecido para exigir essa reconciliação antes de PASS_PRECONDITIONS.

## Estado macro corrigido

- SPEC-005: CLOSED / boundary aprovado;
- SPEC-006: ACTIVE / FINAL GATES;
- SPEC-007: NEXT / NOT ACTIVE.

Atualizados:
- `.specify/PROJECT_MANIFEST.md`;
- `specs/ROADMAP.md`.

Handoff preparado:
- `specs/006-premium-product-foundation/spec006-to-spec007-handoff-plan.md`.

SPEC-007 não pode iniciar runtime até closeout formal da SPEC-006.

## Artefatos finais de closeout preparados

Templates:
- `specs/006-premium-product-foundation/p670-final-closeout-template.md`;
- `specs/006-premium-product-foundation/p670-final-evidence-template.json`.

Ambos são TEMPLATE/NOT EVIDENCE.

Fluxo final:
1. executar `run-spec006-final-gates.ps1`;
2. P640 local PASS;
3. P650 local PASS;
4. P660 local PASS;
5. P640 environmental reconciliation PASS sobre p650.3;
6. Plugin Check PASS/disposition;
7. rollback PASS/data preserved;
8. P670 PASS_PRECONDITIONS;
9. closeout candidate READY_FOR_HUMAN_LEDGER_REVIEW;
10. revisar/aplicar Master Ledger;
11. materializar evidence/closeout final;
12. fechar SPEC-006;
13. somente então ativar SPEC-007.


## Execução real dos final gates — tentativa deste runtime

Foi feita a tentativa de execução dos gates finais no runtime atual.

Checks que puderam ser executados:
- p650.3 SHA-256 = `985091a289f11c0ae449e6f93e2f4090ddd3790762df42cff4a8a97fc775c231`;
- root único PASS;
- 102 arquivos / 83 PHP;
- PHP lint 83/83 PASS;
- forbidden repository paths = 0;
- engineering/lab named files = 0;
- Engineering_Module_Loader ausente;
- flag P640 ambiental ausente do package;
- Search/Public Experience/Word Cloud habilitados;
- required runtime files missing = 0;
- plugin tree atual = `2a205aa5ef1804f4f3da30f3f9832709d25bc800`, sem mudança desde homologação.

Blockers reais do runtime atual:
- Composer ausente;
- PHPCS ausente;
- PHPUnit ausente;
- WP-CLI ausente;
- WordPress runtime não exposto;
- checkout do repositório não montado;
- clone externo bloqueado por DNS (`Could not resolve host: github.com`).

Classificação:
- `BLOCKED_EXTERNAL_ENVIRONMENT`;
- não é FAIL funcional;
- não fecha P640/P650/P660/P670;
- não autoriza simular Plugin Check/rollback.

Evidência:
- `evidence/spec006-final-gates-execution-attempt-20260930.json`.

Nenhum novo ZIP foi gerado e o runtime do plugin não foi alterado.

Próxima execução admissível continua sendo `run-spec006-final-gates.ps1` em workstation com checkout + Composer tooling + WordPress/WP-CLI.


## Click-to-run WordPress para final gates

Companion temporário criado:
- `bdc-spec006-final-gates-runner-1.0.0.zip`;
- SHA-256 `3e34569d1eb60eae5c2fd89e14dd07b5b683d5c744bbff9cff664c4d6e5ba531`;
- NÃO substitui nem modifica o BDC p650.3.

Fluxo no WordPress:
1. manter p650.3 instalado/ativo;
2. instalar/ativar o companion;
3. abrir Base de Conhecimento -> SPEC-006 Final Gates;
4. preparar Plugin Check oficial se necessário;
5. Executar validação completa;
6. confirmar rollback controlado;
7. baixar JSON e retornar para closeout.

O companion:
- verifica p650.3 byte-a-byte por manifest;
- valida runtime Search/Public/Word Cloud;
- usa Plugin Check oficial via WP Admin AJAX;
- não usa AI/experimental/PCP Ignore;
- executa rollback resumível p650.3 -> p650.2 -> p650.3;
- compara hashes de post_content e metas BDC;
- valida identidade final do p650.3.

Limite:
- PHPCS/WPCS e PHPUnit permanecem EXTERNAL_TOOLING_REQUIRED.

Contrato:
- `specs/006-premium-product-foundation/spec006-wordpress-click-runner-contract-v1.md`.

Evidence de package:
- `evidence/spec006-wordpress-click-runner-package-20260930.json`.


## Click-runner ambiental — primeira execução real

Resultado no WordPress de homologação:
- p650.3 installed integrity: PASS;
- expected_files=102 / checked_files=102;
- missing=[] / mismatched=[] / extra=[];
- modular runtime: PASS;
- version/core loader/module registry/plugin/Search/Public Experience/Word Cloud/hooks/plugin active: todos PASS;
- Engineering_Module_Loader ausente como esperado;
- Plugin Check oficial: NOT_INSTALLED;
- execução completa: BLOCKED somente por Plugin Check não pronto;
- rollback ainda NOT_RUN;
- PHPCS/WPCS + PHPUnit continuam EXTERNAL_TOOLING_REQUIRED.

Evidence:
- `evidence/spec006-click-runner-environmental-partial-20260930.json`.

Próxima ação exata:
1. clicar **Instalar/ativar Plugin Check oficial** na tela SPEC-006 Final Gates;
2. confirmar que o status passa para pronto/ativo;
3. clicar **Executar validação completa** novamente;
4. confirmar o rollback quando solicitado;
5. baixar o JSON final e retornar para review/closeout.


## Click-runner 1.0.1 — diagnóstico de transporte Plugin Check

Primeira execução completa do runner 1.0.0:
- installed integrity PASS;
- modular runtime PASS;
- Plugin Check stage falhou com `Unexpected end of JSON input`;
- rollback não iniciou;
- não há evidência de regressão BDC;
- não há finding Plugin Check ainda;
- root cause exato não pode ser inferido porque 1.0.0 fazia `response.json()` diretamente.

Patch 1.0.1:
- robust parsing via `response.text()` + `JSON.parse`;
- registra action/stage/check;
- registra HTTP status/status text;
- registra Content-Type;
- registra body length/body preview;
- registra parse error;
- salva transport failure no report;
- habilita download do JSON parcial em BLOCKED;
- não faz retry silencioso;
- não altera p650.3.

Artifact:
- `bdc-spec006-final-gates-runner-1.0.1.zip`;
- SHA-256 `e497465f1d4dbea2bd5ac548b8c008f357cad173501a9b15dc5b04033cac5982`.

Evidence:
- `evidence/spec006-click-runner-plugin-check-transport-incident-20260930.json`;
- `evidence/spec006-wordpress-click-runner-package-1.0.1-20260930.json`.

Próxima ação:
1. substituir somente o companion 1.0.0 por 1.0.1;
2. manter BDC p650.3 e Plugin Check como estão;
3. executar validação completa;
4. se bloquear, baixar JSON parcial e retornar;
5. se prosseguir, confirmar rollback e baixar JSON final.
