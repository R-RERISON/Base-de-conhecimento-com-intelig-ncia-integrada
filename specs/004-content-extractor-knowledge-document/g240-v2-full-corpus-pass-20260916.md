# G-240 v2 — Full-corpus structural validation PASS

Data: 2026-09-16

## Evidência

Arquivo: `evidence/kd-v2-smoke-20260916T150651Z.json`

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

## Fidelidade estrutural

- first_pass_structure_incomplete=0
- second_pass_structure_incomplete=0
- nenhuma assinatura de mismatch residual
- ai_readiness.not_ready=0
- candidate_ready=548
- review_required=72
- not_applicable=2

Os `review_required` restantes são limitações explícitas e auditáveis, principalmente shortcodes não expandidos, headings locais achatados e listas dentro de tabelas achatadas; não são aceitos como perda estrutural silenciosa.

## Gate

`gate.pass=true`.

O full-corpus do KD v2 está encerrado como PASS ambiental. Nenhuma alteração adicional no parser deve ser feita antes da aceitação humana A/B, salvo nova evidência objetiva de regressão.

## Próximo passo

Executar `Aceitação G-240 v2` no mesmo build `0.4.0-acceptance.11`, usando exatamente os oito posts congelados do G-240 v1.

Critérios humanos por slot:

1. cobertura completa;
2. ordem semântica preservada;
3. nenhum texto inventado;
4. estrutura semântica preservada.

AI readiness permanece calculado pelo sistema e não é checkbox humano.

G-245 e qualquer writer/migration Elementor permanecem bloqueados até a evidência humana A/B ser aceita.