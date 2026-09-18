# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Repositório / referência

- Repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`.
- Branch canônica: `main`.
- Merge commit SPEC-004: `e08871557b2233bf1294b1e57752265d3fe68c0f`.
- PR #4: **MERGED** em `main`.
- SPEC-004: **CLOSED / main**.

## Estado comprovado

- G-240: PASS/CLOSED/main.
- UX-003: PASS ambiental.
- T100D Persistent Single-Post Migration: PASS ambiental.
- T100E E1-E6: PASS/concluídos.
- T100E-E7: PASS/CLOSED.
- G-245: PASS/CLOSED/main.
- G-250 Lifecycle/RC1: PASS ambiental/CLOSED.
- RC final limpo: `0.4.0-spec004-rc2`.
- SHA-256 RC2: `ac25c2ffd4a0ae2250fa2ce1a07bf07b4cad8a24030e31f12c78189e61e7506b`.

## Evidência G-250

Arquivo: `evidence/g250-lifecycle-rc-pass-20260918T123514Z.json`.  
SHA-256 bruto: `c7fa462b7a0e5e0e1307cd63d62a6d98e12ebe44620e9208d9d08dcbecab7c76`.

Comprovado:
- upgrade para RC1;
- Workspace íntegra;
- deactivate/activate;
- downgrade controlado para `0.4.0-g245-ux003.1`;
- reinstall RC1;
- SPEC-001/002/003 read paths PASS;
- Content Extractor PASS;
- Knowledge Document 2.1.0 PASS;
- post 358 source_kind `gutenberg`;
- operational `no_action_required`;
- journal `applied`;
- lock `free`;
- fingerprint before == after;
- `gate_result.g250_lifecycle_rc_pass=true`.

## RC2 final

Runtime final:
- G-250 OFF;
- T100E-E6 OFF;
- Production Preflight OFF;
- T100D executor OFF;
- Elementor writer OFF;
- Workspace ON;
- Core Blocks preparation read-only ON.

Validação:
- 44 arquivos no ZIP;
- 39 PHP / 39 lint PASS;
- 38/38 active requires;
- single plugin root;
- deterministic rebuild PASS;
- Core Blocks activity sem operação proibida;
- Workspace invariants PASS.

Manifest/checksum:
- `evidence/spec004-rc2-manifest.json`;
- `evidence/spec004-rc2.sha256`;
- `evidence/spec004-final-release-validation-20260918.json`.

## Arquitetura vigente

- WordPress Core Blocks são o destino editorial canônico.
- Elementor é fonte legada temporária.
- `Elementor_Adapter` permanece.
- Dependência residual: 39 artigos (34 Elementor + 5 mixed).
- Mixed exige humano.
- Nenhuma ação global pode alterar múltiplos posts implicitamente.
- Writes futuros são post-scoped.

## Autorização de migração

O controle de autorização **permanece obrigatório**. O mecanismo atual de baixar Authorization Pack é transitório e não é a UX final desejada.

Direção futura `AUTH-UX-001`:
- integrar autorização à própria Workspace;
- mostrar dry-run/impacto;
- confirmação humana explícita por post;
- capability + nonce;
- stale-source recheck;
- journal + lock + rollback;
- auditoria do autorizador/fingerprint.

Não reativar T100D one-shot como produto e não criar migração em massa implícita.

## Dívida residual

- 39 artigos relacionados a Elementor;
- retirada física do Elementor somente com dependência zero;
- AUTH-UX-001 fora do escopo da SPEC-004;
- busca lexical/Golden Queries é a próxima evolução de produto prevista no roadmap, sem misturar com migração.

## RC2 Final Smoke

**PASS AMBIENTAL confirmado pelo usuário em 2026-09-18.**

- RC2 instalado em homologação;
- smoke final executado pelo usuário;
- nenhuma regressão foi reportada na confirmação;
- evidência humana: `evidence/spec004-rc2-final-smoke-user-acceptance-20260918.json`.

## Próximo passo

A SPEC-004 não possui gate pendente. O próximo trabalho deve começar em branch/SPEC nova, preservando:
- AUTH-UX-001 como dívida planejada;
- 39 artigos Elementor/mixed como dependência residual;
- nenhuma migração em massa implícita;
- busca lexical + Golden Queries como próxima evolução prevista do produto.

Evidência da promoção: `evidence/spec004-main-promotion-20260918.json`.

> Quem não sabe onde está, não sabe para onde quer ir.
