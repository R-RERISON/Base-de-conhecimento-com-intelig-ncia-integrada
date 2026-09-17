# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline e gates
- SPEC-001/002/003: concluídas.
- UX-001/UX-002: concluídas; UX-002 `0.4.0-ux002.3` é contrato visual obrigatório.
- G-240: PASS / CLOSED / promovido para `main`.
- KD 2.1.0: PASS técnico full-corpus + PASS humano 8/8.
- G-245: REBASELINED / IN PROGRESS; PR #4 DRAFT / NÃO MERGEAR.
- ADR-004-001: Core Blocks como destino editorial canônico.
- T091/T093/T094/T096/T097: PASS AMBIENTAL.
- T095: PASS LOCAL / READ-ONLY.
- T098.1: **FAIL CONTROLADO / SEM MUTAÇÃO**.
- T098.2: **CORRIGIDO / REHOMOLOGAÇÃO PENDENTE**.

## T098.1
Evidência: `evidence/g245-t098-readiness-fail-20260917T190620Z.json`.
SHA-256 bruto: `b85e22c338d72f52dc11b3d113058618108b9dd20778b67a3062839c8eaba44d`.

Resultado: corpus 623; first/second pass 0; first/second throwables 623; safety violations 0; corpus/fingerprint unchanged; gate false.

Causa raiz: mismatch entre T098 e contratos T097:
1. `Core_Block_Editorial_Parity::validate()` inexistente; usar `assess()`;
2. `Block_Migration_Stale_Source_Guard::inspect_post()` inexistente; usar `assess()`;
3. `is_fresh` inexistente; usar `status=fresh`.

## T098.2
Pipeline corrigido:
`Migration Fidelity Source -> Lossless Serializer -> parse_blocks -> Editorial Parity assess -> rebuild current source -> Stale Guard assess -> Dry Run simulate`.

Validação local:
- 28/28 assertions PASS;
- 50 PHP lint PASS pré/pós ZIP;
- UX-002 byte parity PASS;
- writer/migration OFF;
- journal store e lock não invocados pelo smoke.

Pacote: `0.4.0-g245-readiness-t098.2`.
SHA-256: `a37ebc3ff018f71c96546e56e2b8434db53ea148b7d9d253db0332b1d389cef7`.

## Próximo passo
Reexecutar T098.2 em homologação. Se PASS: T099A journal store + lock com cleanup, T099B Authorization Pack e T099C canário real somente com autorização específica.

## Guardrails
- UX-002 não pode regredir;
- plugin Gutenberg não é dependência;
- Elementor não é removido antes de dependência zero;
- source mixed continua humano;
- nenhum write editorial autorizado nesta fase;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
