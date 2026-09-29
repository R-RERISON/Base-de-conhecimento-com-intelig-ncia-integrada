# P-600 — Plugin Metadata & License Contract v1

**Status:** IMPLEMENTATION BASELINE
**Data:** 2026-09-29
**SPEC:** 006 Premium Product Foundation

## Objetivo

Transformar o cabeçalho/distribuição do BDC de artefato de laboratório em identidade de produto explícita, sem antecipar release 1.0.0.

## Contrato

O plugin deve declarar no bootstrap:
- Plugin Name;
- Plugin URI;
- Description;
- Version;
- Requires at least;
- Requires PHP;
- Author;
- License = GPL-2.0-or-later;
- License URI;
- Update URI único;
- Text Domain.

## Versão

A baseline de desenvolvimento da SPEC-006 é `0.6.0-dev`.

Regras:
- `1.0.0` permanece reservado ao CUT-1490 da SPEC-014;
- builds/gates não devem substituir a versão canônica do produto permanentemente;
- builders de homologação podem aplicar versões RC temporárias em staging;
- o bootstrap em source deve carregar a versão canônica corrente.

## Documentação mínima de distribuição

Obrigatórios no diretório do plugin:
- `readme.txt`;
- `LICENSE`;
- `CHANGELOG.md`;
- `UPGRADE.md`;
- `SECURITY.md`;
- `CONTRIBUTING.md`.

## Segurança

P-600 não altera:
- banco;
- Search Service;
- ranker;
- conteúdo editorial;
- hooks funcionais;
- schema.

## Aceite

P600 PASS quando:
1. metadata está presente e consistente;
2. versão header == `BDC_KB_VERSION`;
3. licença declarada como GPL-2.0-or-later;
4. Update URI não colide com WordPress.org;
5. documentos obrigatórios existem;
6. teste estático P-600 passa.
