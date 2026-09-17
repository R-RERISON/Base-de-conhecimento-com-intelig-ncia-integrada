# T100D — Single Post Core Blocks Executor Contract v1

**Status:** FROZEN / BLOCKED BY EXPLICIT AUTHORIZATION  
**ADR:** ADR-004-002  
**Action contract:** `core_blocks_migrate_v1`

## Objetivo

Executar a primeira migração **persistente** de um único artigo para WordPress Core Blocks dentro da Post Management Workspace, sem lote e sem autorização implícita.

## Escopo autorizado

O executor somente pode rodar quando a autorização explícita corresponder exatamente a:

- `post_id`;
- `authorization_id` emitido pelo T100C;
- action contract `core_blocks_migrate_v1`.

Qualquer drift de source, serializer, dry-run ou serialized content invalida o authorization_id antes do write.

## Semântica de execução

1. validar manage_options + edit_post + unfiltered_html;
2. validar authorization_id recebido;
3. recomputar T100C assessment;
4. exigir `ready_for_authorization`;
5. exigir lock livre;
6. adquirir lock exclusivo;
7. recomputar identidade sob lock;
8. stale-source recheck;
9. persistir durable journal `prepared` com rollback capsule;
10. stale/auth recheck final imediatamente antes do write;
11. persistir **somente** `WP_Post.post_content` usando WordPress Core API;
12. preservar `_elementor_data` byte-identical;
13. verificar SHA-256 serializado, block name esperado e payload lossless;
14. persistir journal `applied`;
15. liberar lock;
16. manter a migração aplicada se toda a verificação passar.

## Rollback

- Qualquer falha após o write deve executar rollback automático para o snapshot pré-write.
- Rollback deve ser verificado por hash.
- O journal deve registrar `rolled_back` quando houver rollback automático.
- Se o apply passar, o estado final esperado é `applied`; o rollback capsule permanece auditável para uma ação de rollback post-scoped futura.

## Proibições

- nenhum batch;
- nenhum segundo post;
- nenhum writer para `_elementor_data`;
- nenhuma remoção de Elementor;
- nenhuma execução de shortcode;
- nenhum render de block dinâmico;
- nenhuma rede externa;
- nenhuma reutilização de autorização T099C;
- nenhuma autorização por similaridade de título, hash parcial ou estado anterior.

## T100D alvo atual

T100C PASS ambiental congelou:

- post_id: `358`;
- authorization_id: `17c002d3ccc770c6ef154fdbed28cbd0c8d198411c84e168fe9aadb1b7a41af0`;
- source: `legacy_html`;
- expected block: `core/freeform`;
- dry-run: `ready`;
- journal history: `rolled_back`;
- lock: `free`.

## Autorização necessária

A autorização deve declarar explicitamente que a migração será **persistente em caso de PASS** e que ocorrerá rollback automático apenas em falha de verificação.

Nenhum executável T100D deve ser habilitado antes dessa autorização.
