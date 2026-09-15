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

- [x] T030 Implementar detector read-only de origem/flags e budgets.
- [x] T031 Implementar adapter Elementor allowlisted sem renderização.
- [x] T032 Implementar adapter Gutenberg estático sem `render_block()`.
- [x] T033 Implementar adapter Legacy HTML com `DOMDocument` opcional e fallback estrutural.
- [x] T034 Implementar normalizador estrutural/textual determinístico.
- [x] T035 Implementar fallback fail-soft conforme R-210/R-210.1.
- [x] T036 Confirmar ausência de necessidade de cache/persistência.
- [x] T037–T040 Testes de adapters, inválidos, shortcodes, budgets, zero-write e repetibilidade.
- [x] T041 Adicionar readiness `native/projectable/review_required/blocked` para futura migração Elementor, sem writer.
- [x] T042 Integrar extractor como serviço read-only.
- [x] T043 Implementar runner ambiental temporário G-220.
- [x] T044 Gerar e validar package `0.4.0-smoke.1`.
- [x] T045 Executar `0.4.0-smoke.1` no WordPress de homologação.
- [x] T046 Validar fingerprint equal, zero changed posts, corpus 622→622, zero extractor errors e zero throwables.
- [x] T048 Desabilitar runner G-220 no build seguinte; não será promovido a RC.

Evidências:

- `g220-local-validation.md`;
- `package-smoke1.md`;
- `evidence/g220-smoke-20260915T221710Z.json`.

Resultado ambiental:

- WordPress `6.9.4` / PHP `8.5.10` / Elementor `4.1.0`;
- 622 posts processados;
- `editorial_fingerprint_equal=true`;
- `changed_posts_during_run=0`;
- `extractor_errors=0`;
- `throwables=0`;
- 21.969 fragments;
- Elementor readiness: 39 native / 505 projectable / 78 review_required / 0 blocked.

**Gate G-220: PASS — 2026-09-15.**

> Smoke manual completo de Summary/Classificação/Review permanece como regressão de lifecycle em G-250; não é usado para mascarar ou substituir o gate específico do extractor.

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
- [ ] T059C Executar smoke G-230 no WordPress de homologação.
- [ ] T059D Exigir 622 documentos nas duas passagens, zero errors/throwables, zero hash mismatch, zero canonical JSON mismatch e zero mutação editorial.

Evidências locais:

- `knowledge-document-contract-v1.md`;
- `g230-local-validation.md` — **10/10 PASS**;
- G-220 regression no mesmo package — **14/14 PASS**;
- `package-smoke2.md`.

Package `0.4.0-smoke.2` SHA-256:

`1466cd4fcd18120d0b2405bf04ec629230f23c2a2e759869a8123c45cedaf204`

**Gate G-230: IMPLEMENTED / LOCAL PASS — ENV SMOKE PENDING.**

## S005 — G-240 / Real Content Acceptance

- [ ] T060 Selecionar amostra representativa baseada no profiler/smokes.
- [ ] T061 Validar Elementor típico.
- [ ] T062 Validar Elementor complexo/widgets corporativos.
- [ ] T063 Validar Gutenberg.
- [ ] T064 Validar HTML legado.
- [ ] T065 Validar shortcode/tabela relevante.
- [ ] T066 Validar conteúdo vazio/corrompido.
- [ ] T067 Repetir build e comparar hashes.
- [ ] T068 Comparar conteúdo derivado com fonte por inspeção controlada.
- [ ] T069 Provar zero mutação editorial em amostra real.

**Gate G-240: BLOQUEADO por G-230.**

## S006 — G-245 / Elementor Normalization & Production Readiness

- [x] T080 Congelar `elementor-normalization-contract-v1.md`.
- [x] T081 Congelar `production-rollout-contract-v1.md`.
- [ ] T082 Implementar Production Preflight read-only para comparar homologação x produção.
- [ ] T083 Congelar matriz de compatibilidade WordPress/PHP/Elementor/plugins relevantes.
- [ ] T084 Implementar Projection Plan read-only por post.
- [ ] T085 Implementar `Elementor_Gateway` version-gated; writer desabilitado por default.
- [ ] T086 Definir journal transacional/rollback editorial.
- [ ] T087 Implementar dry-run sem writes.
- [ ] T088 Implementar stale-source guard por fingerprint bruto/modified.
- [ ] T089 Implementar migração explícita em lotes retomáveis, nunca em activation/update.
- [ ] T090 Executar canário em homologação e validar editor Elementor + frontend.
- [ ] T091 Testar rollback integral do canário.
- [ ] T092 Produzir runbook de instalação/upgrade/migration/rollback para produção.

**Gate G-245: PLANNED — writer editorial NÃO autorizado ainda.**

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
