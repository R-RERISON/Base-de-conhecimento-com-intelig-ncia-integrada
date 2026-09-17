# T100C — Core Blocks Post Activity Contract v1

**Status:** IMPLEMENTADO / PASS LOCAL / HOMOLOGAÇÃO PENDENTE  
**ADR:** ADR-004-002  
**Contrato:** 1.0.0

## Objetivo

Transformar a aba **Core Blocks** da Post Management Workspace em uma superfície operacional post-scoped, mantendo o gate T100C estritamente sem write.

A atividade deve consolidar no contexto do artigo:
- fonte detectada;
- dry-run;
- estado operacional;
- blocos esperados;
- journal;
- lock;
- authorization_id determinístico;
- download de Authorization Pack individual.

Nenhum submenu técnico adicional é criado.

## Action Contract

Identificador estável:

`core_blocks_migrate_v1`

O `authorization_id` é calculado sobre:

- action_contract;
- post_id;
- fidelity_hash_before;
- serialization_hash;
- dry_run_hash;
- serialized_post_content_sha256.

Qualquer drift em qualquer uma dessas identidades produz outro authorization_id.

## Estados operacionais

- `ready_for_authorization`: dry-run ready, lock free e sem journal não-terminal.
- `human_review_required`: source mixed ou dry-run review_required.
- `no_action_required`: native/noop.
- `blocked`: qualquer outra condição, lock ativo ou journal não-terminal.

Um histórico terminal `rolled_back` é permitido e informado como observação, não como bloqueio automático.

## Authorization Pack

O botão **Baixar Authorization Pack deste post** aparece somente quando `authorization_ready=true`.

O pack:
- é read-only;
- não persiste estado;
- não adquire lock;
- não escreve `post_content`;
- não escreve `_elementor_data`;
- não executa shortcode;
- não renderiza blocks;
- não usa rede externa;
- não exporta corpo editorial nem URLs;
- retorna `authorized=false` e exige autorização humana explícita.

## UX

A aba Core Blocks permanece no mesmo `post_id`.

Ela mostra:
- estado técnico;
- reasons;
- authorization_id;
- botão de download do pack;
- botão **Migrar para Core Blocks — bloqueado neste gate** desabilitado.

O botão desabilitado apenas fixa o local da futura ação e não contém handler de write.

## Anti-regressão

- Summary/Classificação/Review/Histórico permanecem inalterados.
- T099C continua OFF.
- antigo T100 batch continua OFF.
- Elementor writer continua OFF.
- Activity Registry não muda o `post_id`.
- nenhuma nova tela global é criada.
- nenhuma função T100C chama `wp_update_post`, `update_post_meta`, `add_post_meta` ou `wp_remote_*`.

## Próximo gate

**T100D — Single Post Core Blocks Executor**, somente após:
1. T100C PASS ambiental;
2. Authorization Pack individual gerado para um post específico;
3. autorização explícita vinculada a `post_id + authorization_id`.

O T100D não herdará nenhuma autorização anterior.
