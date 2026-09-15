# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- baseline de entrada: `0.3.0-rc.1`.
- SPEC-004: **ATIVA**.
- R-200 — Current State: **PASS**.
- R-210 — Extraction Contract: **PASS**.
- contrato de extração ativo: **`Extraction Contract v1.1.0`**.
- G-220 — Content Extractor: **IMPLEMENTED / LOCAL PASS**.
- package ambiental ativo: **`0.4.0-smoke.1`**.
- próximo passo operacional: **smoke read-only no WordPress de homologação**.
- G-230 continua bloqueado até a evidência desse smoke.

## Direção editorial consolidada

O R-200 mostrou predominância de legacy HTML por razões históricas/migrações, mas isso não altera a direção futura:

- **Elementor é o editor operacional padrão atual da equipe**;
- o extractor continua multi-source para compreender todo o legado;
- o Knowledge Document permanece independente do editor;
- a convergência dos posts para Elementor será uma **migration editorial explícita**, nunca efeito colateral da leitura.

Contratos:

- `extraction-contract-v1.md`;
- `extraction-contract-v1.1.md`;
- `elementor-normalization-contract-v1.md`;
- `production-rollout-contract-v1.md`.

## Separação arquitetural obrigatória

### Knowledge plane

Read-only:

- Content Extractor;
- Knowledge Document;
- hashes/proveniência;
- busca/IA futura.

Nunca escreve em `post_content` ou `_elementor_data`.

### Editorial migration plane

Futuro writer administrativo:

- Projection Plan;
- Elementor Gateway versionado;
- dry-run;
- journal/rollback;
- stale-source guard;
- canário;
- batch migration.

Nunca roda automaticamente em activation/update.

## Runtime G-220

Componentes permanentes implementados:

- `Content_Normalizer`;
- `Shortcode_Inspector`;
- `Legacy_HTML_Adapter`;
- `Content_Source`;
- `Elementor_Adapter`;
- `Gutenberg_Adapter`;
- `Content_Extractor`.

Características:

- serviço read-only;
- sem cron/job/storage;
- sem IA/rede externa;
- sem `do_shortcode()`;
- sem `render_block()`;
- sem renderização Elementor;
- sem writers editoriais;
- `DOMDocument` opcional com fallback estrutural determinístico.

## Readiness Elementor

A saída intermediária informa:

- `native`;
- `projectable`;
- `review_required`;
- `blocked`.

Isso é diagnóstico para migration futura; não grava Elementor.

## Validação local

Evidência: `g220-local-validation.md`.

- lint dos componentes novos: **PASS**;
- unit tests SPEC-004: **14/14 PASS**;
- zero-write verificado após cada cenário;
- repetibilidade: PASS;
- fallback sem `DOMDocument`: PASS.

## Package ambiental — `0.4.0-smoke.1`

Documento: `package-smoke1.md`.

SHA-256:

`3dad9f5f7c01e7970d314a0d0788756ad694cc9f3b9327a2834ead90b86e4c8f`

Build flags:

- `BDC_KB_SPEC004_PROFILE_BUILD=false`;
- `BDC_KB_SPEC004_SMOKE_BUILD=true`.

Validação do package:

- PHP lint antes/depois de extrair: **20/20 PASS**;
- JS syntax: **PASS**;
- ZIP integrity: **PASS**;
- source parity dos arquivos novos/alterados: **9/9 PASS**;
- unit tests contra os arquivos extraídos do ZIP: **14/14 PASS**.

### Runner temporário

Menu esperado:

**Base de Conhecimento → Smoke G-220**

O runner:

- exige `manage_options`;
- POST + nonce;
- percorre o corpus usando o extractor real;
- não exporta texto, IDs, títulos ou URLs;
- não persiste resultado;
- calcula fingerprint before/after;
- agrega source kinds, warnings, strategies, facts e `elementor_compatibility`;
- deve ser removido/desabilitado após o gate ambiental.

O antigo **Profiler SPEC-004** não deve aparecer porque o profile build está desabilitado.

## Produção

O desenvolvimento ocorre em homologação baseada em cópia de produção, mas a promoção futura deve detectar divergências de ambiente.

G-245 cobrirá:

1. Production Preflight;
2. versão exata WordPress/PHP/Elementor/plugins relevantes;
3. matriz de compatibilidade;
4. dry-run;
5. backup/journal/rollback;
6. canário;
7. stale-source guard;
8. migration por lotes retomáveis;
9. runbook de instalação/upgrade/rollback.

### Regra crítica

Instalar/atualizar/ativar o plugin **não migra conteúdo para Elementor**.

Plugin rollback e editorial rollback são independentes.

## Próximo passo exato

1. instalar/substituir o plugin por `0.4.0-smoke.1` em homologação;
2. confirmar a versão na tela de plugins;
3. confirmar que **Profiler SPEC-004** não aparece;
4. confirmar que **Base de Conhecimento → Smoke G-220** aparece;
5. fazer smoke rápido das superfícies existentes de Summary, Classificação e Review;
6. evitar edição concorrente de posts;
7. clicar **Executar smoke read-only e baixar JSON**;
8. retornar `bdc-kb-spec004-g220-smoke-*.json`;
9. exigir:
   - `safety.editorial_fingerprint_equal=true`;
   - `safety.changed_posts_during_run=0`;
   - corpus before = after;
   - `extraction.extractor_errors=0`;
   - `extraction.throwables=0`;
10. analisar warnings/readiness/performance;
11. fechar G-220 ambiental;
12. somente então iniciar G-230.

## Gates

- R-200: **PASS**.
- R-210: **PASS**.
- G-220: **IMPLEMENTED / LOCAL PASS — ENV SMOKE PENDING**.
- G-230: **BLOCKED por smoke G-220**.
- G-240: **BLOCKED por G-230**.
- G-245: **PLANNED — writer não autorizado**.
- G-250: **NOT_RUN**.
