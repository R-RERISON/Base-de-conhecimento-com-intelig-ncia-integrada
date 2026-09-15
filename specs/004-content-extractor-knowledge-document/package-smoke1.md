# Package — SPEC-004 `0.4.0-smoke.1`

**Finalidade:** homologação ambiental do G-220.  
**NÃO é build de produção.**

## Artefato

`base-conhecimento-inteligencia-integrada-0.4.0-smoke.1.zip`

SHA-256:

`3dad9f5f7c01e7970d314a0d0788756ad694cc9f3b9327a2834ead90b86e4c8f`

Tamanho: `53.424 bytes`.

## Build flags

- `BDC_KB_SPEC004_PROFILE_BUILD=false`;
- `BDC_KB_SPEC004_SMOKE_BUILD=true`.

Consequências:

- profiler R-200 não é registrado;
- submenu temporário **Smoke G-220** é registrado apenas para `manage_options`;
- Content Extractor permanente é carregado como serviço read-only;
- nenhuma migration editorial é executada na instalação/ativação.

## Runtime incluído

Além do baseline validado anterior, o package contém:

- `class-content-normalizer.php`;
- `class-shortcode-inspector.php`;
- `class-legacy-html-adapter.php`;
- `class-content-source.php`;
- `class-elementor-adapter.php`;
- `class-gutenberg-adapter.php`;
- `class-content-extractor.php`;
- `class-content-extractor-smoke.php` — temporário.

O antigo `class-content-profile.php` permanece fisicamente no source/package nesta etapa, porém desabilitado e não carregado. Será removido no cleanup G-250.

## Validação de build

- PHP lint antes de empacotar: **20/20 PASS**;
- PHP lint após extrair ZIP: **20/20 PASS**;
- JavaScript `workspace.js`: syntax **PASS**;
- ZIP integrity (`unzip -t`): **PASS**;
- source parity dos 9 arquivos novos/alterados: **9/9 PASS**;
- unit tests SPEC-004 executados contra os arquivos extraídos do próprio ZIP: **14/14 PASS**.

## Segurança do smoke

Runner:

- `manage_options`;
- POST + nonce;
- somente leitura;
- percorre o corpus usando `Content_Extractor::extract()`;
- não executa shortcode callback;
- não renderiza Elementor;
- não renderiza dynamic blocks;
- não persiste resultado/progresso;
- não exporta corpo editorial, post IDs, títulos ou URLs;
- calcula fingerprint editorial before/after;
- conta posts divergentes;
- exporta somente agregados estruturais/warnings/readiness/performance.

## Evidência esperada

`bdc-kb-spec004-g220-smoke-YYYYMMDD-HHMMSS.json`

Gates mínimos:

- `safety.editorial_fingerprint_equal=true`;
- `safety.changed_posts_during_run=0`;
- `safety.corpus_count_before == safety.corpus_count_after`;
- `extraction.extractor_errors=0`;
- `extraction.throwables=0`.

## Procedimento de homologação

1. instalar/substituir o plugin por `0.4.0-smoke.1`;
2. confirmar versão na tela de plugins;
3. confirmar que o antigo **Profiler SPEC-004** não aparece;
4. abrir **Base de Conhecimento → Smoke G-220**;
5. evitar edição concorrente durante a execução;
6. clicar **Executar smoke read-only e baixar JSON**;
7. retornar o JSON gerado;
8. executar smoke manual das superfícies existentes de Summary/Classificação/Review.

## Regra de lifecycle

`class-content-extractor-smoke.php` e `BDC_KB_SPEC004_SMOKE_BUILD=true` são temporários. O runner não pode chegar ao RC/produção.
