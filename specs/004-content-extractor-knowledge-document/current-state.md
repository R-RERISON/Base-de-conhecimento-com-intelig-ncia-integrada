# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline e gates

- baseline de entrada: `0.3.0-rc.1`;
- SPEC-001/002/003: concluídas;
- R-200: **PASS**;
- R-210: **PASS**;
- G-220: **PASS ambiental**;
- G-230: **IMPLEMENTED / LOCAL PASS / ENV SMOKE PENDING**.

## Ambiente de homologação validado

Smoke G-220 executado sobre cópia de produção:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- Elementor `4.1.0`;
- multisite: não;
- 622 posts.

Evidência: `evidence/g220-smoke-20260915T221710Z.json`.

Segurança comprovada:

- fingerprint editorial antes/depois idêntico;
- zero posts alterados;
- corpus 622 → 622;
- zero extractor errors;
- zero throwables;
- sem persistência/renderização arbitrária.

## Runtime Content Extractor

Implementado e ambientalmente aceito:

- `Content_Normalizer`;
- `Shortcode_Inspector`;
- `Legacy_HTML_Adapter`;
- `Content_Source`;
- `Elementor_Adapter`;
- `Gutenberg_Adapter`;
- `Content_Extractor`.

Resultado ambiental:

- 21.969 fragments;
- `legacy_html`: 536;
- `plain_text`: 41;
- `elementor`: 34;
- `mixed`: 5;
- `gutenberg`: 4;
- `empty`: 2.

A diferença em relação ao profiler R-200 é esperada: o extractor definitivo classifica Elementor inválido pela estratégia efetiva de fallback, principalmente Legacy HTML.

## Readiness Elementor

- native: 39;
- projectable: 505;
- review_required: 78;
- blocked: 0.

Isso significa 544/622 (87,46%) nativos ou projetáveis para o futuro pipeline de normalização; nenhum bloqueio estrutural foi observado nesta classificação inicial.

## Knowledge Document v1

Implementado em memória:

- `Canonical_JSON`;
- `Knowledge_Document`;
- schema `1.0.0`;
- sections ordenadas;
- structure canônica;
- `source_hash`;
- `document_hash`;
- proveniência de extração;
- readiness Elementor apenas informativa.

Contrato: `knowledge-document-contract-v1.md`.

### Hash semantics

`source_hash` é baseado no conhecimento semanticamente extraído, não no JSON/HTML bruto.

`document_hash` representa a projeção canônica da entidade editorial e exclui do payload do hash:

- `document_hash`;
- `canonical_url`;
- `modified_gmt`.

URL e data continuam presentes no documento como envelope operacional.

## Testes locais

- Content Extractor: **14/14 PASS**;
- Knowledge Document: **10/10 PASS**;
- zero-write: PASS;
- canonical JSON repetível: PASS;
- alteração semântica/título/ordem altera hashes: PASS;
- URL/data/ruído bruto não usado não contamina hashes semânticos: PASS.

## Package ambiental atual

`0.4.0-smoke.2`

SHA-256:

`1466cd4fcd18120d0b2405bf04ec629230f23c2a2e759869a8123c45cedaf204`

Validação do artefato:

- 25 arquivos;
- PHP lint extraído: 21/21 PASS;
- JS syntax: PASS;
- ZIP integrity: PASS;
- parity: 25/25 PASS;
- G-220 tests: 14/14 PASS;
- G-230 tests: 10/10 PASS.

O profiler R-200 e o runner G-220 não fazem parte desse package. Somente o runner temporário G-230 está habilitado.

## Direção editorial e produção

Elementor permanece padrão editorial futuro.

A arquitetura separa:

1. knowledge plane read-only;
2. editorial migration plane explícito.

Migration editorial nunca roda em installation/activation/update e exige preflight, dry-run, stale-source guard, journal/rollback, canário e batches retomáveis.

Contratos:

- `elementor-normalization-contract-v1.md`;
- `production-rollout-contract-v1.md`.

## Próximo passo

Executar `0.4.0-smoke.2` em homologação e retornar `bdc-kb-spec004-g230-smoke-*.json`.

G-230 exige duas passagens completas de 622 documentos com:

- zero errors;
- zero throwables;
- zero hash mismatches;
- zero canonical JSON mismatches;
- zero mutação editorial.
