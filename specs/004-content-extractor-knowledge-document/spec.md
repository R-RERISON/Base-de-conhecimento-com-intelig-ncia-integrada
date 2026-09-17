# SPEC-004 — Content Extractor e Knowledge Document

**Status:** ATIVA — R-200 PASS / R-210 PASS / G-220 PASS / G-230 PASS / G-240 PASS-CLOSED / G-245 IN PROGRESS  
**Baseline de entrada:** `0.3.0-rc.1`  
**Baseline consolidada em `main`:** `0.4.0-acceptance.12` / Knowledge Document `2.1.0`  
**Merge G-240:** `32a696386bf2ab5574d4d7725db78636fa51f36c`  
**Pré-requisito:** SPEC-003 concluída — PASS.

## 1. Problema

Busca lexical, busca semântica, IA assistida, chunks e embeddings precisam consumir uma representação semântica confiável do conteúdo editorial. Usar diretamente HTML, `_elementor_data` ou blocos serializados como conhecimento introduz ruído, instabilidade, detalhes de layout e risco de execução de componentes terceiros.

A fonte editorial continua sendo WordPress/Elementor. Esta SPEC cria uma **projeção derivada, determinística e reconstruível**, sem transformar o plugin em CMS.

## 2. Resultado esperado

### Content Extractor read-only

- identifica a fonte editorial efetiva;
- extrai conteúdo semântico de Elementor, Gutenberg/blocos, HTML legado e plain text;
- preserva ordem, boundaries e estrutura relevante;
- não executa código arbitrário;
- falha de forma isolada/fail-soft;
- não persiste resultado.

### Knowledge Document

- projeção canônica in-memory;
- schema versionado;
- seções/blocos em ordem;
- `source_hash` e `document_hash` determinísticos;
- proveniência, warnings e readiness explícitos;
- sem HTML/JSON bruto como fonte de conhecimento;
- sem storage durável nesta etapa.

### Elementor Normalization / Production Readiness

Elementor é o padrão editorial futuro da equipe, mas a convergência do legado ocorre por migration administrativa separada e governada:

- nunca em activation/update;
- preflight;
- matriz de compatibilidade;
- Projection Plan read-only;
- gateway version-gated;
- dry-run;
- stale-source guard;
- journal/rollback;
- canário;
- batches retomáveis;
- autorização explícita para writer real.

## 3. Invariantes

Extração/Knowledge Document nunca podem:

- escrever em `post_content` ou `_elementor_data`;
- alterar status/data/revisões/publicação;
- executar shortcodes/widgets/dynamic blocks arbitrariamente;
- depender de IA/Foundry/vetor/rede externa;
- persistir projeções sem gate específico;
- migrar conteúdo para Elementor implicitamente.

G-245 também não autoriza escrita por existência de preflight, plano ou gateway. Writer/migration permanecem disabled-by-default até subgates e autorização explícita.

## 4. Estratégia de leitura

1. `WP_Post`/APIs nativas;
2. flags independentes de origem;
3. Elementor válido via traversal allowlisted;
4. Gutenberg via estrutura estática;
5. Legacy HTML como adapter de primeira classe;
6. plain text;
7. fallback adicional somente com autorização posterior baseada em evidência.

## 5. R-200 — evidência do corpus

Corpus: 622 posts.

Achados principais:

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

**PASS ambiental.**

Evidência: `evidence/g220-smoke-20260915T221710Z.json`.

Ambiente homologado:

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
- readiness Elementor inicial: 39 native / 505 projectable / 78 review_required / 0 blocked.

## 8. G-230 — Knowledge Document determinístico

G-230/v1 fechou determinismo e canonicalização. O schema v1 foi posteriormente superseded para necessidades de estrutura/hierarquia do gate G-240.

Garantias preservadas:

- canonicalização determinística;
- `source_hash` semântico;
- `document_hash` canônico;
- repetibilidade;
- zero storage durável;
- zero mutação editorial.

## 9. G-240 — Real Content Acceptance

### Histórico controlado

- G-240/v1: FAIL CONTROLADO por perda estrutural;
- G-240/v2 / KD `2.0.1`: full-corpus técnico PASS, porém aceite humano FAIL CONTROLADO por hierarchy fidelity;
- KD `2.1.0` / `0.4.0-acceptance.12`: correção conservadora de relações hierárquicas e fechamento do gate.

### Estado final

**G-240: PASS / CLOSED.**

Full-corpus:

- corpus 622 → 622;
- duas passagens 622/622;
- errors 0;
- throwables 0;
- hash mismatches 0;
- canonical JSON mismatches 0;
- `structure_incomplete` 0;
- `not_ready` 0;
- zero mutação editorial.

Aceite humano fixo:

- 8/8 coverage;
- 8/8 order;
- 8/8 no invented text;
- 8/8 structure preserved;
- 8/8 human_pass;
- 8/8 gate_pass;
- stale/repeatability/sample mismatch = 0.

Contratos/evidências principais:

- `knowledge-document-contract-v2.1.0.md`;
- `evidence/kd-v21-smoke-summary-20260916T172538Z.json`;
- `evidence/g240-kd21-acceptance-20260916T193359Z.json`.

## 10. G-245 — Elementor Normalization & Production Readiness

**Status: IN PROGRESS em branch dedicada `spec004-g245-production-readiness`; PR #4 DRAFT.**

O trabalho de G-245 não faz parte da baseline G-240 promovida para `main`.

Production Preflight T080 já foi executado read-only em homologação:

- blockers: 0;
- itens `review_required`: shortcodes legados sem handler e loopback não testado no preflight v1;
- corpus 622 → 622;
- fingerprint editorial preservado;
- `writer_allowed=false`;
- `migration_execution_allowed=false`.

A sequência restante deve continuar incremental, read-only primeiro, e só avançar para mutação após rollback e autorização explícita.

## 11. G-250 — Lifecycle / RC

**NOT_RUN.**

Antes do RC:

- remover/desabilitar runners temporários;
- package clean;
- source parity/lint/JS/ZIP;
- instalação limpa + update;
- deactivate/activate;
- regressão SPECs 001–003;
- smoke extractor/document;
- production preflight;
- documentação/changelog atualizados.

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
- G-230: **PASS**.
- G-240: **PASS / CLOSED**.
- G-245: **IN PROGRESS — branch/PR draft; writer não autorizado**.
- G-250: **NOT_RUN**.

## 14. Definition of Done

A SPEC-004 termina apenas quando o Content Extractor e o Knowledge Document estiverem aceitos em conteúdo real, a promoção para produção estiver governada, qualquer normalização Elementor estiver separada/segura e houver evidência objetiva de zero mutação editorial nos fluxos read-only, além do fechamento dos requisitos aplicáveis de `docs/DEFINITION-OF-DONE.md`.
