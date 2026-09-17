# T100A — Batch Authorization Pack Contract v1

Status: IMPLEMENTADO / READ-ONLY / HOMOLOGAÇÃO PENDENTE  
Data: 2026-09-17

## Objetivo

Selecionar deterministicamente o primeiro lote controlado de 5 artigos `legacy_html` low-risk após o PASS ambiental do T099C e congelar uma autorização coletiva por hashes exatos. T100A não executa write, não persiste journal e não adquire lock.

## Pré-requisito

T099C deve estar PASS AMBIENTAL com apply verificado e rollback byte-exato no post 358.

## Seleção

Somente posts que atendam simultaneamente:

- `source_kind=legacy_html`;
- `dry_run_status=ready`;
- zero eventos em `_bdc_kb_block_migration_journal`;
- lock Block Migration `free`;
- `_elementor_data` vazio;
- `post_content` entre 1 e 30.000 bytes;
- zero shortcode registrado;
- zero `script|iframe|form|object|embed|style`;
- zero `<!-- wp:`;
- no máximo 10 links, 1 imagem e 1 tabela.

Ordenação: `risk_score ASC`, depois `post_id ASC`. Lote: exatamente 5 itens.

O post 358 é naturalmente excluído enquanto mantiver o journal de auditoria persistente do T099C.

## Identidade por item

Cada item congela:

- `post_id`;
- `fidelity_hash_before`;
- `serialization_hash`;
- `dry_run_hash`;
- `post_content_sha256_before`;
- `elementor_data_sha256_before`;
- `serialized_post_content_sha256_expected`;
- `expected_block_name=core/freeform`;
- `item_authorization_id = SHA-256 canonical` do payload T100B correspondente.

## Identidade do lote

`batch_authorization_id` é o hash canônico de:

- `gate=T100B`;
- `batch_size=5`;
- `execution_mode=sequential_apply_verify_immediate_rollback_stop_on_first_failure`;
- lista ordenada de `post_id + item_authorization_id`.

Qualquer drift de qualquer item invalida o lote inteiro antes do write.

## Futuro T100B

Se explicitamente autorizado pelo `batch_authorization_id`, o T100B deverá:

1. executar sequencialmente (`concurrency=1`);
2. revalidar identidade antes de cada item;
3. adquirir lock exclusivo por item;
4. persistir journal durável antes do write;
5. fazer stale-source recheck imediatamente antes do write;
6. aplicar Core Blocks apenas em `WP_Post.post_content`;
7. verificar SHA-256 e `core/freeform`;
8. fazer rollback imediato do item mesmo no caminho de sucesso;
9. verificar restauração byte-exata de `post_content` e `_elementor_data`;
10. persistir estado `rolled_back`;
11. liberar lock;
12. interromper o lote na primeira falha.

## Negação / segurança

T100A NÃO:

- chama `wp_update_post()`;
- persiste journal;
- adquire lock;
- escreve `_elementor_data`;
- executa shortcodes;
- renderiza blocos;
- chama rede externa;
- exporta corpo editorial ou URLs.

## Gate

PASS somente se exatamente 5 candidatos low-risk forem congelados e `t100a_batch_authorization_pack_pass=true`.
