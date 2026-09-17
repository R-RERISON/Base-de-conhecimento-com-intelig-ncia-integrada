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
- T099C: BLOCKED por autorização humana específica.

## T099B — PASS AMBIENTAL
Evidência: `evidence/g245-t099b-authorization-pack-20260917T193757Z.json`.
SHA-256 bruto: `04d47e7839d67b34af6abf8b4ace87238f934f7ca751bc222e427206d813d8ce`.

Ambiente: WordPress 6.9.4 / PHP 8.5.10 / build `0.4.0-g245-authpack-t099b.1`.

Seleção:
- 623 candidatos avaliados;
- 116 candidatos low-risk;
- post 358 reutilizado do T099A;
- título `LIA | Laboratório de Inteligência Analítica`;
- publish / legacy_html / low risk;
- 1764 bytes, 2 links, 0 imagens, 1 tabela;
- 0 shortcodes registrados;
- 0 tags perigosas/embed/script;
- 0 comentários Core Blocks;
- `_elementor_data` vazio.

Identidade congelada:
- `fidelity_hash_before=5e4695f159494ad3a1715741bdf485da43d77a991f9f39ecdab02901d6b2bd2e`;
- `serialization_hash=9e96a95d451c9463fa6bf37d7eec31c005df39da1774bd8de7ae110ee72f91cf`;
- `dry_run_hash=b533eb953b705b85fd48c3ab26c9bc5d6b61ee4223449370662f6e606123fab3`;
- `post_content_sha256_before=eb7f1c9c4e5426c9c5c473ade6c6b02312f526cad9a375b3bbe30ef0221479d0`;
- `serialized_post_content_sha256_expected=af4101dda487e6f6239b8bb0546439a75e1691062fd27f9322cbb0d9c2f84050`;
- expected block: `core/freeform`;
- `authorization_id=1557c1ee50e1a7a46df7d7952032cb1dd0cb374c7222de8656bb9c055f561bc9`.

T099B foi read-only: journal não persistido, lock não adquirido, `post_content` e `_elementor_data` não escritos, sem shortcode/block render e sem rede. Gate `t099b_authorization_pack_pass=true`.

## T099C — próximo gate
Nenhum writer editorial está autorizado ainda.

Após autorização explícita que cite o post e o `authorization_id` acima, o canário poderá somente:
1. revalidar a identidade atual;
2. persistir journal durável;
3. adquirir lock exclusivo;
4. revalidar stale-source;
5. escrever a serialização determinística em `WP_Post.post_content`;
6. preservar `_elementor_data`;
7. verificar o estado aplicado;
8. executar rollback imediato para o conteúdo anterior;
9. verificar restauração exata;
10. liberar lock e manter a trilha durável de auditoria.

## Próximos passos
- obter autorização humana específica para T099C;
- preparar build executável apenas para essa identidade;
- executar apply + verify + rollback imediato;
- T100 batches homologados;
- T101 inventário residual Elementor / gate de retirada;
- G-250 Lifecycle/RC.

## Guardrails
- UX-002 não pode regredir.
- plugin Gutenberg não é dependência.
- Elementor não é removido antes de dependência zero.
- source mixed continua humano.
- autorização é vinculada ao `authorization_id`; qualquer drift invalida o canário.
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
