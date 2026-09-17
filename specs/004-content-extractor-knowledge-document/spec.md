# SPEC-004 — Content Extractor e Knowledge Document

**Status:** ATIVA — R-200 PASS / R-210 PASS / G-220 PASS / G-230 PASS / G-240 PASS-CLOSED / G-245 REBASELINED IN PROGRESS  
**Baseline de entrada:** `0.3.0-rc.1`  
**Baseline consolidada em `main`:** `0.4.0-acceptance.12` / Knowledge Document `2.1.0`  
**Merge G-240:** `32a696386bf2ab5574d4d7725db78636fa51f36c`  
**Pré-requisito:** SPEC-003 concluída — PASS.  
**ADR vigente:** `adr/ADR-004-001-wordpress-core-blocks-canonical-editorial-target.md`.

## 1. Problema

Busca lexical, busca semântica, IA assistida, chunks, embeddings e normalização editorial precisam consumir uma representação semântica confiável do conteúdo. Usar diretamente HTML legado, `_elementor_data` ou blocos serializados como conhecimento introduz ruído, instabilidade e detalhes de apresentação.

A fonte editorial continua pertencendo ao WordPress. A arquitetura-alvo editorial passa a ser **`WP_Post.post_content` com WordPress Core Blocks**, usando apenas APIs estáveis do WordPress Core. Elementor permanece suportado como fonte legada durante a transição, não como destino futuro.

Esta SPEC cria projeções derivadas, determinísticas e reconstruíveis, sem transformar o plugin em CMS ou editor paralelo.

## 2. Resultado esperado

### Content Extractor read-only

- identifica a fonte editorial efetiva;
- extrai conteúdo semântico de Elementor legado, Gutenberg/Core Blocks, HTML legado e plain text;
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

### Canonical Block Normalization / Production Readiness

A convergência do legado deve produzir WordPress Core Blocks em `post_content`, de forma administrativa, separada e governada:

- nunca em activation/update;
- preflight;
- matriz de compatibilidade por fonte;
- **Block Projection Plan read-only**;
- dry-run;
- stale-source guard;
- journal/rollback;
- lock exclusivo;
- canário;
- batches retomáveis;
- autorização explícita para qualquer writer real;
- paridade estrutural/editorial e rollback comprovados.

O **plugin Gutenberg não é dependência**. Somente APIs/features estáveis presentes no WordPress Core homologado podem ser requisito do produto.

## 3. Invariantes

Extração/Knowledge Document nunca podem:

- escrever em `post_content` ou `_elementor_data`;
- alterar status/data/revisões/publicação;
- executar shortcodes/widgets/dynamic blocks arbitrariamente;
- depender de IA/Foundry/vetor/rede externa;
- persistir projeções sem gate específico;
- migrar conteúdo implicitamente.

G-245 também não autoriza escrita por existência de plano, journal, lock ou readiness. Writer/migration permanecem disabled-by-default até subgates, autorização e evidência.

**Novo invariante:** nenhum writer futuro deve ter `_elementor_data` como destino. Elementor é fonte legada de leitura/migração.

## 4. Estratégia de leitura

1. `WP_Post`/APIs nativas;
2. flags independentes de origem;
3. WordPress Core Blocks via estrutura estática (`parse_blocks`) quando presentes;
4. Elementor válido via traversal allowlisted como source adapter legado;
5. Legacy HTML como adapter de primeira classe;
6. plain text;
7. fallback adicional somente com autorização posterior baseada em evidência.

## 5. R-200 — evidência do corpus

Corpus: 622 posts.

Achados principais consolidados:

- forte predominância Legacy HTML;
- T081: 536 `legacy_html`, 41 `plain_text`, 34 `elementor`, 5 `mixed`, 4 `gutenberg`, 2 `empty`;
- Elementor puro é parcela minoritária do corpus;
- Gutenberg/Core Blocks é residual hoje, porém passa a ser o destino canônico futuro;
- shortcodes com falsos positivos textuais por colchetes;
- budgets observados abaixo do soft limit de 256 KiB.

R-200 foi executado read-only com fingerprint idêntico e zero mutação.

## 6. R-210 — Extraction Contract

Contratos:

- `extraction-contract-v1.md`;
- `extraction-contract-v1.1.md`.

Definem source selection, adapters, shortcodes, fallback, warnings e budgets. Qualquer menção anterior a “direção editorial Elementor” deve ser interpretada como **SUPERSEDED pela ADR-004-001**.

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
- 21.969 fragments.

A compatibilidade Elementor calculada naquele gate permanece evidência histórica de source handling, não critério do novo destino.

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

## 10. G-245 — Canonical Block Normalization & Production Readiness

**Status: REBASELINED / IN PROGRESS em `spec004-g245-production-readiness`; PR #4 DRAFT.**

### 10.1 Histórico antes do pivot

Foram concluídos com valor reutilizável:

- T080 Production Preflight — PASS WITH REVIEW ITEMS;
- T081 Elementor Projection Plan — PASS ambiental como diagnóstico/read-only;
- T082 Gateway Elementor — PASS local/contratual, agora **SUPERSEDED como destino**;
- T083/T084 journal + stale-source — preservados/generalizados;
- T085 dry-run — preservado/generalizável;
- T086 batches — preservados/generalizáveis;
- T083B Durable Journal Storage — PASS ambiental;
- T087A Canary Readiness — preservado como padrão de gate;
- T087B Migration Lock — preservado;
- T088 Runbook — deve ser generalizado de Elementor para Block Migration.

Nenhum writer Elementor foi executado. Nenhum canário mutável ocorreu. `_elementor_data` não foi alterado por G-245.

### 10.2 Decisão de pivot

ADR-004-001 determina:

- WordPress Core Blocks são o destino editorial canônico;
- Elementor fica como source adapter legado;
- plugin Gutenberg não é dependência;
- APIs experimentais/plugin-only não entram no baseline de produção;
- T087C writer Elementor é **CANCELADO/SUPERSEDED antes de implementação**.

### 10.3 Novo subgate

**T090 — Block Projection Contract / Plan read-only.**

Objetivo:

- mapear Knowledge Document → árvore canônica de Core Blocks;
- suportar inicialmente somente blocos Core explicitamente allowlisted;
- não renderizar dynamic blocks;
- não executar shortcodes;
- emitir `review_required` para conteúdo sem mapeamento seguro;
- produzir `block_projection_hash` determinístico;
- `writer_allowed=false`;
- zero persistência.

Depois de T090, os gates defensivos existentes serão generalizados/reaplicados ao destino Blocks antes de qualquer novo canário.

## 11. Estratégia de migração Elementor → Blocks

Elementor não deve ser removido/desativado automaticamente.

Fases:

1. manter leitura do Elementor via `Elementor_Adapter`;
2. projetar para Core Blocks;
3. validar full-corpus e amostras humanas;
4. executar canário governado em homologação;
5. migrar em batches somente após rollback comprovado;
6. inventariar dependências restantes;
7. só propor remoção do plugin Elementor quando dependência efetiva for zero.

Critério mínimo para retirada futura:

- zero `elementor`;
- zero `mixed` dependente de Elementor;
- zero widget/shortcode Elementor necessário ao conteúdo ativo;
- zero dependência runtime de `_elementor_data`;
- paridade editorial/estrutural comprovada;
- rollback comprovado;
- gate explícito.

## 12. G-250 — Lifecycle / RC

**NOT_RUN.**

Antes do RC:

- remover/desabilitar runners temporários;
- package clean;
- source parity/lint/JS/ZIP;
- instalação limpa + update;
- deactivate/activate;
- regressão SPECs 001–003;
- smoke extractor/document;
- Block Projection full-corpus;
- production preflight;
- documentação/changelog atualizados.

## 13. Fora de escopo atual

- índice lexical;
- chunks persistidos;
- embeddings;
- MariaDB Vector;
- Azure Foundry/RAG;
- ranking/telemetria de busca;
- IA como writer editorial autônomo;
- dependência do plugin Gutenberg;
- APIs experimentais do Gutenberg;
- remoção imediata do Elementor.

## 14. Gates

- R-200: **PASS**.
- R-210: **PASS**.
- G-220: **PASS**.
- G-230: **PASS**.
- G-240: **PASS / CLOSED**.
- G-245: **REBASELINED / IN PROGRESS — Blocks como destino; writer não autorizado**.
- T090: **NEXT**.
- G-250: **NOT_RUN**.

## 15. Definition of Done

A SPEC-004 termina apenas quando:

- Content Extractor e Knowledge Document permanecerem aceitos em conteúdo real;
- Block Projection estiver contratada, determinística e validada full-corpus;
- WordPress Core Blocks forem comprovados como destino canônico sem dependência do plugin Gutenberg;
- migração do legado estiver governada por dry-run, journal, stale-source, lock, rollback, canário e batches;
- nenhum writer `_elementor_data` for introduzido;
- houver evidência objetiva de zero mutação editorial nos fluxos read-only;
- requisitos aplicáveis de `docs/DEFINITION-OF-DONE.md` estiverem fechados.
