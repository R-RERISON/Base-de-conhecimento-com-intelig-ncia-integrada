# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline e gates

- SPEC-001/002/003: concluídas.
- UX-001/UX-002: concluídas; UX-002 `0.4.0-ux002.3` é contrato visual obrigatório.
- G-240: **PASS / CLOSED / promovido para `main`**.
- KD 2.1.0: PASS técnico full-corpus + PASS humano 8/8.
- G-245: **IN PROGRESS** em `spec004-g245-production-readiness`; PR #4 DRAFT.
- T080: **PASS WITH REVIEW ITEMS**.
- T081: **PASS AMBIENTAL**.
- T082–T086: **PASS LOCAL / CONTRATUAL**.
- T087: **NEXT — preparação read-only permitida; canário mutável NÃO AUTORIZADO**.
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

## T080 / T081

T080 PASS WITH REVIEW ITEMS, blockers 0. T081 PASS ambiental: 622/622 em duas passagens, zero erros/mismatches/violações, fingerprint editorial idêntico, changed posts 0, `gate.t081_pass=true`, 44/44 checks independentes PASS.

## T082 — Gateway

PASS LOCAL / CONTRATUAL. Version gate, feature flag default false, capability e hard phase gate. Writer/migration false. 45 assertions PASS.

## T083 — Journal / rollback

PASS LOCAL / CONTRATUAL. Write-ahead journal/capsule/hashes/rollback/idempotência contratados. **Storage durável ainda não implementado (`journal_persisted=false`) e é pré-condição obrigatória para qualquer write real.**

## T084 — Stale-source guard

PASS LOCAL / CONTRATUAL. Fresh/stale/blocking; mismatch/hash inválido falham fechado. T083+T084: 38 assertions PASS.

## T085 — Dry-run

PASS LOCAL / CONTRATUAL em `0.4.0-g245-dryrun.1`. Determinístico, zero-write, ready/review/noop/blocked, review não prepara journal/apply, 38 assertions PASS.

## T086 — Batches retomáveis

PASS LOCAL / CONTRATUAL em `0.4.0-g245-batch.1`.

- apenas dry-runs ready entram no cohort;
- ordenação/dedupe determinísticos;
- duplicata conflitante bloqueia;
- batch size 1–100;
- cursor versionado/integridade/cohort binding;
- cursor adulterado ou stale bloqueia;
- resume sem repetição;
- cobertura exata e zero duplicidade entre batches;
- cohort/batch hashes determinísticos;
- `persists_checkpoint=false`;
- não existe executor;
- execution/writer/migration false;
- 37 assertions PASS + lint PASS.

## Próximo passo — T087

Preparar **Canary Readiness** de forma read-only. O readiness deve tornar explícitos os blockers para uma execução real: journal durável, source fresh no último instante, Elementor homologado, dry-run ready, batch elegível, escopo canário mínimo, rollback capsule íntegro e autorização humana explícita.

**Não executar canário mutável sem autorização explícita posterior.**

## Guardrails

- WordPress/Elementor continuam fonte editorial;
- UX-002 não pode regredir;
- nenhum writer/migration está autorizado até aqui;
- journal durável + stale recheck são pré-condições;
- produção não é ambiente experimental;
- GO homologação != GO produção;
- T087 mutável, rollback real e T089 dependem de autorização explícita.

> Quem não sabe onde está, não sabe para onde quer ir.
