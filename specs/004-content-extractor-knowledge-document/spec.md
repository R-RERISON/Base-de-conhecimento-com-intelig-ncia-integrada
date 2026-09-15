# SPEC-004 — Content Extractor e Knowledge Document

**Status:** ATIVA — R-200 PASS / R-210 PASS / G-220 PASS / G-230 LOCAL PASS  
**Baseline de entrada:** `0.3.0-rc.1`  
**Pré-requisito:** SPEC-003 concluída — PASS.  
**Contratos ativos:** `extraction-contract-v1.1.md` e `knowledge-document-contract-v1.md`.

## 1. Problema

Busca lexical, busca semântica, IA assistida, chunks e embeddings precisam consumir uma representação semântica confiável do conteúdo editorial. Usar diretamente HTML, `_elementor_data` ou blocos serializados como conhecimento introduz ruído, instabilidade, detalhes de layout e risco de execução de componentes terceiros.

A fonte editorial continua sendo WordPress/Elementor. Esta SPEC cria uma **projeção derivada e reconstruível**, sem transformar o plugin em CMS.

## 2. Resultado esperado

### Content Extractor read-only

- identifica a fonte editorial efetiva;
- extrai conteúdo semântico de Elementor, Gutenberg/blocos e HTML/conteúdo legado;
- preserva ordem e boundaries;
- não executa código arbitrário;
- falha de forma isolada/fail-soft;
- não persiste resultado.

### Knowledge Document

- projeção canônica in-memory;
- schema versionado;
- seções em ordem;
- `source_hash` e `document_hash` determinísticos;
- proveniência/warnings/readiness;
- sem HTML/JSON bruto;
- sem storage durável nesta SPEC.

### Elementor Normalization / Production Readiness

Elementor é o padrão editorial futuro da equipe, mas a convergência do legado ocorre por migration administrativa separada:

- nunca em activation/update;
- preflight;
- dry-run;
- stale-source guard;
- journal/rollback;
- canário;
- batches retomáveis;
- writer futuro version-gated.

## 3. Invariantes

Extração/Knowledge Document nunca podem:

- escrever em `post_content` ou `_elementor_data`;
- alterar status/data/revisões/publicação;
- executar shortcodes/widgets/dynamic blocks arbitrariamente;
- depender de IA/Foundry/vetor/rede externa;
- persistir projeções sem gate específico;
- migrar conteúdo para Elementor implicitamente.

## 4. Estratégia de leitura

1. `WP_Post`/APIs nativas;
2. flags independentes de origem;
3. Elementor válido via traversal allowlisted;
4. Gutenberg via estrutura estática;
5. Legacy HTML como adapter de primeira classe;
6. plain text;
7. fallback renderizado somente com autorização posterior baseada em evidência.

## 5. R-200 — evidência do corpus

Corpus: 622 posts.

Descobertas históricas:

- forte predominância Legacy HTML;
- Elementor presente em 80 posts no profiler, 39 JSON válidos / 41 inválidos;
- Gutenberg residual, porém real;
- shortcodes com falsos positivos textuais por colchetes;
- budgets observados abaixo do soft limit de 256 KiB.

R-200 foi executado read-only com fingerprint idêntico e zero mutação.

## 6. R-210 — Extraction Contract

Contratos:

- `extraction-contract-v1.md`;
- `extraction-contract-v1.1.md`.

Definem source selection, adapters, shortcodes, fallback, warnings, budgets e direção editorial Elementor sem autorizar writer.

## 7. G-220 — Content Extractor

**PASS ambiental em 2026-09-15.**

Evidência: `evidence/g220-smoke-20260915T221710Z.json`.

Ambiente:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- Elementor `4.1.0`;
- 622 posts.

Resultado:

- fingerprint before/after idêntico;
- zero posts alterados;
- zero extractor errors;
- zero throwables;
- 21.969 fragments;
- readiness Elementor: 39 native / 505 projectable / 78 review_required / 0 blocked.

## 8. G-230 — Knowledge Document

Contrato: `knowledge-document-contract-v1.md` — v1.0.0.

Implementação:

- `Canonical_JSON`;
- `Knowledge_Document`;
- schema `1.0.0`;
- canonicalização determinística;
- `source_hash` semântico;
- `document_hash` semântico da projeção;
- URL/data preservadas como envelope operacional fora do hash;
- `elementor_compatibility` apenas como proveniência/readiness;
- nenhum storage durável.

Validação local:

- G-230: **10/10 PASS**;
- regressão G-220 no mesmo package: **14/14 PASS**.

Package ambiental atual: `0.4.0-smoke.2`.

SHA-256:

`1466cd4fcd18120d0b2405bf04ec629230f23c2a2e759869a8123c45cedaf204`

G-230 fecha apenas após duas passagens no corpus real com zero mismatch de hashes/JSON e zero mutação editorial.

## 9. G-240 — Real Content Acceptance

Após G-230:

`post real → extractor → Knowledge Document → hashes → inspeção controlada`

A amostra deverá cobrir Elementor, Legacy HTML, Gutenberg, shortcodes/tabelas, conteúdo inválido/corrompido e casos `review_required`.

## 10. G-245 — Elementor Normalization & Production Readiness

Contratos:

- `elementor-normalization-contract-v1.md`;
- `production-rollout-contract-v1.md`.

Nenhum writer está autorizado ainda.

Produção exigirá diferença explícita entre:

- upgrade de código;
- migration de schema/config própria do plugin;
- migration editorial Elementor.

A migration editorial é sempre explícita e reversível.

## 11. G-250 — Lifecycle

Antes do RC:

- remover runners/profile temporários;
- package clean `0.4.0-rc.1`;
- source parity/lint/JS/ZIP;
- instalação limpa + update;
- deactivate/activate;
- regressão SPECs 001–003;
- smoke extractor/document;
- production preflight.

## 12. Fora de escopo atual

- índice lexical;
- chunks persistidos;
- embeddings;
- MariaDB Vector;
- Azure Foundry/RAG;
- ranking/telemetria de busca;
- writer editorial automático;
- IA para reparar parsing/migração.

## 13. Gates

- R-200: **PASS**.
- R-210: **PASS**.
- G-220: **PASS**.
- G-230: **IMPLEMENTED / LOCAL PASS — ENV SMOKE PENDING**.
- G-240: **BLOCKED por G-230**.
- G-245: **PLANNED — writer não autorizado**.
- G-250: **NOT_RUN**.

## 14. Definition of Done

A SPEC-004 termina apenas quando o extractor e o Knowledge Document estiverem aceitos em conteúdo real, a promoção para produção estiver governada, qualquer normalização Elementor estiver separada/segura e houver evidência objetiva de zero mutação editorial nos fluxos read-only.
