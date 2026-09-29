# P-640 — Environmental Acceptance

**Status:** READY FOR HOMOLOGATION  
**Build:** `0.6.0-dev-p640.1`  
**Data:** 2026-09-29

## Artefato

- arquivo: `base-conhecimento-inteligencia-integrada-0.6.0-dev-p640.1.zip`;
- SHA-256: `e22e599ae8ae212c8e9c87a522e4f04a63fe187344798191c7f2fe8051f2ef9a`;
- package de homologação, não produção;
- build determinístico: PASS;
- P640-06 local/quality gate: PASS.

## Objetivo

Comprovar em WordPress real que a refatoração do composition root preserva:

- runtime core;
- Search lexical;
- Public Experience Preview;
- Word Cloud;
- Elementor adapter read-only;
- flags vigentes;
- ausência de mutação editorial/migração.

## Procedimento

1. instalar/substituir o plugin pelo ZIP `p640.1` no ambiente de homologação;
2. manter os plugins legados no estado atual — este gate não faz cutover;
3. acessar **Base de Conhecimento → P-640 Modular Runtime**;
4. clicar **Executar P-640 e baixar JSON**;
5. anexar o JSON gerado à continuidade da SPEC-006.

## Critério PASS

O JSON deve retornar:

- `gate = P-640`;
- `status = PASS`;
- `core_classes_loaded = true`;
- `known_product_modules_exact = true`;
- `enabled_product_modules_loaded = true`;
- Search/Public Preview/Word Cloud habilitados;
- `engineering_gate_enabled = true`;
- `legacy_elementor_reader_available = true`;
- `editorial_writer_unchanged = true`;
- `content_mutation = false`;
- `data_migration = false`;
- `cutover_authorized = false`;
- `retirement_authorized = false`.

## Após PASS

- versionar o JSON ambiental;
- fechar P640-07;
- produzir P640-08 package/runtime inventory;
- avaliar promoção de `PROD-005 modular production bootstrap` no Master Functional Parity Ledger;
- fechar P-640;
- avançar para P-650 Packaging/Install/Upgrade.

## Rollback

Reinstalar o pacote anterior da branch/SPEC-006. P-640 não altera schema, conteúdo editorial nem ownership de dados.
