# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- baseline de entrada: `0.3.0-rc.1`.
- SPEC-004: **ATIVA**.
- R-200 — Current State: **PASS**.
- R-210 — Extraction Contract: **PASS**.
- contrato de extração ativo: **`Extraction Contract v1.1.0`**.
- G-220 — Content Extractor: **IMPLEMENTED / LOCAL PASS**.
- build atual: **`0.4.0-dev.1`**.
- próximo passo operacional: **smoke read-only no WordPress de homologação**.
- G-230 continua bloqueado até esse smoke.

## Direção editorial consolidada

O R-200 mostrou predominância de legacy HTML por razões históricas/migrações, mas isso não altera a direção futura:

- **Elementor é o editor operacional padrão atual da equipe**;
- o extractor precisa continuar multi-source para compreender todo o legado;
- o Knowledge Document permanece independente do editor;
- a convergência dos posts para Elementor será tratada como **migration editorial explícita**, e não como efeito colateral do extractor.

Contratos adicionados:

- `elementor-normalization-contract-v1.md`;
- `production-rollout-contract-v1.md`;
- `extraction-contract-v1.1.md`.

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

- projection plan;
- Elementor Gateway versionado;
- dry-run;
- journal/rollback;
- stale-source guard;
- canário;
- batch migration.

Nunca roda automaticamente em activation/update.

## Build `0.4.0-dev.1`

Componentes permanentes adicionados:

- `Content_Normalizer`;
- `Shortcode_Inspector`;
- `Legacy_HTML_Adapter`;
- `Content_Source`;
- `Elementor_Adapter`;
- `Gutenberg_Adapter`;
- `Content_Extractor`.

Características:

- sem hook próprio;
- sem cron/job;
- sem storage;
- sem IA/rede externa;
- sem `do_shortcode()`;
- sem `render_block()`;
- sem renderização Elementor;
- sem writers editoriais;
- profiler R-200 desabilitado no bootstrap (`BDC_KB_SPEC004_PROFILE_BUILD=false`).

O arquivo do profiler ainda fica no source até G-250 para rastreabilidade e será removido antes do RC.

## Readiness Elementor na saída intermediária

O extractor informa:

- `native` — Elementor válido;
- `projectable` — fonte apta a futura projection determinística;
- `review_required` — conteúdo exige adapter/revisão;
- `blocked` — condição atual impede migration segura.

Isso é somente diagnóstico read-only.

## Validação local G-220

Evidência: `g220-local-validation.md`.

Resultado:

- PHP lint dos novos arquivos: **PASS**;
- unit tests SPEC-004: **14/14 PASS**;
- zero-write checado após cada teste;
- repetibilidade: PASS;
- fallback sem `DOMDocument`: PASS no runner local.

A ausência de `ext-dom` no PHP CLI de teste foi tratada como caso de portabilidade, não como nova dependência obrigatória do plugin.

## Produção

O desenvolvimento ocorre em homologação baseada em cópia de produção. Mesmo assim, promotion futura deve assumir que os ambientes podem divergir.

Antes de produção haverá G-245 com:

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

No ambiente WordPress de homologação:

1. instalar/substituir pelo build `0.4.0-dev.1` quando o package for disponibilizado;
2. confirmar ativação sem fatal;
3. confirmar que **Profiler SPEC-004 não aparece mais no menu**;
4. smoke das telas/flows existentes de Summary, Classificação e Review;
5. executar runner read-only controlado do G-220 sobre amostra representativa — sem persistir conteúdo;
6. verificar zero alteração de fingerprint/status/modified/revisões;
7. validar casos Elementor válido, Elementor inválido, legacy HTML, Gutenberg, shortcode e plain text;
8. retornar a evidência do smoke;
9. só então fechar G-220 ambiental e iniciar G-230.

## Gates

- R-200: **PASS**.
- R-210: **PASS**.
- G-220: **IMPLEMENTED / LOCAL PASS — ENV SMOKE PENDING**.
- G-230: **BLOCKED por smoke G-220**.
- G-240: **BLOCKED por G-230**.
- G-245: **PLANNED — writer não autorizado**.
- G-250: **NOT_RUN**.
