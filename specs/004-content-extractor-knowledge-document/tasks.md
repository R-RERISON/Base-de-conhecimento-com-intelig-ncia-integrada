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

**Gate G-220: PASS — 2026-09-15.**

## S004 — G-230 / Knowledge Document v1

- [x] T050–T059D Schema v1, canonical JSON, hashes, testes e smoke ambiental em duas passagens.

Evidências:

- `knowledge-document-contract-v1.md`;
- `g230-local-validation.md`;
- `g230-smoke-analysis.md`;
- `evidence/g230-smoke-20260915T233450Z.json`.

**Gate G-230/v1: PASS de determinismo — 2026-09-15.**

> O G-240 posterior demonstrou que determinismo não bastava: o schema v1 perdeu relações estruturais. v1 permanece como evidência histórica, mas está `SUPERSEDED_FOR_AI`.

## S005 — G-240 / Real Content Acceptance

### G-240 v1

- [x] T060 Congelar contrato de aceite e amostra determinística.
- [x] T061–T066 Revisar os 8 slots reais em homologação.
- [x] T067 Repetibilidade por dupla reconstrução.
- [x] T068 Comparação lado a lado fonte × Knowledge Document.
- [x] T069 Stale guard/fingerprint/zero persistência.
- [x] T069A Executar `0.4.0-acceptance.1` e retornar evidência.
- [x] T069B Analisar resultado: 8/8 revisados, 0/8 aprovados, 7/8 `structure_loss`.

Evidências:

- `evidence/g240-acceptance-20260916T085721Z.json`;
- `g240-failure-analysis-20260916.md`.

**Gate G-240 v1: FAIL CONTROLADO — perda estrutural.**

Achado positivo: todos os 8 casos preservaram cobertura textual, ordem e ausência de texto inventado. A falha concentrou-se na representação estrutural.

### Remediação estrutural / Knowledge Document v2

- [x] T070 Congelar `knowledge-document-contract-v2.md` (`2.0.0`).
- [x] T071 Introduzir `Semantic_Structure` e `heading_path`.
- [x] T072 Preservar listas: `ul/ol`, profundidade, item pai/filho e ordem.
- [x] T073 Preservar tabelas: linhas, células, `header|data`, `rowspan`, `colspan` e caption.
- [x] T074 Evoluir `Knowledge_Document` para schema `2.0.0` com `blocks[]`.
- [x] T075 Adicionar `ai_readiness` calculado (`candidate_ready|review_required|not_ready|not_applicable`).
- [x] T076 Remover `acceptable_for_knowledge_use` do veredito humano; manter apenas critérios observáveis.
- [x] T077 Congelar os mesmos 8 posts do G-240 v1 como amostra A/B da remediação.
- [x] T078 Adicionar teste unitário v2 para heading path, lista aninhada, tabela, hashes e AI readiness.
- [x] T079 Implementar smoke ambiental v2 em duas passagens sobre todo o corpus.
- [x] T079A Implementar acceptance v2 lado a lado com render semântico de `blocks[]`.
- [x] T079B Gerar/validar package `0.4.0-acceptance.2`.
- [x] T079C Executar `Validação KD v2` com `acceptance.2`: determinismo/zero-write PASS; 82/622 `structure_incomplete`.
- [x] T079C1 Versionar `evidence/kd-v2-smoke-20260916T100412Z.json` e bloquear G-240 v2.
- [x] T079C2 Instrumentar diagnóstico agregado e gerar `0.4.0-acceptance.3` sem relaxar parser/schema/gate.
- [x] T079C3 Executar `acceptance.3`: 82/622 reproduzidos; 62 legacy_html, 14 elementor, 5 mixed, 1 gutenberg; mismatches concentrados em headings/lists/list_items/tables.
- [x] T079C3A Versionar `evidence/kd-v2-smoke-20260916T101517Z.json`.
- [x] T079C4A Comprovar risco de colisão de IDs estruturais locais (`list-0`, `table-0`, item IDs) entre parciais Elementor/Gutenberg/mixed.
- [x] T079C4B Implementar namespace determinístico de IDs estruturais nos merges, sem alterar `Semantic_Structure`, schema `2.0.0`, parser Legacy ou critério do gate.
- [x] T079C4C Adicionar regressão `spec004-structural-id-namespace.php` para duas listas/tabelas independentes.
- [x] T079C4D Gerar/validar package `0.4.0-acceptance.4` para experimento full-corpus controlado.
- [ ] T079C5 Executar `Validação KD v2` com `acceptance.4` e medir redução de `structure_incomplete`, especialmente Elementor/mixed/Gutenberg.
- [ ] T079C6 Se Legacy permanecer divergente, corrigir somente causa Legacy comprovada e repetir full-corpus até `structure_incomplete=0`.
- [ ] T079D Reexecutar os mesmos 8 casos no `Aceitação G-240 v2` somente após full-corpus PASS.
- [ ] T079E Fechar G-240 somente se os quatro critérios humanos passarem, sem stale/repeatability failure e com limitações refletidas em `ai_readiness`.

### Evidência `acceptance.3`

- ambiente: WordPress `6.9.4`, PHP `8.5.10`, Elementor `4.1.0`, DOM disponível;
- 622/622 documentos em ambas as passagens;
- zero errors/throwables/hash mismatch/canonical JSON mismatch;
- fingerprint editorial igual e zero posts alterados;
- `structure_incomplete=82` nas duas passagens;
- por source: 62 legacy_html, 14 elementor, 5 mixed, 1 gutenberg;
- mismatches: headings em 47 docs; lists em 47; list_items em 40; tables em 8;
- `ai_readiness`: 495 candidate_ready, 82 not_ready, 43 review_required, 2 not_applicable.

**Gate G-240: FAIL CONTROLADO / STRUCTURAL REMEDIATION ACTIVE.**

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

**Gate G-245: BLOCKED por G-240 — writer editorial NÃO autorizado.**

## S007 — G-250 / Lifecycle, package e baseline final

- [ ] T100 Remover definitivamente profiler/runners/acceptance tools temporários.
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
8. Determinismo sem fidelidade estrutural não é aceite de conhecimento.
9. Nenhuma etapa posterior compensa lacuna de segurança ou semântica da anterior.
