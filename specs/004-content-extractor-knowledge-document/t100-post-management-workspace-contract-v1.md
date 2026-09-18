# T100 — Post Management Workspace Contract v1

**Status:** FROZEN — T100A LOCAL PASS / HOMOLOGAÇÃO PENDENTE  
**ADR:** ADR-004-002  
**Contrato:** 1.0.0

## Objetivo

Transformar o Knowledge Workspace já existente na superfície canônica de gerenciamento por artigo, sem criar telas paralelas e sem habilitar novos writes no T100A.

## Rota canônica

`Base de Conhecimento → lista → Gerenciar → post_id`

O parâmetro `post_id` é obrigatório para qualquer atividade post-scoped. A navegação de atividade preserva o mesmo `post_id`.

## Activity Registry v1

| Activity | Modo T100A |
|---|---|
| overview | read-only |
| content | read-only |
| summary | writer existente, sem mudança contratual |
| classification | writer existente, sem mudança contratual |
| intelligence | read-only |
| core_blocks | read-only |
| review | writer existente, sem mudança contratual |
| history | read-only |

Nenhuma atividade nova possui `wp_update_post`, `update_post_meta`, `add_post_meta` ou rede externa.

## Post Context Contract v1

O contexto agrega somente metadados técnicos/operacionais seguros do artigo:

- identidade e status do post;
- source kind e estratégia de fidelidade;
- contagem/bytes de unidades;
- hashes de origem;
- disponibilidade de Summary;
- contagem de conceitos de Classificação;
- estado de Review;
- Core Blocks dry-run;
- contagem/último estado de journal;
- status de lock;
- flags de segurança.

O contexto não exporta corpo editorial nem URLs e não persiste estado.

## T100A — escopo

T100A adiciona:

- `Post_Activity_Registry`;
- `Post_Management_Context`;
- `Post_Management_Activities`;
- abas **Conteúdo**, **Inteligência** e **Core Blocks**;
- source label técnico no painel lateral;
- cards correspondentes na Visão Geral.

A aba Inteligência apenas registra a camada e informa que execução IA está desabilitada neste gate.

A aba Core Blocks mostra readiness, journal e lock, mas não contém ação de migração.

## Anti-regressão obrigatória

1. botão **Gerenciar** preservado;
2. Summary preservado;
3. Classificação preservada;
4. Review preservado;
5. Histórico preservado;
6. rota por `post_id` preservada;
7. tabs existentes preservadas;
8. novas tabs não escrevem;
9. batch authorization build desabilitada;
10. T099C canary build desabilitada;
11. sem rede externa;
12. lint full-plugin PASS;
13. visual foundation, classification admin e visual foundation class byte-idênticos à UX-002.3;
14. qualquer delta em `class-admin-page.php` deve ser deliberado e limitado à Workspace.

## Aceite ambiental T100A

Revisar no WordPress de homologação:

- lista renderiza;
- **Gerenciar** abre o artigo correto;
- título/ID/status permanecem corretos;
- Summary/Classificação/Review/Histórico continuam operacionais;
- Conteúdo/Inteligência/Core Blocks permanecem no mesmo post;
- post 358 exibe source legado, journal `rolled_back` e lock livre;
- nenhuma nova atividade oferece write;
- nenhuma regressão visual material.

## Rollback

Rollback do T100A é o downgrade para o último build homologado anterior. Como as novas atividades são read-only, T100A não cria dados persistentes.
