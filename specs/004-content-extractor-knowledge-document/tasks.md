# Tarefas — SPEC-004 Content Extractor e Knowledge Document

## S001 — R-200 / Current State do corpus

- [x] T001 Confirmar baseline de entrada `0.3.0-rc.1` e fechamento da SPEC-003.
- [x] T002 Ler contrato editorial WordPress/Elementor existente.
- [x] T003 Inventariar prior art do KB2Ops `Content_Extractor`.
- [x] T004 Registrar riscos/limitações que não devem ser copiados automaticamente.
- [x] T005 Implementar profiler temporário read-only `0.4.0-profile.1`; package validado e com source parity 16/16.
- [x] T006 Medir posts por status e source kind.
- [x] T007 Medir validade/presença/tamanho de `_elementor_data`.
- [x] T008 Medir Gutenberg block names e distribuição.
- [x] T009 Medir HTML legado/plain/shortcodes e combinações mistas.
- [x] T010 Medir widget types Elementor sem exportar conteúdo.
- [x] T011 Medir shortcode tags sem executar shortcodes.
- [x] T012 Medir estrutura: headings/listas/tabelas/imagens/links/code.
- [x] T013 Medir candidatos a fallback renderizado.
- [x] T014 Comprovar fingerprint editorial before/after idêntico.
- [x] T015 Preservar JSON real sanitizado e análise do profiler.

**Gate R-200: PASS — 2026-09-15.**

## S002 — R-210 / Extraction Contract

- [x] T020 Fechar source precedence para Elementor/Gutenberg/legacy/mixed.
- [x] T021 Fechar contrato de traversal Elementor baseado no corpus.
- [x] T022 Fechar contrato de Gutenberg/blocks.
- [x] T023 Fechar contrato de HTML legado.
- [x] T024 Fechar política de shortcode allowlist/placeholder.
- [x] T025 Fechar política de render fallback e budget.
- [x] T026 Definir normalização de whitespace/boundaries.
- [x] T027 Definir warnings/códigos de integridade.
- [x] T028 Definir política de conteúdo vazio/corrompido.
- [x] T029 Congelar `Extraction Contract v1`.
- [x] T029A Incorporar direção editorial Elementor e portabilidade/produção no amendment `v1.1.0`.

Contratos:

- `extraction-contract-v1.md` — `FROZEN v1.0.0`;
- `extraction-contract-v1.1.md` — `FROZEN v1.1.0`.

**Gate R-210: PASS.**

## S003 — G-220 / Content Extractor determinístico

- [x] T030 Implementar detector read-only de origem/flags e budgets.
- [x] T031 Implementar adapter Elementor allowlisted (`text-editor`/`shortcode`) sem renderização.
- [x] T032 Implementar adapter Gutenberg estático sem `render_block()`.
- [x] T033 Implementar adapter Legacy HTML com `DOMDocument` opcional e fallback estrutural.
- [x] T034 Implementar normalizador estrutural/textual determinístico.
- [x] T035 Implementar fallback fail-soft conforme R-210/R-210.1.
- [x] T036 Avaliar cache in-request: não necessário no escopo atual; nenhuma persistência adicionada.
- [x] T037 Unit tests Elementor/Gutenberg/legacy.
- [x] T038 Unit tests conteúdo inválido/vazio/corrompido/oversize.
- [x] T039 Unit tests shortcodes/fallback/error isolation e zero-write.
- [x] T040 Provar repetibilidade da saída intermediária.
- [x] T041 Adicionar readiness `native/projectable/review_required/blocked` para futura migração Elementor, sem writer.
- [x] T042 Integrar extractor como serviço read-only e desabilitar o profiler R-200.
- [x] T043 Implementar runner ambiental temporário G-220 (`manage_options`, POST+nonce, agregados sem conteúdo/IDs).
- [x] T044 Gerar e validar package `0.4.0-smoke.1`: lint 20/20, JS PASS, ZIP integrity PASS, parity 9/9, unit 14/14 sobre ZIP extraído.
- [ ] T045 Instalar `0.4.0-smoke.1` no WordPress de homologação e executar smoke ambiental.
- [ ] T046 Validar fingerprint equal, zero changed posts, corpus unchanged, zero extractor errors e zero throwables.
- [ ] T047 Executar smoke manual das superfícies existentes Summary/Classificação/Review.
- [ ] T048 Remover/desabilitar runner G-220 após evidência ambiental aceita.

Evidências:

- `g220-local-validation.md`;
- `package-smoke1.md`.

Package SHA-256:

`3dad9f5f7c01e7970d314a0d0788756ad694cc9f3b9327a2834ead90b86e4c8f`

**Gate G-220: IMPLEMENTED / LOCAL PASS — fechamento ambiental pendente de T045–T047.**

## S004 — G-230 / Knowledge Document

- [ ] T050 Congelar schema version 1.
- [ ] T051 Implementar builder canônico.
- [ ] T052 Implementar ordenação/canonicalização determinística.
- [ ] T053 Implementar `source_hash` SHA-256.
- [ ] T054 Implementar `document_hash` SHA-256.
- [ ] T055 Testar estabilidade de hash para input idêntico.
- [ ] T056 Testar mudança de hash para alteração semântica relevante.
- [ ] T057 Testar que metadados/layout irrelevantes não contaminam documento quando contrato assim determinar.
- [ ] T058 Confirmar ausência de storage durável não autorizado.
- [ ] T059 Incorporar `elementor_compatibility` somente como proveniência/readiness, sem acoplar Knowledge Document ao editor.

**Gate G-230: BLOQUEADO até smoke ambiental de G-220.**

## S005 — G-240 / Real Content Acceptance

- [ ] T060 Selecionar amostra representativa baseada no profiler.
- [ ] T061 Validar Elementor típico.
- [ ] T062 Validar Elementor complexo/widgets corporativos.
- [ ] T063 Validar Gutenberg.
- [ ] T064 Validar HTML legado.
- [ ] T065 Validar shortcode/tabela relevante.
- [ ] T066 Validar conteúdo vazio/corrompido.
- [ ] T067 Repetir build e comparar hashes.
- [ ] T068 Comparar conteúdo derivado com fonte por inspeção controlada.
- [ ] T069 Provar zero mutação de `post_content`, `_elementor_data`, `post_status`, modified/publicação e revisões por leitura.

**Gate G-240: BLOQUEADO por G-230.**

## S006 — G-245 / Elementor Normalization & Production Readiness

- [x] T080 Congelar `elementor-normalization-contract-v1.md`.
- [x] T081 Congelar `production-rollout-contract-v1.md`.
- [ ] T082 Implementar Production Preflight read-only para comparar homologação x produção.
- [ ] T083 Congelar matriz de compatibilidade WordPress/PHP/Elementor/plugins relevantes.
- [ ] T084 Implementar Projection Plan read-only por post (`native/projectable/review_required/blocked`).
- [ ] T085 Implementar `Elementor_Gateway` version-gated usando Document lifecycle; writer desabilitado por default.
- [ ] T086 Definir/persistir journal transacional suficiente para rollback editorial.
- [ ] T087 Implementar dry-run de migração sem writes.
- [ ] T088 Implementar stale-source guard por hash/modified.
- [ ] T089 Implementar migração explícita em lotes retomáveis, sem activation/update automático.
- [ ] T090 Executar canário em homologação e validar abertura/save no editor Elementor + frontend.
- [ ] T091 Testar rollback integral do canário.
- [ ] T092 Produzir runbook de instalação/upgrade/migration/rollback para produção.

**Gate G-245: PLANNED — writer editorial NÃO autorizado ainda.**

## S007 — G-250 / Lifecycle, package e baseline final

- [ ] T100 Remover definitivamente profiler/runners temporários.
- [ ] T101 Gerar `0.4.0-rc.1` clean.
- [ ] T102 Source parity do package.
- [ ] T103 PHP lint/JS syntax/ZIP integrity.
- [ ] T104 Validar instalação limpa e update sobre baseline compatível.
- [ ] T105 Deactivate/activate no ambiente real sem job editorial implícito.
- [ ] T106 Smoke de regressão das SPECs 001–003.
- [ ] T107 Smoke de Content Extractor/Knowledge Document sem write.
- [ ] T108 Validar production preflight e comportamento fail-closed para versão Elementor não homologada.
- [ ] T109 Congelar baseline final SPEC-004.

**Gate G-250: NOT_RUN.**

## Regras constitucionais de continuidade

1. Content Extractor/Knowledge Document permanecem read-only.
2. Normalização editorial para Elementor é fluxo de migration separado e explícito.
3. Instalação/activation/update nunca converte posts automaticamente.
4. Nenhuma migration editorial em massa antes de dry-run, journal/rollback e canário.
5. Usuário/editor vence sobre migration atrasada: source divergente vira `STALE_SOURCE`.
6. IA não é usada para reparar parsing nem para writer inicial de Elementor.
7. Runner de smoke é temporário e não chega ao RC/produção.
8. Nenhuma etapa posterior compensa lacuna de segurança da anterior.
