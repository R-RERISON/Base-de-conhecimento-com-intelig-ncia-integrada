# P-610 — Tooling / WPCS / PHPUnit / Static Analysis v1

**Status:** IMPLEMENTATION BASELINE
**Data:** 2026-09-29

## Objetivo

Introduzir um toolchain reproduzível de desenvolvimento sem confundir adoção da ferramenta com limpeza instantânea de todo o legado.

## Baseline

- PHP mínimo do produto: 8.1;
- PHPUnit: linha 10.5, compatível com PHP 8.1;
- WPCS: 3.4.x;
- PHPCS Composer Installer: 1.2.x;
- PHPStan: 2.2.x;
- phpstan-wordpress: 2.0.x.

## Artefatos

- `composer.json`;
- `phpunit.xml.dist`;
- `phpcs.xml.dist`;
- `phpstan.neon.dist`;
- suíte PHPUnit inicial em `tests/phpunit`.

## Estratégia de adoção

O primeiro gate é bounded:
- PHPUnit valida contratos de produto;
- WPCS é obrigatório para novos harnesses/arquivos da foundation;
- PHPStan inicia em level 5 sobre a nova suíte;
- o legado completo será auditado separadamente e sua dívida não será mascarada por exclusions silenciosos.

P-610 não declara o plugin inteiro WPCS/PHPStan clean.

## Aceite

P610 PASS requer:
1. `composer validate`;
2. instalação determinística de dev dependencies;
3. PHPUnit PASS;
4. bounded WPCS PASS;
5. bounded PHPStan PASS;
6. inventário separado do débito do plugin completo;
7. nenhuma dependência `vendor/` incluída no ZIP de produção por inferência.
