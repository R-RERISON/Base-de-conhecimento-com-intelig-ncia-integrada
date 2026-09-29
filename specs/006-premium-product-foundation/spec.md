# SPEC-006 — Premium Product Foundation, Domain Consolidation & Distribution

**Status:** PLANEJADA — PRIMEIRA SPEC APÓS SEARCH BASELINE  
**Pré-requisito:** fronteira da SPEC-005 formalmente fechada.

## Problema

O BDC já possui domínio e engenharia avançados, mas ainda não é um plugin premium distribuível completo e mantém gaps canônicos herdados de GRE/KB2Ops.

## Resultado esperado

### Product engineering
- header completo;
- GPL-2.0-or-later;
- Plugin URI/Update URI;
- LICENSE/CHANGELOG/UPGRADE/SECURITY/CONTRIBUTING;
- readme.txt;
- Composer;
- WPCS;
- PHPUnit;
- análise estática;
- Plugin Check;
- política de versão/release;
- matriz de compatibilidade.

### Architecture
- composition root modular;
- carregamento condicional;
- redução de bootstrap monolítico;
- revisão de classes grandes;
- runners de engenharia fora do ZIP production por default;
- module registry explícito.

### Domain consolidation
- owner para affected_service;
- owner para systems_involved;
- disposition de KB2Ops service/technologies/keywords/versions;
- Helpful Tips com read/write canônico;
- Coverage read model BDC;
- nenhum dual-write permanente.

## Fora

Telemetria, vetor, IA, semantic search e redesign público completo.

## Gates

- P-600 Plugin Metadata/License;
- P-610 Tooling/WPCS/PHPUnit;
- P-620 Plugin Check;
- P-630 Domain Closure;
- P-640 Modular Runtime;
- P-650 Packaging/Install/Upgrade;
- P-660 Security/Privacy baseline;
- P-670 Premium Foundation Acceptance.

## Done

O mesmo ZIP instala, atualiza, passa quality gates e Plugin Check ou waivers explícitos, possui documentação profissional, preserva dados, exclui laboratório indevido e atualiza Master Parity Ledger.
