# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Repositório / branch
- Repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`.
- Branch ativa: `spec004-g245-production-readiness`.
- PR #4: DRAFT / NÃO MERGEAR.
- SPEC ativa: SPEC-004.

## Estado atual
- UX-002 `0.4.0-ux002.3`: PASS/CLOSED, contrato visual obrigatório.
- G-240: PASS/CLOSED/main.
- ADR-004-001: Core Blocks como destino editorial canônico.
- T091/T093/T094/T096/T097/T098.2/T099A/T099B: PASS AMBIENTAL.
- T095: PASS LOCAL / READ-ONLY.
- T098.1: FAIL CONTROLADO / SEM MUTAÇÃO.
- T099C: **AUTHORIZED / EXECUTION PENDING**.

## Evidência T099B
Arquivo: `evidence/g245-t099b-authorization-pack-20260917T193757Z.json`.
SHA-256 bruto: `04d47e7839d67b34af6abf8b4ace87238f934f7ca751bc222e427206d813d8ce`.

Canário congelado:
- post_id 358;
- `LIA | Laboratório de Inteligência Analítica`;
- legacy_html / low risk;
- expected block `core/freeform`;
- authorization_id `1557c1ee50e1a7a46df7d7952032cb1dd0cb374c7222de8656bb9c055f561bc9`.

## Autorização T099C
Registro: `t099c-authorization-20260917.md`.
Autorização humana recebida para o escopo exato acima, com apply, verificação e rollback imediato.

Build executável: `0.4.0-g245-canary-t099c.1`.

Fluxo obrigatório:
1. capabilities `manage_options` + `edit_post` + `unfiltered_html`;
2. journal/lock preexistentes = zero/free;
3. authorization_id revalidado;
4. lock exclusivo;
5. source/dry-run/stale recheck sob lock;
6. journal durável persistido;
7. stale/auth recheck imediatamente antes do write;
8. `wp_update_post()` de um único `post_content`;
9. verificar serialized SHA-256, `core/freeform` e payload original;
10. persistir applied/partial_failure;
11. rollback imediato via `wp_update_post()` para o conteúdo original;
12. verificar post_content e `_elementor_data` originais;
13. persistir rolled_back;
14. liberar lock;
15. journal permanece como audit trail.

Observação: `wp_update_post()` pode produzir os efeitos normais do WordPress em `post_modified` e revisões. O conteúdo editorial do post deve terminar byte-exatamente igual ao snapshot anterior.

## Depois do T099C
- se PASS ambiental: versionar evidência e desenhar T100 batch controlado;
- se FAIL: nenhum batch, analisar estágio e journal durável;
- T101: dependência residual Elementor / gate de retirada;
- G-250 Lifecycle/RC.

## Guardrails absolutos
- UX-002 intacta.
- plugin Gutenberg não é dependência.
- Elementor não é removido agora.
- mixed exige humano.
- nenhum batch autorizado ainda.
- qualquer drift invalida a autorização antes do write.
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
