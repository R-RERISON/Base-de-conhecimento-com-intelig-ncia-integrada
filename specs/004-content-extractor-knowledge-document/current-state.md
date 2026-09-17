# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline e gates

- SPEC-001/002/003: concluídas.
- UX-001/UX-002: concluídas; UX-002 `0.4.0-ux002.3` é contrato visual obrigatório.
- R-200/R-210/G-220: PASS.
- G-230/v1: PASS de determinismo / superseded for AI.
- G-240: **PASS / CLOSED / promovido para `main`**.
- KD 2.1.0 / `0.4.0-acceptance.12`: PASS técnico full-corpus + PASS humano 8/8.
- G-245: **IN PROGRESS** em branch dedicada; PR #4 DRAFT.
- T080: **PASS WITH REVIEW ITEMS**.
- T081: **PASS AMBIENTAL**.
- T082: **PASS LOCAL / CONTRATUAL**.
- T083: **PASS LOCAL / CONTRATUAL**.
- T084: **PASS LOCAL / CONTRATUAL**.
- T085: **PASS LOCAL / CONTRATUAL**.
- T086: **NEXT / NOT_STARTED**.
- G-250: NOT_RUN.

## Baseline `main` e UX

A `main` está em `6d0fc8e33f826ee957038483d22fa1b804bae056`, contendo a UX-002 homologada. A branch G-245 foi sincronizada no merge `145e16bf31f7afe2d3f08d087b79b69f3f40b885` e deve permanecer sem diferenças nos arquivos visuais canônicos.

Arquivos protegidos contra regressão visual durante G-245:

- `includes/class-admin-page.php`;
- `includes/class-classification-admin.php`;
- `assets/css/visual-foundation.css`;
- `includes/class-visual-foundation.php`.

## Ambiente homologado

- WordPress `6.9.4`;
- PHP `8.5.10`;
- Elementor `4.1.0`;
- MariaDB `12.2.2`;
- corpus: 622 posts;
- DOMDocument ativo;
- WP-Cron habilitado.

## T080 / T081 — evidência ambiental

T080: PASS WITH REVIEW ITEMS, blockers 0; `faq_wd` legacy orphan, `wpt` dependência legada desconhecida, loopback não testado. Writer/migration false.

T081: PASS ambiental em `0.4.0-g245-projection.2`:

- 622/622 + 622/622;
- zero errors/throwables;
- zero projection hash/canonical JSON mismatches;
- zero writer/safety/review-policy violations;
- fingerprint editorial idêntico;
- changed posts: 0;
- `gate.t081_pass=true`;
- 44/44 checks independentes PASS.

Evidências:

- `evidence/g245-preflight-summary-20260916T215612Z.json`;
- `evidence/g245-projection-summary-20260917T111009Z.json`.

## T082 — Elementor Gateway

PASS LOCAL / CONTRATUAL. O gateway version-gated homologa explicitamente Elementor `4.1.0`; ausência bloqueia e versão diferente exige review. Feature flag default false, capability administrativa explícita e hard phase gate mantêm `writer_allowed=false` mesmo no caminho hipoteticamente mais permissivo.

Teste: 45 assertions PASS.

## T083 — Journal / rollback

PASS LOCAL / CONTRATUAL. Contrato write-ahead congelado com capsule de rollback, hashes de integridade, estados prepared/applied/partial_failure/rolled_back, bloqueio de rollback stale/tampered e idempotência.

**Importante:** `journal_persisted=false`. O storage durável ainda não foi escolhido/implementado e continua requisito obrigatório antes de qualquer write real.

## T084 — Stale-source guard

PASS LOCAL / CONTRATUAL. Compara `source_hash_before` do plano com o Knowledge Document atual; estados fresh/stale/blocking; mismatch e hash inválido falham fechado antes de qualquer write.

T083+T084: 38 assertions PASS.

## T085 — Dry-run zero-write

PASS LOCAL / CONTRATUAL em `0.4.0-g245-dryrun.1`.

- estados ready/review_required/noop/blocked;
- `ready` é apenas simulação, nunca autorização;
- review_required não prepara journal nem simula apply;
- stale bloqueia;
- gateway blocking bloqueia;
- versão não homologada exige review;
- Projection Plan unsafe bloqueia;
- dry-run hash/JSON determinísticos;
- `execution_allowed=false`;
- `writer_allowed=false`;
- `migration_execution_allowed=false`;
- 38 assertions PASS + lint PASS.

## Próximo passo — T086

Implementar **batches retomáveis read-only**, com particionamento determinístico, cursor/checkpoint explícito, idempotência, ausência de duplicidade e batch hash canônico. T086 ainda não executará writer nem persistirá conteúdo editorial.

## Guardrails

- WordPress/Elementor continuam fonte editorial;
- Content Extractor/KD/Projection Plan são derivados reconstruíveis;
- UX-002 permanece baseline visual inviolável durante estes subgates;
- nenhum writer Elementor está autorizado;
- nenhuma migration Elementor está autorizada;
- journal durável e recheck stale imediatamente antes do write são pré-condições futuras;
- produção não é ambiente experimental;
- GO de homologação != GO de produção;
- canário/rollback real e autorização explícita ainda são obrigatórios.

> Quem não sabe onde está, não sabe para onde quer ir.
