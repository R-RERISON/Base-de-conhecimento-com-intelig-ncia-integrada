# Roadmap SpecKit — Base de Conhecimento com Inteligência Integrada

> **Mantra:** “Quem não sabe onde está, não sabe para onde quer ir”.

## Estado canônico

1. **SPEC-000 — Inventário Profundo e Contratos** — CONCLUÍDA.
2. **SPEC-001 — Core mínimo + Summary narrativo** — CONCLUÍDA.
3. **SPEC-002 — Classificação de Conhecimento** — CONCLUÍDA.
4. **UX-001 — Product Experience & Knowledge Workspace** — CONCLUÍDA.
5. **SPEC-003 — Review & Governança** — CONCLUÍDA; baseline `0.3.0-rc.1`.
6. **SPEC-004 — Content Extractor e Knowledge Document** — ATIVA.
   - R-200: PASS.
   - R-210: PASS.
   - G-220: PASS.
   - G-230: PASS.
   - G-240: PASS / CLOSED; KD `2.1.0` / `0.4.0-acceptance.12` promovido para `main`.
   - G-245: IN PROGRESS em `spec004-g245-production-readiness`; PR #4 DRAFT.
   - G-250: NOT_RUN.
7. **SPEC-005 — Search lexical + Golden Queries** — planejada; não autorizada enquanto a fronteira atual da SPEC-004 não estiver formalmente fechada para o avanço correspondente.
8. **SPEC-006 — Telemetria / Inteligência de Busca** — planejada.
9. **SPEC-007 — Operações / Jobs / Indexação / Migrações** — planejada.
10. **Semantic Search / Vetores / Hybrid Retrieval** — futuro, condicionado a Golden Queries e métricas.
11. **IA / Foundry / RAG** — futuro, assistivo, governado por custo e evidência.

## Baseline atual da SPEC-004

O Knowledge Document `2.1.0` foi aceito após evolução explícita dos gaps de hierarchy fidelity.

Fechamento G-240:

- corpus 622 → 622;
- duas passagens 622/622;
- zero errors e throwables;
- zero hash/canonical JSON mismatches;
- zero `structure_incomplete`;
- zero `not_ready`;
- fingerprint editorial preservado;
- aceite humano fixo 8/8 para cobertura, ordem, ausência de invenção, estrutura e gate.

A promoção para `main` ocorreu no merge `32a696386bf2ab5574d4d7725db78636fa51f36c`.

## Fronteira G-245

G-245 existe para separar claramente o **knowledge plane read-only** de qualquer futura **migration editorial Elementor**.

A sequência obrigatória é incremental:

1. Production Preflight read-only;
2. matriz de compatibilidade;
3. Projection Plan read-only;
4. gateway Elementor version-gated e disabled-by-default;
5. journal/rollback;
6. stale-source guard;
7. dry-run;
8. batches retomáveis;
9. canário controlado;
10. autorização explícita para qualquer writer real.

T080/Production Preflight já possui evidência em homologação com zero blockers para continuidade read-only. Itens `review_required` permanecem bloqueantes para writer até tratamento explícito.

## Invariantes do roadmap

- sem big-bang;
- vertical slice homologável;
- WordPress-first;
- baseline antes de mudança;
- testes/gates antes de substituição;
- rollback e preservação de dados;
- nenhum runtime fora de SPEC ativa;
- nenhuma capacidade postergada implementada silenciosamente;
- `WP_Post` + Elementor permanecem fonte editorial;
- Knowledge Document, índices, chunks e vetores são derivados reconstruíveis;
- GO de homologação não equivale a GO de produção.

## Regra de alteração

O roadmap pode evoluir por decisão formal, mas nunca por conveniência de implementação. Constituição, Manifesto, decisões das SPECs concluídas e a SPEC ativa prevalecem sobre planejamento histórico.
