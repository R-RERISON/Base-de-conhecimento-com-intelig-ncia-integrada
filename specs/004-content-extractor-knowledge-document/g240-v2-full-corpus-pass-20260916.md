# G-240 v2 — Full-corpus structural validation PASS

Data: 2026-09-16

## Evidência

Arquivo: `evidence/kd-v2-smoke-20260916T150651Z.json`.

Ambiente:

- WordPress 6.9.4
- PHP 8.5.10
- Elementor 4.1.0
- plugin 0.4.0-acceptance.11
- Knowledge Document 2.0.1
- DOMDocument=true

## Segurança

- read-only design=true
- fingerprint editorial before/after idêntico
- changed_posts_during_run=0
- corpus 622→622
- nenhuma persistência de documento/hash

## Determinismo

- first_pass_documents=622
- second_pass_documents=622
- errors=0
- throwables=0
- hash_mismatches=0
- canonical_json_mismatches=0

## Fidelidade estrutural por cardinalidade

- first_pass_structure_incomplete=0
- second_pass_structure_incomplete=0
- nenhuma assinatura de mismatch residual
- ai_readiness.not_ready=0
- candidate_ready=548
- review_required=72
- not_applicable=2

`gate.pass=true` para o smoke full-corpus `1.3.0`.

## Limitação descoberta posteriormente no A/B humano

O aceite humano `evidence/g240-v2-acceptance-20260916T153610Z.json` demonstrou que o PASS acima prova **cardinalidade estrutural**, mas não necessariamente fidelidade de relações hierárquicas.

Resultado humano:

- estrutura preservada 5/8;
- estrutura perdida 3/8 (1290, 370, 1307).

Portanto este documento permanece válido como evidência de full-corpus técnico do KD `2.0.1`, mas **não fecha G-240**.

A análise de relacionamento/hierarquia está em `g240-v2-hierarchy-gap-analysis-20260916.md` e conduz à proposta KD `2.1.0`.
