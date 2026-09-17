# Knowledge Document 2.1.0 — Draft de Hierarchy Fidelity

Status: DRAFT — derivado do FAIL humano G-240 v2 em 2026-09-16.

## Objetivo

Evoluir o KD 2.0.1 de fidelidade por cardinalidade para fidelidade de **relações hierárquicas**, mantendo construção read-only, determinística e independente de IA.

## Princípios

1. DOM explícito é autoridade primária.
2. Contagens estruturais continuam obrigatórias, mas não suficientes.
3. Relações pai→filho, profundidade e ordem entre irmãos tornam-se parte do contrato.
4. Inferência por numeração só ocorre quando o sinal é forte, coerente e não ambíguo.
5. Nunca inferir hierarquia por token isolado que possa ser versão, IP, data, código ou outro identificador.
6. Toda relação inferida carrega proveniência e confiança.
7. Conflito nunca é silenciosamente resolvido; vira warning/readiness explícito.

## Relationship Fidelity

Adicionar métricas/assinaturas esperadas e atuais para listas:

- `list_parent_edges`;
- `list_root_count`;
- `list_max_depth`;
- `list_sibling_order_signature`;
- `list_tree_signature` determinística.

Para headings:

- `heading_path_transitions`;
- `heading_parent_edges` quando aplicável;
- `heading_tree_signature`.

Mismatch de relação deve gerar warning bloqueante específico, por exemplo:

- `HIERARCHY_EDGE_MISMATCH:*`;
- `HIERARCHY_DEPTH_MISMATCH:*`;
- `HIERARCHY_ORDER_MISMATCH:*`.

## Numbered Hierarchy Resolver

Primeira gramática autorizada:

- `1`;
- `1.1`;
- `1.2`;
- `1.2.1`.

### Condições de inferência

- token precisa ocorrer no início do texto semanticamente materializado;
- precisa haver separador claro entre token e conteúdo;
- o parent prefix deve ter aparecido anteriormente no mesmo contexto;
- não cruzar `heading_path`, tabela, source boundary ou container explicitamente incompatível;
- exigir coerência de sequência mínima; um token isolado não basta;
- DOM explícito sempre vence.

### Proveniência

Cada nó/relação deve indicar:

- `hierarchy_source`: `explicit_dom|numbering_inferred|heading_inferred|flat`;
- `hierarchy_confidence`: `authoritative|deterministic|ambiguous`.

### Conflitos e ambiguidade

- DOM explícito × numeração conflitante → `HIERARCHY_NUMBERING_CONFLICT` + `review_required`;
- sinal forte sem parent resolvível → `HIERARCHY_AMBIGUOUS` + no mínimo `review_required`;
- perda comprovada de relação explícita → `not_ready`.

## AI Readiness

`candidate_ready` exige:

- cardinalidade estrutural completa;
- relationship fidelity completa para estruturas explícitas;
- nenhum sinal hierárquico forte não resolvido;
- nenhuma razão crítica existente.

## Acceptance Gate

O G-240 humano deve separar:

- `human_pass` — quatro critérios observáveis;
- `system_status` — `candidate_ready|review_required|not_ready|not_applicable`;
- `gate_pass`.

`review_required` não falha automaticamente um slot humanamente aprovado; suas limitações devem permanecer registradas e auditáveis. `not_ready` continua bloqueante.

## Não objetivos

- não inferir semântica por LLM;
- não reescrever texto;
- não converter HTML/editorial para Elementor;
- não usar embeddings para determinar hierarquia;
- não suportar alfabetos/romanos/outros padrões de numeração sem evidência real adicional.
