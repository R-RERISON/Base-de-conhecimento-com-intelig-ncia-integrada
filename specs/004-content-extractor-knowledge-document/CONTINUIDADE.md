# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- baseline consolidado em `main`: commit `a676f8daaaf9a794d503ccd6a2c28178be1fb8bf`.
- R-200: **PASS**.
- R-210: **PASS**.
- G-220 — Content Extractor: **PASS ambiental**.
- G-230/v1 — Knowledge Document determinístico: **PASS ambiental**; v1 permanece `SUPERSEDED_FOR_AI` após G-240 v1.
- G-240/v1 — Real Content Acceptance: **FAIL CONTROLADO — perda estrutural**.
- G-240/v2 — full-corpus `2.0.1` passou tecnicamente, mas o aceite humano A/B permaneceu **FAIL CONTROLADO — HIERARCHY FIDELITY**.
- evidência humana atual: `evidence/g240-v2-acceptance-20260916T153610Z.json`.
- análise: `g240-v2-hierarchy-gap-analysis-20260916.md`.
- build validado: `0.4.0-acceptance.11` / Knowledge Document `2.0.1` / smoke `1.3.0`.
- branch: `spec004-g240-real-content-acceptance`.
- PR #3: **DRAFT / NÃO MERGEAR** antes de G-240 v2 PASS humano.
- G-245 — Elementor/produção: **BLOCKED por G-240**; writer proibido.
- G-250: **NOT_RUN**.

## Diagnóstico atual

O full-corpus do KD `2.0.1` atingiu:

- 622/622 documentos em duas passagens;
- zero errors/throwables;
- zero hash/canonical JSON mismatch;
- zero `structure_incomplete`;
- zero `not_ready`;
- `gate.pass=true`.

Entretanto o aceite humano A/B sobre os mesmos oito posts do G-240 v1 mostrou:

- cobertura completa: 8/8;
- ordem preservada: 8/8;
- nenhum texto inventado: 8/8;
- estrutura preservada: **5/8**;
- estrutura perdida: **3/8** — posts 1290, 370 e 1307;
- stale=0, repeatability failure=0, sample mismatch=0;
- gate humano=false.

Conclusão: `structure_complete` atual valida cardinalidade, mas não fidelidade de relações hierárquicas.

## Gap de hierarchy fidelity

O modelo atual reconstrói `children` apenas quando a relação é explicitamente representável via `parent_item_id`/`item_id` de listas aninhadas. Ele não infere hierarquia textual por numeração como `1`, `1.1`, `1.2`, `1.2.1`.

Isso explica por que alguns conteúdos, como o post 28748, ficam bem estruturados quando o markup fornece relação resolvível, enquanto conteúdos historicamente planos podem preservar todos os itens e ainda perder parent/child.

Há ainda um defeito no acceptance runner: `review_required` é tratado como `system_ready=false`, mesmo quando os quatro critérios humanos passam. Assim 36431, 1289 e 28748 ficaram `pass=false` apesar de aceitação humana estrutural positiva. `review_required` deve permanecer explícito, porém não equivaler automaticamente a `not_ready`.

## Próxima evolução proposta — Knowledge Document `2.1.0`

1. adicionar relationship fidelity ao gate:
   - parent edges de listas;
   - max depth;
   - sibling order;
   - assinatura determinística da árvore;
   - relações de heading quando aplicável;
2. implementar resolver conservador de numeração hierárquica (`1`, `1.1`, `1.2`, `1.2.1`) apenas quando houver sinal forte e não ambíguo;
3. DOM explícito sempre vence inferência textual;
4. conflito vira `HIERARCHY_NUMBERING_CONFLICT` + `review_required`;
5. sinal forte não resolvido vira `HIERARCHY_AMBIGUOUS` e não pode ser `candidate_ready`;
6. adicionar proveniência de hierarquia (`explicit_dom|numbering_inferred|heading_inferred|flat`) e confiança;
7. ajustar acceptance gate para permitir `review_required` humanamente aprovado, mantendo `not_ready` bloqueante;
8. repetir full-corpus e depois os mesmos oito A/B.

## Invariantes preservados

- construção read-only;
- nenhuma persistência de documento/hash/cache/progresso durante validação;
- nenhum write em `post_content`, `_elementor_data`, status, revisão ou publicação;
- nenhum `do_shortcode()` genérico;
- nenhum `render_block()` ou renderização dinâmica arbitrária;
- nenhuma dependência de IA, Foundry, embeddings ou vetores para parsing;
- instalação/activation/update nunca migra posts automaticamente;
- G-245 e writer Elementor permanecem bloqueados.

## Gates

- R-200: **PASS**.
- R-210: **PASS**.
- G-220: **PASS**.
- G-230/v1: **PASS determinístico**.
- G-240/v1: **FAIL CONTROLADO — STRUCTURE LOSS**.
- G-240/v2 (`2.0.1`): **FAIL CONTROLADO — HIERARCHY FIDELITY**.
- G-245: **BLOCKED**.
- G-250: **NOT_RUN**.
