# SPEC-004 — Content Extractor e Knowledge Document

**Status:** REABERTA EM DISCOVERY READ-ONLY — baseline G-250 permanece CLOSED; investigação R-260 aberta por evidência G-590 RC5  
**Baseline de entrada:** `0.3.0-rc.1`  
**Baseline consolidada em `main`:** `0.4.0-acceptance.12` / Knowledge Document `2.1.0`  
**Pré-requisito:** SPEC-003 concluída — PASS.

## 1. Problema

Busca lexical, busca semântica, IA assistida, chunks, embeddings e normalização editorial precisam consumir uma representação semântica confiável do conteúdo. Usar diretamente HTML legado, `_elementor_data` ou markup de Blocks como conhecimento introduz ruído, instabilidade e detalhes de apresentação.

A fonte editorial pertence ao WordPress. Após ADR-004-001, o destino editorial canônico futuro é `WP_Post.post_content` + WordPress Core Blocks. Elementor permanece source adapter legado temporário durante a transição.

## 2. Resultado esperado

### Content Extractor read-only

- identificar a fonte editorial efetiva;
- extrair conteúdo semântico de Elementor legado, Gutenberg/Core Blocks, HTML legado e plain text;
- preservar ordem, boundaries e estrutura relevante;
- não executar código arbitrário;
- falhar de forma isolada/fail-soft;
- não persistir resultado.

### Knowledge Document

- projeção canônica in-memory;
- schema versionado;
- seções/blocos em ordem;
- `source_hash` e `document_hash` determinísticos;
- proveniência, warnings e readiness explícitos;
- sem storage durável como fonte editorial.

### Canonical Block Normalization / Production Readiness

A convergência futura é para **WordPress Core Blocks**, por migration administrativa separada e governada:

- nunca em activation/update;
- projection plan read-only;
- allowlist comprovada por corpus real;
- serialização in-memory antes de qualquer persistência;
- round-trip semântico;
- dry-run;
- stale-source guard;
- journal/rollback;
- lock exclusivo;
- canário;
- batches retomáveis;
- autorização explícita para writer real.

## 3. Invariantes

Extração/Knowledge Document/Block Projection não podem:

- escrever em `post_content` ou `_elementor_data` sem gate específico;
- alterar status/data/revisões/publicação;
- executar shortcodes/widgets/dynamic blocks arbitrariamente;
- depender de IA/Foundry/vetor/rede externa para normalização;
- transformar o plugin Gutenberg em dependência de produção;
- remover Elementor automaticamente;
- inventar referência de mídia ausente;
- achatar estrutura complexa silenciosamente.

## 4. Estratégia de leitura

1. `WP_Post`/APIs nativas;
2. flags independentes de origem;
3. Elementor legado válido via traversal allowlisted;
4. Gutenberg/Core Blocks via estrutura estática;
5. Legacy HTML como adapter de primeira classe;
6. plain text;
7. fallback adicional somente com autorização baseada em evidência.

## 5. Gates concluídos R-200 → G-240

R-200/R-210/G-220/G-230/G-240 estão concluídos. KD 2.1.0 possui full-corpus técnico PASS e aceite humano 8/8 PASS com zero mutação editorial.

Contratos/evidências principais:

- `knowledge-document-contract-v2.1.0.md`;
- `evidence/kd-v21-smoke-summary-20260916T172538Z.json`;
- `evidence/g240-kd21-acceptance-20260916T193359Z.json`.

## 6. G-245 — Rebaseline arquitetural

ADR aceita:

`adr/ADR-004-001-wordpress-core-blocks-canonical-editorial-target.md`.

Decisão:

- Core Blocks são destino editorial futuro;
- plugin Gutenberg não é dependência;
- Elementor é reader legado temporário;
- nenhum novo writer usa `_elementor_data`;
- antigos gates Elementor permanecem como memória/infra defensiva reutilizável;
- T087C writer Elementor foi cancelado antes de implementação.

## 7. Investimentos defensivos preservados

- Journal/rollback;
- durable storage;
- stale-source guard;
- dry-run;
- batch planning;
- exclusive lock;
- readiness/canary methodology;
- production runbook.

T083B Durable Journal Storage possui PASS ambiental.

## 8. T090 — Block Projection v1.0

**PASS LOCAL / READ-ONLY.**

Allowlist v1:

- heading → `core/heading`;
- paragraph → `core/paragraph`;
- code → `core/code`;
- list → `core/list` + `core/list-item`;
- table simples → `core/table`.

Resultado local: 23/23 assertions PASS + lint PASS.

## 9. T091 — Full-corpus Block Projection v1.0

**PASS AMBIENTAL.**

Ambiente observado:

- WordPress 6.9.4;
- PHP 8.5.10;
- Elementor 4.1.0;
- corpus 623 posts;
- plugin Gutenberg dependency false.

Duas passagens 623/623, zero errors/throwables/hash mismatches/safety violations e fingerprint editorial before/after idêntico.

Plan status:

- projectable 347;
- review_required 269;
- native_noop 4;
- not_applicable 3.

Warnings:

- KD review required 233;
- image unsupported 40;
- table span review 32;
- quote unsupported 8.

Evidência: `evidence/g245-block-projection-t091-20260917T172515Z.json`.

## 10. T092 — Block Projection v1.1

**PASS LOCAL / READ-ONLY.**

Contrato: `block-projection-contract-v1.1.md`.

Mudança:

- `quote` → `core/quote`;
- Block Projection schema → `1.1.0`;
- image permanece review porque o KD 2.1 não preserva referência canônica de mídia suficiente para `core/image`;
- table spans permanecem review.

Validação: 25/25 assertions PASS + lint PASS.

## 11. T093 — Full-corpus v1.1 + diagnóstico KD

**IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE.**

O runner read-only passa a exportar métricas agregadas de:

- Knowledge Document readiness;
- Knowledge Document reasons para review/not_ready;
- source kind × plan status;
- warnings;
- projected block names;
- determinismo/fingerprint.

Não exporta conteúdo editorial nem post IDs e não serializa/persiste Blocks.

Pacote: `0.4.0-g245-block-projection-t093.1`.

## 12. Próximos gates

- T093: homologação full-corpus v1.1;
- T094: contrato de serialização Core Blocks in-memory orientado pela evidência T093;
- T095: semantic round-trip `projection → serialize → parse`, sem persistência;
- T096: generalizar dry-run/journal/stale/lock/batches para Block Migration;
- T097: canário de 1 artigo + rollback real com Authorization Pack;
- T098: batches homologados;
- T099: inventário de dependência residual Elementor e gate de retirada;
- G-250: Lifecycle / RC.

## 13. Fora de escopo atual

- índice lexical;
- chunks persistidos;
- embeddings;
- MariaDB Vector;
- Azure Foundry/RAG;
- ranking/telemetria de busca;
- writer editorial automático;
- remoção automática de Elementor;
- plugin Gutenberg como runtime dependency.

## 14. Definition of Done

A SPEC-004 termina somente quando Content Extractor/KD permanecerem confiáveis, a normalização para Core Blocks estiver governada por projection/serialization/round-trip/dry-run/journal/stale/lock/canary/rollback, a dependência residual Elementor estiver conhecida e os requisitos aplicáveis de `docs/DEFINITION-OF-DONE.md` forem satisfeitos.


## 15. Fechamento da SPEC-004 — 2026-09-18

A SPEC-004 foi encerrada após T100D PASS ambiental, T100E-E6 PASS ambiental, T100E-E7 PASS/CLOSED e G-250 Lifecycle/RC1 PASS ambiental.

RC final limpo: `0.4.0-spec004-rc2`, SHA-256 `ac25c2ffd4a0ae2250fa2ce1a07bf07b4cad8a24030e31f12c78189e61e7506b`.

O fechamento não autoriza migração em massa nem writer autônomo. O controle de autorização editorial permanece explícito e post-scoped. O download de Authorization Pack é uma implementação transitória; uma futura execução integrada à Workspace deve manter confirmação humana explícita, capability, nonce, dry-run, stale-source guard, journal, lock e rollback.


## 16. Reabertura controlada R-260 — Legacy Numbered Hierarchy Fidelity

**Data:** 2026-09-24  
**Motivo:** evidência ambiental G-590 RC5.

A baseline de produção da SPEC-004 continua válida e fechada em G-250. A reabertura é estritamente investigativa e não invalida os PASS anteriores.

Evidência nova mostrou fragmentos `legacy_html` semanticamente semelhantes a headings numerados chegando como `kind=paragraph`, com `hierarchy_source=numbering_inferred`, inclusive sinais determinísticos de profundidade > 1 sem heading contextual.

Exemplos observados incluem:
- `5.1 INTRODUÇÃO`;
- `5.2 POSTURA NO ATENDIMENTO`;
- `6.1 AVALIAÇÃO DA MONITORIA DE QUALIDADE - 1º NÍVEL`.

### R-260 — objetivo

Determinar, sem write editorial:
- distribuição real por source kind/confidence/post;
- quantos sinais são índice/sumário duplicado vs. headings editoriais reais;
- impacto potencial em KD, Block Projection e Search;
- owner arquitetural correto da recuperação;
- critérios determinísticos que não inventem hierarquia.

### Proibições

R-260 não autoriza:
- alteração automática de `post_content`;
- migration;
- writer;
- reclassificação massiva de fragments;
- mudança de KD schema;
- mudança de Search ranker;
- heurística baseada somente em caixa alta/tamanho.

Somente após diagnóstico ambiental completo será decidido se a correção pertence à SPEC-004 ou se deve permanecer como projeção derivada de consumidor.
