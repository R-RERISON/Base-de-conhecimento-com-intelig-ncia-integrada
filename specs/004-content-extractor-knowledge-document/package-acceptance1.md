# Package `0.4.0-acceptance.1` — histórico

O build `0.4.0-acceptance.1` executou o primeiro Real Content Acceptance da SPEC-004 e revelou perda estrutural no Knowledge Document v1.

SHA-256 histórico:

`8391a0c2ace748711087c327a48d63aa2fa009963c2d4584488d2c1ed5679d82`

Resultado ambiental:

- tooling read-only: PASS;
- zero mutação: PASS;
- stale/repeatability/selection: PASS;
- 8/8 casos revisados;
- 0/8 casos aprovados;
- 7/8 com `structure_loss`;
- G-240: **FAIL CONTROLADO**.

Este package não deve ser reutilizado para novo aceite porque contém Knowledge Document schema `1.0.0` e o critério humano subjetivo `acceptable_for_knowledge_use`.

A remediação ativa usa Knowledge Document `2.0.0`; próximo build planejado: `0.4.0-acceptance.2`.
