# G-240 — Homologação

Estado atual: **FAIL CONTROLADO — HIERARCHY FIDELITY**.

O full-corpus do KD `2.0.1` no build `0.4.0-acceptance.11` passou tecnicamente em segurança, determinismo, cardinalidade estrutural e `not_ready=0`. Porém o aceite humano A/B dos mesmos oito posts do G-240 v1 identificou perda de hierarquia em 3/8 casos.

Evidências:

- `evidence/kd-v2-smoke-20260916T150651Z.json` — full-corpus técnico PASS;
- `evidence/g240-v2-acceptance-20260916T153610Z.json` — humano A/B FAIL;
- `g240-v2-hierarchy-gap-analysis-20260916.md` — causa e proposta KD `2.1.0`.

Conclusão operacional:

- não executar G-245;
- não autorizar writer/migration Elementor;
- não considerar `structure_complete=true` suficiente enquanto ele validar somente contagens;
- evoluir para relationship fidelity + resolução conservadora de hierarquia numérica;
- corrigir o acceptance gate para não tratar `review_required` como sinônimo automático de falha quando os quatro critérios humanos passam.

A próxima rodada deve usar nova versão do KD (`2.1.0`) e repetir o full-corpus e os mesmos oito A/B.
