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
- T099C: **BLOCKED até autorização específica para a identidade congelada**.

## Evidência T099B
Arquivo: `evidence/g245-t099b-authorization-pack-20260917T193757Z.json`.
SHA-256 bruto: `04d47e7839d67b34af6abf8b4ace87238f934f7ca751bc222e427206d813d8ce`.

Authorization Pack ambiental:
- post_id: 358;
- título: `LIA | Laboratório de Inteligência Analítica`;
- source: legacy_html;
- status: publish;
- risk: low;
- content: 1764 bytes;
- links 2 / images 0 / tables 1;
- shortcodes 0 / dangerous tags 0 / Core block comments 0;
- `_elementor_data` vazio;
- expected block: `core/freeform`.

Hashes congelados:
- fidelity `5e4695f159494ad3a1715741bdf485da43d77a991f9f39ecdab02901d6b2bd2e`;
- serialization `9e96a95d451c9463fa6bf37d7eec31c005df39da1774bd8de7ae110ee72f91cf`;
- dry-run `b533eb953b705b85fd48c3ab26c9bc5d6b61ee4223449370662f6e606123fab3`;
- current post_content `eb7f1c9c4e5426c9c5c473ade6c6b02312f526cad9a375b3bbe30ef0221479d0`;
- expected serialized post_content `af4101dda487e6f6239b8bb0546439a75e1691062fd27f9322cbb0d9c2f84050`.

`authorization_id`: `1557c1ee50e1a7a46df7d7952032cb1dd0cb374c7222de8656bb9c055f561bc9`.

## T099C — boundary atual
Escopo permitido somente após autorização explícita correspondente ao ID acima:
1. post 358 somente;
2. stale-source/hash recheck imediatamente antes da mutação;
3. durable journal persistido antes do write;
4. lock exclusivo;
5. write somente em `WP_Post.post_content`;
6. `_elementor_data` preservado;
7. verificação do hash esperado após apply;
8. rollback imediato obrigatório;
9. verificação byte-exata da restauração;
10. lock liberado e trilha durável mantida.

Qualquer mudança no artigo/hashes antes do T099C invalida a autorização e exige novo T099B.

## Depois do T099C
- se apply + verify + rollback PASS: T100 batch controlado;
- T101: dependência residual Elementor / gate de retirada;
- G-250 Lifecycle/RC.

## Guardrails absolutos
- UX-002 intacta.
- plugin Gutenberg não é dependência.
- Elementor não é removido agora.
- mixed exige humano.
- nenhum write editorial sem gate + autorização específica.
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
