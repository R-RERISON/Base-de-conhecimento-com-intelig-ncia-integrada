# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- baseline de entrada: `0.3.0-rc.1`.
- SPEC-004: **ATIVA**.
- R-200 — Current State: **PASS**.
- R-210 — Extraction Contract: **PASS**.
- G-220 — Content Extractor: **PASS ambiental**.
- G-230 — Knowledge Document: **PASS ambiental — 2026-09-15**.
- G-240 — Real Content Acceptance: **PASS ambiental — 2026-09-16**.
- contrato de extração: `Extraction Contract v1.1.0`.
- contrato Knowledge Document: `Knowledge Document Contract v1.0.0`.
- G-245 — normalização Elementor/produção: **IN_PROGRESS — T082 Production Preflight read-only concluída; writer ainda proibido**.
- T083 — matriz de compatibilidade: **PASS para baseline de homologação; produção ainda NOT_VERIFIED**.
- T084 — Projection Plan: **PASS em testes unitários, containerizados e WordPress real; writer ainda proibido**.
- T085 — Elementor Gateway: **PASS para inspeção version-gated e bloqueio de writer; migration ainda proibida**.
- T086 — journal/rollback: **PASS documental; storage e writer ainda proibidos**.
- T087 — dry-run: **PASS ambiental no corpus real; sem writes ou journal**.
- T088 — stale-source guard: **PASS unitário; divergência bloqueia aplicação**.
- T089 — batches: **PASS documental; executor e writer ainda proibidos**.
- T090 — canário: **PASS técnico read-only; 20/20 posts multi-origem; save Elementor/frontend e rollback editorial ainda NOT_RUN**.
- T092 — runbook: **PASS documental; migration e rollback editorial ainda bloqueados**.
- T091 — rollback: **PASS no canário Elementor nativo 3/3; origens não-Elementor ainda sem writer**.
- Builder Elementor: **PASS unitário e ambiental para HTML legado, plain text e Gutenberg estático; review_required permanece bloqueado**.
- Canário writer não-Elementor: **PASS corrigido para HTML/texto 2/2**; snapshot registra presença/ausência de metas e rollback remove chaves criadas. Gutenberg mixed/review_required continuam fora do writer.
- Gutenberg estático projectable: **PASS no post 13575**, com save/rollback e snapshot integral iguais; `44283` review_required e `606` mixed/native continuam bloqueados.

## Evidência ambiental G-220

- `evidence/g220-smoke-20260915T221710Z.json`;
- WordPress `6.9.4` / PHP `8.5.10` / Elementor `4.1.0`;
- corpus 622 → 622;
- fingerprint editorial idêntico;
- zero changed posts;
- zero extractor errors/throwables;
- 21.969 fragments;
- readiness Elementor: 39 native / 505 projectable / 78 review_required / 0 blocked.

**G-220: PASS.**

## Evidência ambiental G-230

Arquivos:

- `evidence/g230-smoke-20260915T233450Z.json`;
- `g230-smoke-analysis.md`.

Ambiente:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- plugin `0.4.0-smoke.2`;
- Elementor `4.1.0`;
- Knowledge Document schema `1.0.0`;
- corpus: 622 posts.

Segurança:

- `read_only_design=true`;
- fingerprint editorial before/after idêntico;
- `changed_posts_during_run=0`;
- corpus `622 -> 622`;
- nenhum Knowledge Document/hash persistido;
- nenhum conteúdo, post ID, título ou URL exportado.

Determinismo real:

- first pass documents: `622`;
- second pass documents: `622`;
- errors: `0/0`;
- throwables: `0/0`;
- `hash_mismatches=0`;
- `canonical_json_mismatches=0`;
- `unique_source_hashes=601`;
- `unique_document_hashes=622`;
- sections total: `21.969`.

Performance das duas passagens:

- `3096 ms`;
- peak memory `31.457.280 bytes` (~30 MiB).

Hashes agregados da evidência:

- source: `53bac368abb9bbcf9d55b59db457aa6044e371ee7ab96e88b0374638a1cfc415`;
- document: `839012c3336a3c6323be1b5bd2abc18146cac67254c376dc0c8a966426d7c0dd`.

**G-230: PASS.**

## Direção editorial consolidada

- Elementor é o editor operacional padrão futuro.
- Legacy/Gutenberg/plain continuam suportados pelo knowledge plane por representarem o histórico real.
- Knowledge Document é editor-independent.
- normalização para Elementor é migration editorial explícita, separada da leitura e da instalação/update.

## Evidência ambiental G-240

- execução pela interface autenticada do navegador integrado;
- WordPress `7.0` / PHP `8.5.10` / Elementor `4.1.0`;
- plugin `0.4.0-smoke.2`;
- corpus: `622` antes e depois;
- amostra: `23` casos em `8` categorias;
- categorias: legacy HTML, review_required, plain text, Elementor native, shortcode/tabela, Elementor mixed, Gutenberg e vazio/corrompido;
- first/second pass errors: `0/0`;
- first/second pass throwables: `0/0`;
- hash mismatches: `0`;
- canonical JSON mismatches: `0`;
- seções verificadas: `1.114`;
- fingerprint editorial igual: `true`;
- posts alterados durante execução: `0`;
- runtime: `458 ms`;
- peak memory: `25.165.824 bytes`;
- relatório seguro exibido inline em vez de download, para permitir validação pelo navegador integrado.

**G-240: PASS.**

## Runtime permanente validado

### T082 — Production Preflight

- página administrativa temporária `G-245 Preflight`;
- compara WordPress, PHP, Elementor e backup com perfil alvo via ambiente;
- sem perfil alvo completo, decisão `NOT_CONFIGURED` e fail-closed;
- ambiente local validado: WordPress `7.0`, PHP `8.5.10`, Elementor `4.1.0`, MariaDB `12.3.3`;
- `read_only_design=true`, `editorial_writes=false`, `elementor_writes=false`, `persistent_storage=false`;
- validação no navegador integrado: **PASS para comportamento read-only / NOT_CONFIGURED para promoção**.

### T083 — Matriz de compatibilidade

- artefato: `compatibility-matrix-v1.md`;
- baseline: WordPress `7.0`, PHP `8.5.10`, Elementor `4.1.0`, MariaDB `12.3.3`, plugin `0.4.0-smoke.2`;
- leitura/extractor: compatível e validada;
- writer/migration editorial: `BLOCKED`;
- produção: `NOT_VERIFIED` até novo preflight no ambiente alvo.

### T084 — Projection Plan read-only

- serviço: `includes/class-projection-plan.php`;
- campos: `post_id`, `source_kind`, `source_hash_before`, readiness Elementor, estratégia, `projection_hash`, warnings e `requires_review`;
- teste unitário: `1/1 PASS`;
- teste WordPress real: `3/3` planos, hashes de 64 caracteres, zero erros;
- duas passagens em 12 posts: determinístico, zero mutação editorial;
- nenhuma escrita em `_elementor_data`, `post_content`, status, revisão ou storage;
- writer Elementor continua bloqueado.

### T085 — Elementor Gateway

- serviço: `includes/class-elementor-gateway.php`;
- contrato `1.0.0`, versão homologada `4.1.0`;
- Elementor carregado e Document público encontrado em post real;
- `writer_enabled=false`;
- `save()` retorna `bdc_kb_elementor_writer_blocked` com status `BLOCKED`;
- metadata `post_content`, `_elementor_data` e `post_modified_gmt` permaneceu idêntica;
- não chama `Document::save()` e não faz direct-meta write.

### T086 — Journal e rollback editorial

- contrato: `editorial-journal-rollback-contract-v1.md`;
- estados, snapshot mínimo, stale source, idempotência e rollback por item definidos;
- package rollback e editorial rollback permanecem independentes;
- nenhum storage persistente criado;
- writer permanece `BLOCKED` até dry-run, stale guard, batches, canário e rollback real.

### T087/T088 — Dry-run e Stale Source Guard

- dry-run executado no corpus real: `622` posts, `0` errors, `0` blocked;
- `journal_status=NOT_CONFIGURED`, `journal_writes=false`, `editorial_writes=false`;
- fingerprint editorial antes/depois igual;
- stale guard compara `source_hash_before` e `post_modified_gmt_before`;
- fonte divergente retorna `STALE_SOURCE`, `write_allowed=false`;
- nenhum writer ou batch foi criado.

### T090 — Canário técnico read-only

- amostra: `7` categorias reais do corpus;
- todas as fontes ficaram `FRESH` após Projection Plan + Stale Source Guard;
- estratégias cobertas: legacy HTML, plain text, Elementor native/mixed, Gutenberg, review required e vazio/corrompido;
- erros: `0`;
- gateway inspect errors: `0`;
- Knowledge Document hashes: 64 caracteres;
- fingerprint editorial igual: `true`;
- writer não tentado e nenhum conteúdo alterado;
- validação de abrir/salvar no editor Elementor: `NOT_RUN` enquanto o writer continuar bloqueado.

Canário ampliado:

- `20/20` posts válidos;
- legacy HTML `3`, plain text `3`, Elementor native `3`, Elementor mixed `3`, Gutenberg `3`, review_required `3`, vazio/corrompido `2`;
- todos `FRESH`, zero erros e fingerprint editorial igual;
- nenhuma escrita executada.

### T091 — Canário editorial Elementor nativo

- flag de writer habilitada somente no processo de teste;
- capability `manage_options` verificada;
- posts testados: `385`, `464`, `485`;
- save: `APPLIED_CANARY` em `3/3`;
- restore: `ROLLED_BACK_CANARY` em `3/3`;
- snapshot integral igual após restore em `3/3`;
- timestamps, conteúdo, status e metadados Elementor restaurados;
- batch amplo não executado.

### Falha encontrada no canário não-Elementor

- save de projeção em HTML/plain text retornou `APPLIED_CANARY`;
- a promoção criou metadados Elementor em fontes que originalmente não os possuíam;
- o rollback inicial restaurou valores existentes, mas não removeu chaves criadas pela promoção;
- posts afetados: `358` e `367`;
- correção aplicada: snapshot registra presença/ausência e rollback remove chaves criadas;
- estado posterior: `358=legacy_html/projectable`, `367=plain_text/projectable`;
- resultado corrigido: HTML/texto `2/2` com snapshots integrais iguais após restore;
- Gutenberg estático `13575`: snapshot, save e rollback PASS;
- Gutenberg `review_required`/mixed não recebe writer sem adapter/contrato adicional;

### Validação frontend/editorial

- HTML `358`: frontend PASS, título/conteúdo/tabela renderizados, sem erro fatal;
- texto `367`: frontend PASS, título/conteúdo renderizados, sem erro fatal;
- Elementor nativo `385`: frontend PASS, artigo e toolbar Elementor presentes, sem erro fatal;
- Gutenberg `13575`: resposta renderizada, mas estado é rascunho automático; publicação/frontend final `NOT_VERIFIED`.
- PHP lint: PASS no runtime;
- JavaScript: `node --check` PASS em `node:22-alpine`.
- conclusão: o snapshot deve registrar presença/ausência original de cada meta e o rollback deve remover chaves ausentes no snapshot;
- nenhuma nova escrita não-Elementor deve ser executada até corrigir esse contrato.

### Projection Builders não-Elementor

- `Elementor_Projection_Builder` implementado;
- HTML legado, texto simples e Gutenberg estático geram árvore Elementor mínima e determinística;
- IDs de elementos e `projection_hash` são estáveis entre chamadas;
- shortcodes, dynamic blocks, widgets desconhecidos e `review_required` continuam bloqueados;
- nenhum builder grava conteúdo por si só.

### T089 — Execução em lotes retomáveis

- contrato: `editorial-batch-execution-contract-v1.md`;
- checkpoint só avança após resultado terminal validado;
- estados de pause, cancel, retry limitado e `ROLLBACK_REQUIRED` definidos;
- stale source e incompatibilidade bloqueiam sem retry cego;
- nenhum job, fila, cron, tabela ou writer foi criado.

### Estratégia de validação sem ferramentas do host

- PHP/lint/testes: container existente `wordpress-from-repo`;
- JavaScript: imagem `node:22-alpine` com `node --check`;
- PHPUnit/Core Test Suite: permanece `NOT_RUN` até disponibilizar `wordpress-tests-lib` e PHPUnit;
- WP-CLI: permanece bloqueado pelo bootstrap antecipado de `add_filter()` no `wp-config.php`;
- navegador integrado: usado para gates administrativos e acceptance visual.

## Testes automatizados em homologação local

- SPEC-001 Summary Store: `15/15 PASS`;
- SPEC-003 Review Store: `19/19 PASS`;
- G-220 Content Extractor: `14/14 PASS`;
- G-230 Knowledge Document: `10/10 PASS`;
- execução: PHP `8.5.10` dentro do container `wordpress-from-repo`;
- testes de fault injection emitiram logs `PARTIAL_FAILURE_CRITICAL` esperados e terminaram PASS;
- WP-CLI/Core Test Suite de integração: `NOT_RUN`, pois o `wp-config.php` atual chama `add_filter()` antes do bootstrap de funções no WP-CLI e não há `wordpress-tests-lib`/PHPUnit configurado no ambiente.

### G-220

- `Content_Normalizer`;
- `Shortcode_Inspector`;
- `Legacy_HTML_Adapter`;
- `Content_Source`;
- `Elementor_Adapter`;
- `Gutenberg_Adapter`;
- `Content_Extractor`.

### G-230

- `Canonical_JSON`;
- `Knowledge_Document`;
- schema `1.0.0`;
- `source_hash` semântico;
- `document_hash` canônico;
- sections e facts estruturais ordenados;
- warnings/proveniência/readiness;
- zero storage durável.

## Política de hashes

- `source_hash` representa o conhecimento semanticamente extraído; não é identificador de post.
- conteúdos semanticamente equivalentes podem compartilhar `source_hash` — no corpus atual são 601 hashes para 622 posts.
- `document_hash` representa a projeção canônica do documento; na evidência atual são 622 hashes distintos.
- URL/data operacional não invalidam o hash semântico.
- fingerprint bruto continua reservado ao futuro `STALE_SOURCE` do plano de migration Elementor.

## Produção / normalização Elementor

Contratos ativos:

- `elementor-normalization-contract-v1.md`;
- `production-rollout-contract-v1.md`.

Regras permanentes:

- activation/update não migra posts;
- Production Preflight antes de promoção;
- writer futuro atrás de `Elementor_Gateway` version-gated;
- dry-run, journal/rollback, stale-source guard, canário e batches retomáveis;
- plugin rollback e editorial rollback independentes.

## Próximo passo exato — G-245

1. definir e documentar o perfil alvo real de homologação/produção;
2. definir runbook operacional e critérios de canário;
3. executar canário somente após journal/storage e precondições reais;
4. testar rollback integral do canário antes de qualquer lote amplo.

## Gates

- R-200: **PASS**.
- R-210: **PASS**.
- G-220: **PASS**.
- G-230: **PASS**.
- G-240: **PASS — browser acceptance ambiental 2026-09-16; 23 casos / 8 categorias / zero mutação**.
- G-245: **IN_PROGRESS — builders projectable implementados; canário de gravação não-Elementor, frontend por origem e release final ainda pendentes**.
- G-250: **NOT_RUN**.
