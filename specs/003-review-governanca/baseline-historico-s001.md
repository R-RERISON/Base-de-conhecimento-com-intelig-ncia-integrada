# SPEC-003 — Baseline histórico S001

## Escopo

Este documento consolida o que já está comprovado no inventário histórico do KB2Ops antes do profiling real do banco atual. Ele **não autoriza** estado canônico, migração, writer ou primitive.

## Baseline funcional

- plugin atual limpo: `0.2.0-rc.1`;
- SPEC-001 Summary: concluída;
- SPEC-002 Classificação: concluída;
- UX-001: baseline v1 congelada;
- SPEC-003 começa sem writer novo.

## Stores históricos candidatos

| Store | Semântica histórica | Tipo observado |
|---|---|---|
| `_kb2ops_review_state` | estado de revisão | string |
| `_kb2ops_review_notes` | notas humanas | texto |
| `_kb2ops_reviewed_at` | instante de revisão | timestamp/string |
| `_kb2ops_reviewed_by` | revisor | WP user ID/integer |
| `_kb2ops_include_ai` | decisão humana de inclusão futura em IA | boolean |
| `_kb2ops_review_history` | histórico bounded | array serializado, histórico até 50 eventos na baseline KB2Ops |

Também existia `_kb2ops_view_count`, mas ele pertence a Analytics/telemetria e **não** entra no domínio canônico de Review/Governança desta slice.

## Estados históricos

O KB2Ops utilizava:

- `unreviewed`;
- `in_review`;
- `approved`;
- `excluded`.

Esses valores são candidatos para profiling. Não são promovidos automaticamente ao novo domínio.

## Writer histórico

`Knowledge::save_review()` persistia estado/tipo/classificações/notas/include_ai/reviewer/data, acrescentava histórico bounded e podia emitir `kb2ops_post_approved` na transição para aprovado.

Gap comprovado: os retornos individuais de `update_post_meta()` não eram confirmados antes da emissão do evento. O novo domínio deverá obedecer `validar -> persistir -> reler/confirmar -> emitir evento`.

## Consumers históricos

O domínio histórico alimentava:

- Knowledge Studio/admin;
- checklist/pré-análise local;
- filtros/relatórios;
- Search scope;
- derivação histórica de `AI READY`.

A nova SPEC não herda automaticamente esses acoplamentos. Search e IA continuam consumidores futuros e não writers do estado de governança.

## Capabilities e trust boundaries históricos

- Studio/listagem: `edit_posts`;
- revisão individual: `edit_post(post_id)`;
- settings/migração: `manage_options`;
- handlers via `admin-post.php`, POST + nonce + capability.

Direção mantida: capability por objeto deve ser revalidada no handler da decisão.

## Relação com o editorial

`post_status` permanece estado editorial do WordPress. Review/Governança é domínio separado. O profiler deverá medir a coexistência entre review_state e `post_status`, inclusive `approved` em conteúdo não publicado e conteúdo publicado sem estado de governança.

## Política anterior ao profiling

- migração automática: **NÃO AUTORIZADA**;
- dual-write: **PROIBIDO**;
- `AI READY`: **FORA DE ESCOPO**;
- scores de qualidade: **FORA DE ESCOPO**;
- notas humanas: dado potencialmente sensível; profiler mede somente presença/tamanho, nunca exporta conteúdo;
- reviewer IDs: profiler mede apenas cardinalidade/validade agregada, sem exportar IDs.

## Gate

Este baseline fecha inventário de código/contratos históricos (T001–T005). O Gate R-001 depende ainda do profiling real (T006–T011).
