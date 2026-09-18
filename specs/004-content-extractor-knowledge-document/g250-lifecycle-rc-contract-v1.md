# G-250 — Lifecycle / RC Contract v1

**Status:** PASS / CLOSED  
**RC validado:** `0.4.0-spec004-rc1`  
**Fechado em:** 2026-09-18

## Resultado ambiental

A sequência obrigatória foi executada em homologação: upgrade para RC1; Workspace smoke; deactivate/activate; novo smoke; downgrade controlado para `0.4.0-g245-ux003.1`; smoke do post 358; reinstall RC1; execução do runner oculto.

PASS comprovado:
- lifecycle reactivated confirmation;
- rollback cycle confirmation;
- SPEC-001 Summary;
- SPEC-002 Classification;
- SPEC-003 Review;
- Content Extractor;
- Knowledge Document 2.1.0;
- Workspace Context;
- source kind `gutenberg`;
- operational `no_action_required`;
- journal `applied`;
- lock `free`;
- Activity Registry 8/8;
- fingerprint antes/depois idêntico;
- `gate_result.g250_lifecycle_rc_pass=true`.

## Segurança

Runner read-only: sem persistência, writes editoriais/metadata/journal, lock, rede, shortcode ou render dinâmico.

## Evidência

`evidence/g250-lifecycle-rc-pass-20260918T123514Z.json`  
SHA-256 bruto: `c7fa462b7a0e5e0e1307cd63d62a6d98e12ebe44620e9208d9d08dcbecab7c76`.

## Artefato final

`0.4.0-spec004-rc2`: G-250 OFF; E6 OFF; Production Preflight OFF; T100D OFF; Elementor writer OFF. O source tree pode preservar ferramentas históricas, que não entram no artefato instalável quando seus flags estão OFF.
