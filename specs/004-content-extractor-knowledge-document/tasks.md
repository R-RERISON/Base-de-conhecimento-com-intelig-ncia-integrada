# Tarefas — SPEC-004 Content Extractor e Knowledge Document

## S001 — R-200 / Current State do corpus

- [x] T001 Confirmar baseline de entrada `0.3.0-rc.1` e fechamento da SPEC-003.
- [x] T002 Ler contrato editorial WordPress/Elementor existente.
- [x] T003 Inventariar prior art do KB2Ops `Content_Extractor`.
- [x] T004 Registrar riscos/limitações que não devem ser copiados automaticamente.
- [x] T005 Implementar profiler temporário read-only `0.4.0-profile.1`.
- [x] T006–T015 Medir corpus real, estruturas, formatos, budgets, fallback e zero mutação.

**Gate R-200: PASS — 2026-09-15.**

## S002 — R-210 / Extraction Contract

- [x] T020–T029 Congelar source precedence, adapters, shortcodes, fallback, normalização, warnings e budgets.
- [x] T029A Incorporar direção editorial Elementor e portabilidade/produção no amendment `v1.1.0`.

Contratos:

- `extraction-contract-v1.md` — `FROZEN v1.0.0`;
- `extraction-contract-v1.1.md` — `FROZEN v1.1.0`.

**Gate R-210: PASS.**

## S003 — G-220 / Content Extractor determinístico

- [x] T030–T042 Implementar detector, adapters, normalização, fallback, testes, readiness Elementor e integração read-only.
- [x] T043 Implementar runner ambiental temporário G-220.
- [x] T044 Gerar e validar package `0.4.0-smoke.1`.
- [x] T045 Executar `0.4.0-smoke.1` no WordPress de homologação.
- [x] T046 Validar fingerprint equal, zero changed posts, corpus 622→622, zero extractor errors e zero throwables.
- [x] T048 Desabilitar runner G-220 no build seguinte; não será promovido a RC.

Evidências:

- `g220-local-validation.md`;
- `package-smoke1.md`;
- `g220-smoke-analysis.md`;
- `evidence/g220-smoke-20260915T221710Z.json`.

**Gate G-220: PASS — 2026-09-15.**

## S004 — G-230 / Knowledge Document

- [x] T050 Congelar schema version 1 em `knowledge-document-contract-v1.md`.
- [x] T051 Implementar builder canônico `Knowledge_Document`.
- [x] T052 Implementar `Canonical_JSON` com ordenação determinística de mapas e preservação de listas.
- [x] T053 Implementar `source_hash` SHA-256 sobre conhecimento semântico extraído.
- [x] T054 Implementar `document_hash` SHA-256 com envelope operacional fora do escopo do hash.
- [x] T055 Testar estabilidade de hash/JSON para input idêntico.
- [x] T056 Testar mudança de hash para alteração semântica/título/ordem.
- [x] T057 Testar neutralidade de URL/data operacional e ruído bruto/layout não utilizado.
- [x] T058 Confirmar ausência de storage durável não autorizado.
- [x] T059 Incorporar `elementor_compatibility` somente como proveniência/readiness.
- [x] T059A Implementar runner ambiental G-230 com duas passagens e comparação hash/JSON sem exportar conteúdo.
- [x] T059B Gerar/validar package `0.4.0-smoke.2`.
- [x] T059C Executar smoke G-230 no WordPress de homologação.
- [x] T059D Comprovar 622 documentos nas duas passagens, zero errors/throwables, zero hash mismatch, zero canonical JSON mismatch e zero mutação editorial.

Evidências:

- `knowledge-document-contract-v1.md`;
- `g230-local-validation.md` — **10/10 PASS**;
- `package-smoke2.md`;
- `g230-smoke-analysis.md`;
- `evidence/g230-smoke-20260915T233450Z.json`.

Resultado ambiental:

- first pass: `622/622`;
- second pass: `622/622`;
- errors: `0/0`;
- throwables: `0/0`;
- `hash_mismatches=0`;
- `canonical_json_mismatches=0`;
- `editorial_fingerprint_equal=true`;
- `changed_posts_during_run=0`;
- unique source hashes: `601`;
- unique document hashes: `622`;
- sections: `21.969`;
- runtime duas passagens: `3096 ms`;
- peak memory: `31.457.280 bytes`.

Package `0.4.0-smoke.2` SHA-256:

`1466cd4fcd18120d0b2405bf04ec629230f23c2a2e759869a8123c45cedaf204`

**Gate G-230: PASS — 2026-09-15.**

## S005 — G-240 / Real Content Acceptance

- [x] T060 Selecionar amostra representativa baseada no profiler/smokes; runner read-only temporário criado em `includes/class-real-content-acceptance.php`, com limite de 3 casos por categoria.
- [x] T061 Validar Elementor típico; 3 casos no browser acceptance.
- [x] T062 Validar Elementor complexo/widgets corporativos; cobertura por `review_required` e assinaturas estruturais.
- [x] T063 Validar Gutenberg; 3 casos.
- [x] T064 Validar HTML legado; 3 casos.
- [x] T065 Validar shortcode/tabela relevante; 3 casos.
- [x] T066 Validar conteúdo vazio/corrompido; 2 casos disponíveis.
- [x] T067 Repetir build e comparar hashes/JSON; 0 mismatches em 23 casos.
- [x] T068 Comparar conteúdo derivado com fonte por inspeção controlada; 1.114 seções e assinaturas estruturais exibidas inline.
- [x] T069 Provar zero mutação editorial em amostra real; fingerprint igual e 0 posts alterados.

**Gate G-240: PASS — browser acceptance ambiental executado em 2026-09-16.**

## S006 — G-245 / Elementor Normalization & Production Readiness

- [x] T080 Congelar `elementor-normalization-contract-v1.md`.
- [x] T081 Congelar `production-rollout-contract-v1.md`.
- [x] T082 Implementar Production Preflight read-only para comparar ambiente atual com perfil alvo; sem perfil configurado o resultado é `NOT_CONFIGURED`, nunca `PASS`.
- [x] T083 Congelar matriz de compatibilidade WordPress/PHP/Elementor/plugins relevantes em `compatibility-matrix-v1.md`; baseline de homologação registrada, produção ainda `NOT_VERIFIED`.
- [x] T084 Implementar Projection Plan read-only por post; plano determinístico, hash SHA-256, estratégia por fonte e zero writer.
- [x] T085 Implementar `Elementor_Gateway` version-gated; inspeção compatível em Elementor `4.1.0`, writer desabilitado e fail-closed por default.
- [x] T086 Definir journal transacional/rollback editorial em `editorial-journal-rollback-contract-v1.md`; nenhum storage ou writer criado.
- [x] T087 Implementar dry-run sem writes; corpus `622`, erros `0`, journal `NOT_CONFIGURED`, fingerprint editorial preservado.
- [x] T088 Implementar stale-source guard por `source_hash`/`post_modified_gmt`; divergência retorna `STALE_SOURCE` e nunca autoriza write.
- [x] T089 Definir execução explícita em lotes retomáveis em `editorial-batch-execution-contract-v1.md`; executor e writer continuam bloqueados.
- [x] T090 Executar canário técnico read-only em homologação; 20/20 posts em 7 categorias `FRESH`, zero erros e zero mutação. Validação de save no editor permanece `NOT_RUN` por writer bloqueado.
- [x] T091 Testar rollback integral do canário Elementor nativo e projectable; nativo `3/3`, HTML/texto `2/2`, snapshots integrais idênticos, `0` erros.
- [x] T092 Produzir runbook de instalação/upgrade/migration/rollback para produção em `production-migration-runbook-v1.md`; migration permanece bloqueada.

**Gate G-245: IN_PROGRESS — builders e rollback projectable PASS; Gutenberg mixed/review_required, frontend por origem e release final pendentes.**

### Evidência de regressão automatizada

- SPEC-001: `15/15 PASS`;
- SPEC-003: `19/19 PASS`;
- SPEC-004 G-220: `14/14 PASS`;
- SPEC-004 G-230: `10/10 PASS`;
- Core Test Suite/PHPUnit: `NOT_RUN` por ausência de `wordpress-tests-lib`/PHPUnit e incompatibilidade atual do bootstrap WP-CLI.
- Validador reproduzível: `tools/validate-local.sh` executado no container PHP;
- JavaScript: `node --check` executado em `node:22-alpine`, `PASS`;
- lint do plugin no runtime: `26/26 PASS`.
- Elementor Gateway no WordPress real: status compatível, Document `Elementor\\Core\\DocumentTypes\\Post`, save `BLOCKED`, metadata editorial inalterada.
- Gateway unitário: `1/1 PASS`.
- Dry-run ambiental: `622` posts, `0` erros, `0` bloqueados, fingerprint editorial igual, sem journal/storage.
- Stale Source Guard unitário: `1/1 PASS` para `FRESH` e `STALE_SOURCE`.
- Canário técnico: `7/7` categorias `FRESH`, gateway sem erros, hashes de documento com 64 caracteres, fingerprint editorial igual, writer não tentado.
- Canário multi-origem ampliado: `20/20` posts; legacy HTML `3`, plain text `3`, Elementor native `3`, mixed `3`, Gutenberg `3`, review_required `3`, vazio/corrompido `2`; fingerprint igual e writer não tentado.
- Rollback canário Elementor nativo: `3/3 PASS`, snapshots completos iguais após restore, `0` erros, nenhum batch.
- Rollback projectable: HTML/texto `2/2 PASS`, snapshots completos iguais após restore; snapshot registra presença/ausência de metas.
- Rollback Gutenberg estático: `1/1 PASS` no post `13575`; snapshot integral igual após restore. Gutenberg mixed/native e `review_required` permanecem bloqueados.
- Frontend/browser: HTML `358` PASS, texto `367` PASS, Elementor nativo `385` PASS sem erro fatal; Gutenberg `13575` respondeu como rascunho automático e fica `NOT_VERIFIED` para publicação.
- Canário não-Elementor: `NOT_ACCEPTED`; promoção criou metadados Elementor e rollback inicial não removeu todos os metadados criados. Posts `358`/`367` foram reparados e novas escritas estão bloqueadas.
- Projection Builder unitário: `1/1 PASS`; HTML legado, plain text e Gutenberg estático geram árvore Elementor determinística; `review_required` permanece bloqueado.

## S007 — G-250 / Lifecycle, package e baseline final

- [ ] T100 Remover definitivamente profiler/runners temporários.
- [ ] T101 Gerar `0.4.0-rc.1` clean.
- [ ] T102 Source parity do package.
- [ ] T103 PHP lint/JS syntax/ZIP integrity.
- [ ] T104 Validar instalação limpa e update sobre baseline compatível.
- [ ] T105 Deactivate/activate sem job editorial implícito.
- [ ] T106 Smoke de regressão das SPECs 001–003.
- [ ] T107 Smoke de Content Extractor/Knowledge Document sem write.
- [ ] T108 Validar production preflight/fail-closed para Elementor não homologado.
- [ ] T109 Congelar baseline final SPEC-004.

**Gate G-250: NOT_RUN.**

## Regras constitucionais de continuidade

1. Content Extractor/Knowledge Document permanecem read-only.
2. Normalização editorial para Elementor é fluxo de migration separado e explícito.
3. Instalação/activation/update nunca converte posts automaticamente.
4. Nenhuma migration editorial em massa antes de dry-run, journal/rollback e canário.
5. Usuário/editor vence sobre migration atrasada: source divergente vira `STALE_SOURCE`.
6. IA não é usada para reparar parsing nem para writer inicial de Elementor.
7. Runners de smoke são temporários e não chegam ao RC/produção.
8. Nenhuma etapa posterior compensa lacuna de segurança da anterior.
