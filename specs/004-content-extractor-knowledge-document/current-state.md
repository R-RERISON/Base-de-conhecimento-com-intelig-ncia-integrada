# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline e gates
- SPEC-001/002/003: concluídas.
- UX-001/UX-002: concluídas; UX-002 `0.4.0-ux002.3` é contrato visual obrigatório.
- G-240: PASS / CLOSED / promovido para `main`.
- KD 2.1.0: PASS técnico full-corpus + PASS humano 8/8.
- G-245: REBASELINED / IN PROGRESS; PR #4 DRAFT / NÃO MERGEAR.
- ADR-004-001: Core Blocks como destino editorial canônico.
- T091/T093/T094/T096/T097/T098.2/T099A/T099B: PASS AMBIENTAL.
- T095: PASS LOCAL / READ-ONLY.
- T098.1: FAIL CONTROLADO / SEM MUTAÇÃO.
- T099C: **AUTHORIZED / EXECUTION PENDING**.

## T099B — PASS AMBIENTAL
Evidência: `evidence/g245-t099b-authorization-pack-20260917T193757Z.json`.
SHA-256 bruto: `04d47e7839d67b34af6abf8b4ace87238f934f7ca751bc222e427206d813d8ce`.

Candidato congelado:
- post 358 — `LIA | Laboratório de Inteligência Analítica`;
- publish / legacy_html / low risk;
- 1764 bytes, 2 links, 0 imagens, 1 tabela;
- 0 shortcodes, 0 tags perigosas, 0 comentários Core Block;
- `_elementor_data` vazio;
- expected block: `core/freeform`;
- `authorization_id=1557c1ee50e1a7a46df7d7952032cb1dd0cb374c7222de8656bb9c055f561bc9`.

## T099C — autorizado
Registro: `t099c-authorization-20260917.md`.
Build preparada: `0.4.0-g245-canary-t099c.1`.

Escopo exato:
1. post 358 somente;
2. revalidar authorization_id/hashes;
3. exigir `manage_options`, `edit_post` e `unfiltered_html`;
4. lock exclusivo;
5. journal durável antes do write;
6. stale-source recheck imediatamente antes da mutação;
7. `wp_update_post()` apenas em `WP_Post.post_content`;
8. verificar SHA-256 e `core/freeform`;
9. persistir evento applied/partial_failure;
10. rollback imediato obrigatório para o post_content original;
11. verificar post_content e `_elementor_data` byte-exatos após rollback;
12. persistir `rolled_back`, liberar lock e manter journal de auditoria.

Side effects normais do WordPress (`post_modified` e revisões) podem ocorrer; não fazem parte da restauração byte-exata exigida para `post_content`. Nenhum batch está autorizado.

## Próximos passos
- instalar/executar T099C em homologação;
- versionar o JSON ambiental;
- somente se T099C PASS: desenhar T100 batch controlado;
- T101 inventário residual Elementor / gate de retirada;
- G-250 Lifecycle/RC.

## Guardrails
- UX-002 não pode regredir.
- plugin Gutenberg não é dependência.
- Elementor não é removido antes de dependência zero.
- source mixed continua humano.
- autorização T099C é vinculada à identidade congelada; qualquer drift aborta antes do write.
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
