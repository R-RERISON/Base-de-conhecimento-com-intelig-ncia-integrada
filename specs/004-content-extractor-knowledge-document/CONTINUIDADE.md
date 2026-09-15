# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- baseline de entrada: `0.3.0-rc.1`.
- SPEC-004: **ATIVA**.
- R-200: **PASS**.
- R-210: **PASS**.
- G-220 — Content Extractor: **PASS ambiental**.
- G-230 — Knowledge Document: **PASS ambiental — 2026-09-15**.
- G-240 — Real Content Acceptance: **PACKAGE READY / HUMAN ACCEPTANCE PENDING**.
- branch atual: `spec004-g240-real-content-acceptance`.
- G-245 — normalização Elementor/produção: planejado; writer ainda proibido.

## Baseline consolidado

PR #2 foi mergeado em `main` no commit:

`a676f8daaaf9a794d503ccd6a2c28178be1fb8bf`

Esse baseline contém:

- Content Extractor determinístico/read-only;
- Knowledge Document schema `1.0.0`;
- `source_hash` semântico;
- `document_hash` canônico;
- evidências ambientais G-220/G-230;
- contratos de normalização Elementor e rollout de produção.

## Evidência G-230

- `evidence/g230-smoke-20260915T233450Z.json`;
- first pass `622/622`;
- second pass `622/622`;
- errors `0/0`;
- throwables `0/0`;
- `hash_mismatches=0`;
- `canonical_json_mismatches=0`;
- fingerprint editorial idêntico;
- zero posts alterados;
- 601 unique source hashes / 622 unique document hashes;
- 21.969 sections;
- runtime das duas passagens: 3096 ms;
- peak memory ~30 MiB.

## G-240 — objetivo

Determinismo não prova fidelidade semântica. O G-240 valida se o Knowledge Document realmente preserva o conhecimento da fonte editorial, na ordem e estrutura necessárias, sem inventar conteúdo.

Contrato ativo:

`real-content-acceptance-contract-v1.md` — `FROZEN v1.0.0`.

## Amostra determinística

Slots:

1. `elementor_native_typical`;
2. `elementor_or_mixed_complex`;
3. `legacy_typical`;
4. `legacy_complex`;
5. `gutenberg`;
6. `shortcode_or_table`;
7. `review_required`;
8. `empty_or_corrupt`.

A seleção usa métricas estruturais, mediana para casos típicos e score determinístico para casos complexos. O submit recalcula a amostra e rejeita alteração/omissão silenciosa via `selection_mismatches`.

## Package temporário G-240

Build: `0.4.0-acceptance.1`.

Documento: `package-acceptance1.md`.

SHA-256:

`8391a0c2ace748711087c327a48d63aa2fa009963c2d4584488d2c1ed5679d82`

Validação final:

- runtime files: `25`;
- PHP lint staging: `21/21 PASS`;
- PHP lint ZIP extraído: `21/21 PASS`;
- JS syntax: `1/1 PASS`;
- ZIP integrity: PASS;
- staging/ZIP parity: `25/25 PASS`;
- acceptance safety scan: PASS;
- runner acceptance Git/package blob: `5b88be5c9a3283e2055aea81950fdffed82f79f8` em ambos;
- bootstrap Git/package blob: `49ea1b55b7535914c69c62f9434ae208b3970006` em ambos;
- Profiler R-200, Smoke G-220 e Smoke G-230 fisicamente ausentes do ZIP.

Menu esperado:

**Base de Conhecimento → Aceitação G-240**

A página:

- exige `manage_options`;
- exibe fonte editorial e Knowledge Document lado a lado;
- não executa shortcode, dynamic block ou render Elementor;
- não persiste seleção/verdict/conteúdo;
- primeiro passe mantém apenas metadados estruturais leves e materializa conteúdo completo somente dos slots selecionados;
- usa stale fingerprint por post;
- recalcula a amostra no submit;
- reconstrói o Knowledge Document duas vezes no submit;
- exporta JSON sem corpo editorial, título ou URL;
- exporta post ID somente para rastreabilidade da amostra.

## Veredito humano por slot

Marcar somente quando verdadeiro:

- cobertura completa;
- ordem semântica preservada;
- nenhum texto inventado;
- estrutura adequada;
- aceitável para busca/IA.

Se houver falha, selecionar reason enum apropriada. Não forçar PASS.

## Gate G-240

PASS exige:

- todos os slots disponíveis revisados;
- todos os slots disponíveis aprovados nos cinco critérios;
- `selection_mismatches=0`;
- `stale_slots=0`;
- `repeatability_failures=0`;
- fingerprint before/after igual;
- zero changed posts durante geração da evidência.

Qualquer falha bloqueia G-240 e volta para correção do extractor/contract. IA ou migração Elementor não mascaram divergência.

## Produção / normalização Elementor

Permanece separado:

- Elementor é direção editorial futura;
- Knowledge plane continua multi-source e read-only;
- activation/update nunca migra posts;
- G-245 exige Production Preflight, version gate, dry-run, journal/rollback, stale-source guard, canário e batches retomáveis.

## Próximo passo exato

1. instalar `0.4.0-acceptance.1` em homologação;
2. confirmar que apenas `Aceitação G-240` aparece como ferramenta temporária da SPEC-004;
3. abrir a tela e revisar todos os slots disponíveis;
4. marcar critérios somente quando objetivamente satisfeitos;
5. para qualquer discrepância, deixar critério desmarcado e escolher reason;
6. gerar `bdc-kb-spec004-g240-acceptance-*.json`;
7. retornar o JSON para análise e fechamento/correção do G-240.

## Gates

- R-200: **PASS**.
- R-210: **PASS**.
- G-220: **PASS**.
- G-230: **PASS**.
- G-240: **PACKAGE READY / HUMAN ACCEPTANCE PENDING**.
- G-245: **PLANNED — writer não autorizado**.
- G-250: **NOT_RUN**.
