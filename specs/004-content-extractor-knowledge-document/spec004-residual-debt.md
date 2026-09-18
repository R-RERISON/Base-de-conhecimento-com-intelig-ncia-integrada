# SPEC-004 — Dívida Residual

**Data:** 2026-09-18

## RD-001 — Dependência Elementor

Baseline E6:
- Elementor: 34 artigos;
- mixed: 5 artigos;
- total relacionado a Elementor: 39.

Decisão: manter `Elementor_Adapter`. Remoção física do Elementor exige dependência zero e gate futuro.

## RD-002 — AUTH-UX-001: autorização integrada

Estado atual: a Workspace pode gerar/baixar Authorization Pack individual para comprovar readiness e identidade da autorização.

Direção de produto:
- manter autorização como requisito de segurança;
- eliminar no futuro a necessidade de manipulação manual de arquivo;
- integrar a confirmação à própria Workspace;
- escopo sempre por post;
- apresentar dry-run/impacto antes da confirmação;
- exigir capability + nonce;
- revalidar stale-source imediatamente antes do write;
- usar journal + lock + rollback;
- registrar quem autorizou, quando e qual fingerprint/source hash;
- mixed permanece bloqueado para decisão humana especializada.

Não permitido:
- migração automática ao abrir a tela;
- autorização implícita;
- ação global que escreva em vários posts sem escopo/consentimento explícitos;
- reativar T100D one-shot como produto.

Esta dívida não bloqueia o fechamento da SPEC-004 porque o writer final permanece OFF.
