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

Evidências:

- `evidence/r200-content-profile-20260915T213342Z.json`;
- `r200-corpus-analysis.md`.

Package executado: `package-profile1.md` — SHA-256 `eeae2f7a5c37dead27bd21f486bea7a64b75d510392a35b742ec6eb338a59bdd`.

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

Contrato: `extraction-contract-v1.md` — `FROZEN v1.0.0`.

**Gate R-210: PASS — contrato congelado; nenhum runtime permanente implementado nesta etapa.**

## S003 — G-220 / Content Extractor determinístico

- [ ] T030 Implementar detector de source kind.
- [ ] T031 Implementar adapter Elementor.
- [ ] T032 Implementar adapter Gutenberg.
- [ ] T033 Implementar adapter HTML legado.
- [ ] T034 Implementar normalizador estrutural/textual.
- [ ] T035 Implementar fallback controlado apenas conforme R-210.
- [ ] T036 Adicionar cache somente in-request se necessário.
- [ ] T037 Unit tests Elementor/Gutenberg/legacy.
- [ ] T038 Unit tests conteúdo inválido/vazio/corrompido.
- [ ] T039 Unit tests shortcodes/fallback/error isolation.
- [ ] T040 Provar repetibilidade do extractor.

**Gate G-220: READY — próximo gate; implementação ainda não iniciada nesta branch.**

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

**Gate G-230: BLOQUEADO por G-220.**

## S005 — G-240 / Real Content Acceptance

- [ ] T060 Selecionar amostra representativa baseada no profiler.
- [ ] T061 Validar Elementor típico.
- [ ] T062 Validar Elementor complexo/widgets corporativos.
- [ ] T063 Validar Gutenberg se presente.
- [ ] T064 Validar HTML legado.
- [ ] T065 Validar shortcode/tabela relevante.
- [ ] T066 Validar conteúdo vazio/corrompido quando presente.
- [ ] T067 Repetir build e comparar hashes.
- [ ] T068 Comparar conteúdo derivado com fonte por inspeção controlada.
- [ ] T069 Provar zero mutação de `post_content`, `_elementor_data`, `post_status`, modified/publicação e revisões por leitura.

**Gate G-240: BLOQUEADO por G-230.**

## S006 — G-250 / Lifecycle e baseline

- [ ] T070 Remover profiler/runners temporários.
- [ ] T071 Gerar `0.4.0-rc.1` clean.
- [ ] T072 Source parity do package.
- [ ] T073 PHP lint/JS syntax/ZIP integrity.
- [ ] T074 Deactivate/activate no ambiente real.
- [ ] T075 Smoke de regressão das SPECs 001–003.
- [ ] T076 Smoke de Content Extractor/Knowledge Document sem write.
- [ ] T077 Congelar baseline final SPEC-004.

**Gate G-250: NOT_RUN.**

## Regra

R-200 e R-210 estão concluídos. O próximo passo é G-220, seguindo estritamente `extraction-contract-v1.md`. Nenhuma decisão de runtime pode contornar o contrato por conveniência, renderização arbitrária ou execução de componentes terceiros.
