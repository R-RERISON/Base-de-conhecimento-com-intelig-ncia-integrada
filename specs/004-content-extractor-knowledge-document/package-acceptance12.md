# Package `0.4.0-acceptance.12` — KD 2.1.0 Hierarchy Fidelity

## Objetivo

Validar ambientalmente o primeiro build KD 2.1.0 após o FAIL humano controlado do G-240 v2.

Este build é read-only. **Não autoriza G-245, writer ou migration Elementor.**

## Alterações relevantes

- KD schema `2.1.0`;
- relationship fidelity expected/actual;
- numbered hierarchy resolver conservador;
- proveniência/confiança de hierarquia;
- `cardinality_complete` separado de `relationship_complete`;
- acceptance gate separado em `human_pass`, `system_status`, `gate_pass`.

## Ordem de execução em homologação

### 1. Instalar/atualizar plugin

Confirmar no cabeçalho/ambiente:

- plugin `0.4.0-acceptance.12`;
- KD `2.1.0`;
- `DOMDocument=true`.

### 2. Executar `Validação KD v2`

O nome administrativo permanece por compatibilidade do smoke, mas o documento gerado deve informar `knowledge_document_schema=2.1.0`.

Aceite técnico mínimo antes do A/B humano:

- corpus before == after;
- editorial fingerprint equal;
- changed posts = 0;
- duas passagens com todos os documentos;
- errors = 0;
- throwables = 0;
- hash mismatches = 0;
- canonical JSON mismatches = 0;
- `structure_incomplete = 0` **ou**, se houver, diagnóstico fechado antes de prosseguir;
- `ai_readiness.not_ready = 0` **ou**, se houver, cada ocorrência `HIERARCHY_*` analisada e corrigida antes do A/B.

Salvar o JSON em `evidence/` com timestamp UTC.

### 3. Inspecionar razões novas

Dar atenção específica a:

- `HIERARCHY_EDGE_MISMATCH:*`;
- `HIERARCHY_DEPTH_MISMATCH:*`;
- `HIERARCHY_ORDER_MISMATCH:*`;
- `HIERARCHY_NUMBERING_CONFLICT:*`;
- `HIERARCHY_AMBIGUOUS:*`.

Mismatch explícito é bloqueante. Ambiguidade/conflito numérico deve permanecer `review_required`, nunca ser convertido silenciosamente em `candidate_ready`.

### 4. Só após full-corpus aceitável: executar `Aceitação G-240 KD 2.1`

Usar exatamente os mesmos oito posts e os mesmos fingerprints de baseline.

Marcar somente os quatro critérios humanos:

1. cobertura completa;
2. ordem semântica preservada;
3. nenhum texto inventado;
4. estrutura semântica preservada.

O JSON deve expor separadamente:

- `human_pass`;
- `system_status`;
- `system_blocking`;
- `gate_pass`.

`review_required` não é falha automática. `not_ready` é bloqueante.

### 5. Condição de fechamento do G-240

Somente considerar G-240 PASS quando:

- 8/8 `human_pass=true`;
- 8/8 `gate_pass=true`;
- stale = 0;
- repeatability failures = 0;
- sample mismatches = 0;
- system not ready = 0;
- zero mutação editorial;
- evidências versionadas no PR #3.

Até lá: **PR DRAFT / NÃO MERGEAR / G-245 BLOCKED**.
