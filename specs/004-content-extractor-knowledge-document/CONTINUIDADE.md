# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-001/002/003: concluídas.
- UX-002 `0.4.0-ux002.3`: **PASS / CLOSED / promovida para main** e contrato visual obrigatório.
- G-240: **PASS / CLOSED / promovido para `main`**.
- KD 2.1.0: PASS técnico full-corpus + PASS humano 8/8.
- G-245: **IN PROGRESS** em `spec004-g245-production-readiness`; PR #4 DRAFT / NÃO MERGEAR.
- T080: **PASS WITH REVIEW ITEMS**.
- T081: **PASS AMBIENTAL**.
- T082–T086: **PASS LOCAL / CONTRATUAL**.
- T083B Journal Durable Storage: **PASS AMBIENTAL**.
- T087A Canary Readiness: **PASS LOCAL / READ-ONLY**.
- T087B-prep Exclusive Migration Lock: **PASS LOCAL**.
- T087 canário mutável + rollback real: **BLOCKED / NÃO EXECUTADO**.
- T088 Runbook: **FROZEN PROCEDURE / EXECUTION BLOCKED**.
- writer/migration Elementor permanecem não autorizados.

## Baseline comprovada

`main`: `6d0fc8e33f826ee957038483d22fa1b804bae056`.
Sincronização G-245 + UX-002: `145e16bf31f7afe2d3f08d087b79b69f3f40b885`.
Build de prova T083B: `0.4.0-g245-journal-smoke.1`.

Os arquivos visuais homologados devem permanecer fora do diff: `class-admin-page.php`, `class-classification-admin.php`, `visual-foundation.css`, `class-visual-foundation.php`.

## Gates fechados

- T081: 622/622 em duas passagens, zero mutação, `gate.t081_pass=true`.
- T082: Gateway version-gated, 45 assertions PASS.
- T083/T084: journal contract + stale guard, 38 assertions PASS.
- T085: dry-run zero-write, 38 assertions PASS.
- T086: batches determinísticos/retomáveis, 37 assertions PASS.
- T087A: readiness de canário read-only, 25 assertions PASS.
- T083B: journal storage privado append-only, 22 assertions locais PASS + **PASS ambiental**.
- T087B-prep: lock exclusivo por artigo, 18 assertions PASS.

Nenhum desses resultados, isoladamente ou em conjunto, habilita writer.

## T083B — Journal Durable Storage — PASS AMBIENTAL

Storage selecionado após princípio de negação: postmeta privado `_bdc_kb_migration_journal`, append-only. Comments API, custom table, Options API e file storage foram descartados para esta finalidade.

O capsule guarda `post_content` e `_elementor_data` byte-a-byte usando Base64 somente no envelope de storage. Integridade continua validada sobre o payload original. Cadeia de eventos é linear e fail-closed.

Smoke executado em homologação com WordPress `6.9.4`, PHP `8.5.10` e build `0.4.0-g245-journal-smoke.1`:

- evento criado: true;
- round-trip exato: true;
- integridade do record: true;
- cleanup: true;
- journal count 0 antes / 0 depois;
- conteúdo editorial inalterado;
- writer/migration false;
- `gate.t083b_storage_pass=true`;
- duração: 44 ms.

Evidência: `evidence/g245-journal-storage-smoke-20260917T155150Z.json`. SHA-256 recebido: `a05918e28e206766cb2b28e37c8ec64e9d18a39f44ecd28d02a7bbd6ffb2c118`.

## T087B-prep — Exclusive Migration Lock

`_bdc_kb_migration_lock` é exclusivo por artigo via Metadata API. Token exato é obrigatório para release; TTL não permite takeover automático. Lock expirado exige recuperação explícita/manual. O lock não concede autorização editorial.

## T088 — Runbook

`t088-production-runbook-v1.md` está congelado. O primeiro canário deverá manter lock, persistir journal prepared, repetir stale check imediatamente antes do write, verificar pós-write e executar rollback real antes de T087 ser considerado PASS.

## Próximo passo exato

1. implementar **T087C — executor mutável mínimo**, version-gated e disabled-by-default, sem rota ativa por padrão;
2. selecionar 1 artigo canário de baixo risco (`projectable`, sem review requerido e sem dependência legada desconhecida);
3. gerar Authorization Pack específico com hashes/operação prevista/rollback;
4. obter autorização específica para esse artigo/run;
5. executar canário e rollback real;
6. somente após evidências decidir T089 e eventual escalada.

## Guardrails

- UX-002 não pode regredir;
- journal write-ahead durável está comprovado e continua obrigatório antes de write;
- stale-source deve ser revalidado imediatamente antes de write;
- lock exclusivo cobre a janela crítica;
- canário inicial = 1 item;
- produção não é ambiente experimental;
- autorização do canário deve ser específica ao Authorization Pack;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
