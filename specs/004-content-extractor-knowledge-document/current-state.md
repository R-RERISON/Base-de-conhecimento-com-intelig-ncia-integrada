# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline e gates

- SPEC-001/002/003: concluídas.
- UX-001/UX-002: concluídas; UX-002 `0.4.0-ux002.3` é contrato visual obrigatório.
- G-240: **PASS / CLOSED / promovido para `main`**.
- KD 2.1.0: PASS técnico full-corpus + PASS humano 8/8.
- G-245: **IN PROGRESS** em `spec004-g245-production-readiness`; PR #4 DRAFT / NÃO MERGEAR.
- T080: **PASS WITH REVIEW ITEMS**.
- T081: **PASS AMBIENTAL**.
- T082–T086: **PASS LOCAL / CONTRATUAL**.
- T083B Durable Journal Storage: **PASS AMBIENTAL**.
- T087A Canary Readiness: **PASS LOCAL / READ-ONLY**.
- T087B-prep Migration Lock: **PASS LOCAL**.
- T087 mutável: **BLOCKED / NÃO EXECUTADO**.
- T088 Runbook: **FROZEN PROCEDURE / EXECUTION BLOCKED**.
- G-250: NOT_RUN.

## Baseline `main` e UX

`main`: `6d0fc8e33f826ee957038483d22fa1b804bae056`.
Sincronização G-245 + UX-002: `145e16bf31f7afe2d3f08d087b79b69f3f40b885`.

A UX-002 homologada permanece inviolável. Os arquivos visuais canônicos não devem aparecer no diff G-245 vs `main`.

## Ambiente homologado

- WordPress `6.9.4`;
- PHP `8.5.10`;
- Elementor `4.1.0`;
- MariaDB `12.2.2`;
- corpus: 622 posts;
- DOMDocument ativo;
- WP-Cron habilitado.

## Gates já comprovados

T080 PASS WITH REVIEW ITEMS, blockers 0. T081 PASS ambiental: 622/622 em duas passagens, zero erros/mismatches/violações, fingerprint editorial idêntico, changed posts 0 e `gate.t081_pass=true`.

T082 Gateway: PASS local/contratual, 45 assertions. T083/T084 Journal contract + stale guard: PASS, 38 assertions. T085 Dry-run: PASS, 38 assertions. T086 Batches retomáveis: PASS, 37 assertions. Todos mantêm writer/migration/execution false conforme aplicável.

## T083B — Durable Journal Storage — PASS AMBIENTAL

Storage WordPress-first comprovado em homologação com **postmeta privado append-only** na chave `_bdc_kb_migration_journal`.

Decisões e prova:

- sem custom table, Options API, Comments API ou file storage;
- rollback capsule persistida com Base64 somente como envelope de storage;
- hashes permanecem calculados sobre payload editorial original;
- cadeia linear por parent event;
- fork/retry stale fail-closed;
- `manage_options` obrigatório;
- readback obrigatório e cleanup fail-safe;
- limite v1 de 16 MiB por evento;
- smoke executado com build `0.4.0-g245-journal-smoke.1`;
- evento temporário criado com sucesso;
- `roundtrip_exact=true`;
- `record_integrity_ok=true`;
- cleanup OK;
- contagem de eventos `0 → 0` restaurada;
- `post_content_unchanged=true`;
- `elementor_data_unchanged=true`;
- `editorial_unchanged=true`;
- writer/migration false;
- `gate.t083b_storage_pass=true`.

Evidência: `evidence/g245-journal-storage-smoke-20260917T155150Z.json`. SHA-256 do arquivo recebido: `a05918e28e206766cb2b28e37c8ec64e9d18a39f44ecd28d02a7bbd6ffb2c118`.

## T087A — Canary Readiness

PASS LOCAL / READ-ONLY, 25 assertions. Mesmo quando todas as pré-condições técnicas e autorização simulada estão presentes, o readiness mantém `execution_allowed=false`, `writer_allowed=false` e `migration_execution_allowed=false`; exige executor mutável separado.

## T087B-prep — Exclusive Migration Lock

Implementado com `_bdc_kb_migration_lock` usando `add_post_meta(..., true)` para exclusividade por artigo.

- TTL padrão 300s; limites 30–900s;
- token exato obrigatório para release;
- release repetido é idempotent noop;
- lock expirado não sofre takeover automático;
- `manage_options` obrigatório;
- lock não concede writer/migration;
- **18/18 assertions PASS + lint PASS**.

## T088 — Runbook

`t088-production-runbook-v1.md` está congelado como procedimento. Define freeze, lock, journal write-ahead, stale check final, write controlado, verificação pós-write, rollback obrigatório do primeiro canário, abort conditions, batches e requisitos adicionais de produção.

## Próximo passo técnico

1. implementar o menor executor mutável possível, version-gated e disabled-by-default, sem habilitá-lo;
2. selecionar 1 candidato `projectable`, sem review/shortcode legado desconhecido;
3. gerar Authorization Pack específico com hashes, Projection Plan, dry-run, journal/rollback e operação prevista;
4. obter autorização específica para esse candidato/run;
5. executar canário + rollback real;
6. somente após evidências decidir T089 e eventual escalada para batches.

## Guardrails

- WordPress/Elementor continuam fonte editorial;
- UX-002 não pode regredir;
- journal durável + stale recheck + lock são pré-condições já fechadas tecnicamente;
- produção não é ambiente experimental;
- GO homologação != GO produção;
- nenhum writer está autorizado neste estado;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
