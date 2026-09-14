# Plano — SPEC-000 Inventário Profundo e Contratos

## Estado final
**SPEC-000 — CONCLUÍDA em T097.**

Todas as fases T000–T059 e revisões T090–T097 foram concluídas documentalmente sem criar runtime novo.

## Decisão T097
Artefato: `decisao-t097.md`.

**GO formal para abrir e executar a SPEC-001**, restrita a:

- Core mínimo;
- Summary narrativo;
- `objective`, `escalation`, `important`;
- meta keys GRE existentes;
- wp-admin server-rendered;
- segurança, B-006, QA e lifecycle definidos.

O GO não é autorização de produção/cutover e não inclui capacidades adjacentes.

## Próximo ciclo
Criar `specs/001-core-summary-narrativo/`.

Antes de código, a SPEC-001 deve concluir seu próprio Definition of Ready:
- baseline/HEAD;
- post types reais suportados;
- contratos dos três campos;
- Matriz de Mutação;
- Matriz de Evidência;
- critérios de aceite/não aceite;
- rollback e coexistência.

A SPEC-000 permanece como memória institucional e fonte de contratos para as futuras SPECs.