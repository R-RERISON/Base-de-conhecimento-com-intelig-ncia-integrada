# Tarefas — SPEC-004 Content Extractor e Knowledge Document

## S001 — R-200 / Current State do corpus

- [x] T001–T015 Descoberta ambiental, profiler, formatos, structures, budgets, fallback e zero mutação.

**Gate R-200: PASS — 2026-09-15.**

## S002 — R-210 / Extraction Contract

- [x] T020–T029 Congelar source precedence, adapters, shortcodes, fallback, normalização, warnings e budgets.
- [x] T029A Incorporar direção editorial Elementor e portabilidade/produção no amendment `v1.1.0`.

**Gate R-210: PASS.**

## S003 — G-220 / Content Extractor determinístico

- [x] T030–T048 Implementação, testes, smoke ambiental e desativação do runner anterior.

Evidências:

- `g220-local-validation.md`;
- `package-smoke1.md`;
- `g220-smoke-analysis.md`;
- `evidence/g220-smoke-20260915T221710Z.json`.

**Gate G-220: PASS — 2026-09-15.**

## S004 — G-230 / Knowledge Document

- [x] T050–T059D Schema, canonical JSON, hashes, testes e smoke ambiental em duas passagens.

Evidências:

- `knowledge-document-contract-v1.md`;
- `g230-local-validation.md`;
- `package-smoke2.md`;
- `g230-smoke-analysis.md`;
- `evidence/g230-smoke-20260915T233450Z.json`.

**Gate G-230: PASS — 2026-09-15.**

## S005 — G-240 / Real Content Acceptance

- [x] T060 Congelar contrato `real-content-acceptance-contract-v1.md` e amostra determinística por slots.
- [ ] T061 Validar Elementor típico por inspeção humana.
- [ ] T062 Validar Elementor/Mixed complexo por inspeção humana.
- [ ] T063 Validar Gutenberg por inspeção humana.
- [ ] T064 Validar HTML legado típico/complexo por inspeção humana.
- [ ] T065 Validar shortcode/tabela relevante por inspeção humana.
- [ ] T066 Validar vazio/corrompido/review_required quando disponíveis.
- [x] T067 Incorporar repetibilidade no relatório final por dupla reconstrução do Knowledge Document.
- [x] T068 Implementar ferramenta read-only de comparação lado a lado fonte editorial × Knowledge Document.
- [x] T069 Implementar stale guard, revalidação da seleção determinística e fingerprint before/after sem persistência.
- [ ] T069A Executar `0.4.0-acceptance.1` em homologação e retornar JSON de evidência.
- [ ] T069B Exigir todos os slots disponíveis revisados e aprovados, zero stale, zero selection mismatch, zero repeatability failure e zero mutação editorial.

Contrato:

- `real-content-acceptance-contract-v1.md` — `FROZEN v1.0.0`.

Tooling:

- build temporário `0.4.0-acceptance.1`;
- menu `Base de Conhecimento → Aceitação G-240`;
- conteúdo da fonte só é mostrado localmente no wp-admin;
- JSON de evidência não exporta corpo, título ou URL;
- post ID é exportado apenas para rastreabilidade da amostra;
- seleção é recalculada no submit para impedir substituição/omissão silenciosa de slots.

**Gate G-240: TOOLING READY / HUMAN ACCEPTANCE PENDING.**

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
7. Runners/acceptance tools são temporários e não chegam ao RC/produção.
8. Nenhuma etapa posterior compensa lacuna de segurança da anterior.
