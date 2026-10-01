# P-670 — Master Ledger Disposition Plan

**Status:** PREPARED / NÃO APLICAR ANTES DE `PASS_PRECONDITIONS`  
**Data:** 2026-09-30

Este documento define a disposition possível para os IDs de foundation afetados pela SPEC-006. Ele não altera o Master Ledger por si só.

## Regra de aplicação

Pré-condição absoluta:

- `evidence/spec006-p670-preflight-current.json`;
- `status=PASS_PRECONDITIONS`;
- mesmo `plugin_tree_sha` do source atual;
- mesmo SHA-256 congelado do p650.4;
- Plugin Check estático oficial concluído + disposition explícita;
- rollback PASS com `data_preserved=true`.

Se qualquer condição falhar, nenhuma promoção abaixo pode ser aplicada.

## PROD-003 — Composer/WPCS/PHPUnit/static

Estado atual: `PARTIAL`.

Candidato após P670 preflight: `IMPROVED_VERIFIED`.

Evidência necessária:

- P640 local PASS com WPCS + PHPUnit;
- P660 local PASS com WPCS dos arquivos endurecidos;
- contratos estáticos P640/P650/P660/P670;
- tooling resolvido e versionado na evidência.

Observação:

- o full-plugin legacy WPCS debt não deve ser reclassificado como limpo;
- `IMPROVED_VERIFIED` significa que a foundation possui quality gates locais reproduzíveis para o runtime afetado, não que todo o legado está WPCS-clean;
- `composer.lock` não está versionado atualmente; o lock deve ser revisado quando gerado localmente, mas sua ausência não autoriza alegar dependências dev bit-for-bit reproduzíveis.

## PROD-004 — Plugin Check

Estado atual: `PARTIAL`.

Candidato após P670 preflight: `IMPROVED_VERIFIED`.

Evidência necessária:

- Plugin Check oficial;
- execução `LOCAL_ONLY`;
- target direto = p650.4;
- SHA exato do p650.4;
- runtime oficial preferido; quando indisponível por limitação ambiental comprovada, runtime nativo PASS + disposition explícita são obrigatórios;
- nenhum ignore global;
- PASS final ou disposition explícita e versionada compatível com o contrato P660.

## PROD-005 — modular production bootstrap

Estado atual: `PARTIAL`.

Candidato após P670 preflight: `IMPROVED_VERIFIED`.

Evidência necessária:

- P640 local PASS no mesmo `plugin_tree_sha`;
- contratos modular runtime PASS;
- environmental smoke;
- P650 package sem Engineering_Module_Loader;
- Search/Public Experience/Word Cloud funcionais.

## PROD-006 — ZIP sem laboratório indevido

Estado atual: `PARTIAL`.

Candidato após P670 preflight: `IMPROVED_VERIFIED`.

Evidência necessária:

- p650.4 deterministic package;
- single root;
- engineering/lab files = 0 conforme contrato de distribuição;
- repo-only paths = 0;
- checksum congelado;
- environmental smoke PASS;
- Plugin Check do mesmo ZIP;
- rollback PASS.

## IDs que NÃO devem ser promovidos por P670

P670 não promove por inferência:

- GRE-003;
- GRE-005;
- KB2-002;
- KB2-006;
- ASI-007..018;
- ASI-020..022;
- ENV-001..004.

Esses IDs permanecem sob suas SPECs próprias e continuam bloqueando os cutovers aplicáveis.

## Limites

Mesmo que PROD-003..006 sejam promovidos:

- não autoriza versão 1.0.0;
- não autoriza public cutover;
- não autoriza retirement de ASI/GRE/KB2Ops;
- não autoriza bulk migration;
- não autoriza deleção de storage legado.

A autorização final de cutover/retirement permanece na SPEC-014.
