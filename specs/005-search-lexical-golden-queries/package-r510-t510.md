# Package — SPEC-005 R-510/T510

Build: `0.5.0-r510-t510.1`  
SHA-256: `3b4b4e0ec308c5914ce155e740228ff4b0f735fd76fd1b30b930bce384ce77d3`

## Objetivo

Descobrir Golden Queries reais preservadas pelo ASI no banco de homologação, sem importar ou modificar qualquer dado.

Tabela legada procurada:

`{$wpdb->prefix}asi_golden_queries`

## Comportamento

- detecta a tabela pelo prefixo real do WordPress;
- valida colunas;
- lê apenas campos de expectativa Golden;
- não exporta created_by/updated_by;
- valida existência/post_type/status dos expected posts atuais;
- preserva item-level somente como evidência;
- lê metadata segura de `asi4_golden_last_run`;
- calcula candidate set hash;
- não executa Search/ranking;
- não persiste candidatos;
- fingerprint editorial before/after.

## Validação local

- 45 arquivos;
- 40 PHP;
- 40/40 lint pré-ZIP;
- 40/40 lint pós-ZIP;
- 39/39 active requires;
- deterministic rebuild PASS;
- T502 OFF/fora do ZIP;
- T510 ON/presente;
- forbidden write/network calls = 0;
- local source == Git blob.

## Execução ambiental

Instalar sobre o build T502 e acessar:

`Base de Conhecimento -> Golden Discovery R-510`

ou:

`/wp-admin/admin.php?page=bdc-kb-spec005-r510-legacy-golden`

Executar e baixar o JSON.

PASS de segurança esperado:

`gate_result.t510_read_only_safety_pass=true`.

`r510_ready=false` permanece esperado até revisão humana e congelamento da Golden Suite v1.
