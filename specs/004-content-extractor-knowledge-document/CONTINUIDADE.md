# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Repositório / branch

- Repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`.
- Branch ativa: `spec004-g245-production-readiness`.
- PR #4: DRAFT / NÃO MERGEAR.
- SPEC ativa: SPEC-004.

## Estado atual

- UX-002 `0.4.0-ux002.3`: PASS/CLOSED, contrato visual obrigatório.
- G-240: PASS/CLOSED/main.
- KD 2.1.0: PASS técnico + humano 8/8.
- ADR-004-001: ACEITA — Core Blocks como destino editorial canônico.
- T091/T093/T094/T096/T097: PASS AMBIENTAL.
- T095: PASS LOCAL / READ-ONLY.
- T098.1 Block Migration Readiness: **FAIL CONTROLADO / SEM MUTAÇÃO**.
- T098.2 Block Migration Readiness: **CORRIGIDO / REHOMOLOGAÇÃO PENDENTE**.
- Nenhum writer/migration está autorizado.

## T097 — PASS AMBIENTAL

Evidência: `evidence/g245-editorial-parity-t097-20260917T184418Z.json`.
SHA-256 bruto: `6466fb0830d9ba65e79da9dc69b90da6a08fb4286391517eb0663849b10babec`.

Resultado: 623/623 em duas passagens; errors/throwables/safety violations/parity mismatches/stale sources/manifest mismatches = 0; parity pass 611; native_noop 4; not_applicable 3; review_required 5; stale fresh 623; fingerprint editorial idêntico; `t097_static_editorial_parity_pass=true`.

## T098.1 — FAIL CONTROLADO

Evidência: `evidence/g245-t098-readiness-fail-20260917T190620Z.json`.
SHA-256 bruto: `b85e22c338d72f52dc11b3d113058618108b9dd20778b67a3062839c8eaba44d`.

Resultado ambiental:

- corpus 623;
- first/second pass = 0;
- first/second throwables = 623;
- safety violations = 0;
- corpus unchanged;
- fingerprint editorial unchanged;
- gate false.

Causa raiz: mismatch de integração entre `Block_Migration_Dry_Run` e contratos T097 já homologados:

- `Core_Block_Editorial_Parity::validate()` não existe; usar `assess(source, serialization, parsed_blocks)`;
- `Block_Migration_Stale_Source_Guard::inspect_post()` não existe; usar `assess(planned, current)`;
- `is_fresh` não faz parte do contrato; freshness é `status=fresh`.

Não houve mutação editorial nem falha arquitetural.

## T098.2 — correção

Pipeline corrigido:

`Migration_Fidelity_Source::build`
→ `Core_Block_Lossless_Serializer::serialize_source`
→ `parse_blocks`
→ `Core_Block_Editorial_Parity::assess`
→ rebuild `Migration_Fidelity_Source`
→ `Block_Migration_Stale_Source_Guard::assess`
→ `Block_Migration_Dry_Run::simulate`.

O runner também passa a exportar apenas assinatura agregada de throwable (`classe + basename:linha + hash curto da mensagem`) caso ocorra nova incompatibilidade, sem conteúdo editorial/URL/post ID.

Validação local:

- 28/28 assertions PASS;
- 50 PHP lint PASS pré/pós ZIP;
- UX-002 byte parity PASS;
- journal store não invocado;
- lock não adquirido;
- writer não invocado;
- `post_content`/`_elementor_data` não escritos.

Pacote:

- `0.4.0-g245-readiness-t098.2`;
- SHA-256 `a37ebc3ff018f71c96546e56e2b8434db53ea148b7d9d253db0332b1d389cef7`.

## Próximo passo

1. instalar T098.2;
2. abrir `Base de Conhecimento > Block Migration Readiness G-245`;
3. executar e devolver JSON;
4. se PASS, fechar T098;
5. T099A testa journal store + lock com cleanup, sem write editorial;
6. T099B gera Authorization Pack de um `legacy_html` de baixo risco;
7. T099C canário real só com autorização específica e rollback.

PASS esperado: `gate_result.t098_block_migration_readiness_pass=true`.

## Guardrails

- não mexer na UX-002 sem UX-SPEC;
- plugin Gutenberg não é requisito;
- Elementor não é removido agora;
- source mixed continua humano;
- nenhum write em `post_content` ou `_elementor_data` está autorizado;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
