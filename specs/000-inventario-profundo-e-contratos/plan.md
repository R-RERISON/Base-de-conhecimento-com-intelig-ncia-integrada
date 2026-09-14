# Plano — SPEC-000 Inventário Profundo e Contratos

## Estado
- T000–T059 concluídos documentalmente.
- T090 WordPress-first: PASS.
- T091 Simplicidade: PASS.
- T092 Segurança: PASS arquitetural.
- T093 QA/Regressão: PASS documental.
- Próximos: T094 -> T095 -> T096 -> T097.

## Arquitetura preservada
- WordPress/Elementor como fonte editorial.
- primeiro runtime por vertical slice mínimo.
- candidato atual para SPEC-001: Core mínimo + Summary narrativo, ainda sujeito a T094/T095/T097.
- Content Extractor somente com consumidor real.
- Search própria posterior e incremental, post-level antes de item-level quando suficiente.
- Search Retrieval Projection continua única família persistente própria aprovada.
- Analytics, durable queue, semantic/vector/rerank/agentes permanecem postergados.

## Resultado T093
Artefato: `revisao-qa-t093.md`.

### Evidência por slice
Cada SPEC futura deve possuir Matriz de Evidência antes do código, com:
- contrato/gate;
- classe;
- cenário;
- tipo de teste;
- evidência esperada;
- estado;
- artefato/execução.

Estados válidos: PASS, FAIL, NOT_RUN, NOT_CONFIGURED, STALE, N/A, POSTERGADO, WAIVED.

PASS vazio é proibido. N/A exige justificativa. Evidência stale não vale como atual.

### Primeiro Summary slice
Gates mínimos:
- G-001 editorial;
- G-020 Summary;
- G-070 segurança/scope;
- G-110 UI/UX;
- G-130 lifecycle/release quando aplicável;
- B-006 no write composto.

Cenários negativos obrigatórios quando aplicáveis:
- capability;
- nonce/CSRF;
- mutação por GET;
- IDOR;
- mass assignment;
- XSS/escaping;
- falha parcial/read-after-write.

### Search
Quando nascer: G-010/G-050/G-060/Golden/G-070/G-120/G-130 + B-001. Golden vazia/not-run/stale nunca PASS.

### Features postergadas
Não criar harness de IA/vector/queue/Analytics antes da capacidade. Manter gates documentais e ativá-los junto do slice real.

## Gate
Nenhum runtime/teste executável é autorizado antes de T097.

## Próximo passo — T094
Revisar valor de produto/gestão de conhecimento e confirmar ou alterar a recomendação de primeiro slice.