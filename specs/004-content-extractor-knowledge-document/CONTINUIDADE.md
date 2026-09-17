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
- T098 Block Migration Protection: **PASS LOCAL / HOMOLOGAÇÃO PENDENTE**, 24/24 assertions.
- Nenhum writer/migration está autorizado.

## T097 — PASS AMBIENTAL

Evidência: `evidence/g245-editorial-parity-t097-20260917T184418Z.json`.

SHA-256 bruto: `6466fb0830d9ba65e79da9dc69b90da6a08fb4286391517eb0663849b10babec`.

Resultado: 623/623 em duas passagens; errors/throwables/safety violations/parity mismatches/stale sources/manifest mismatches = 0; parity pass 611; native_noop 4; not_applicable 3; review_required 5; stale fresh 623; fingerprint editorial idêntico; `t097_static_editorial_parity_pass=true`.

## T098 — Block Migration Protection

Contrato: `block-migration-protection-contract-v1.md`.

Primitivas:

- `Block_Migration_Journal`;
- `Block_Migration_Journal_Store`;
- `Block_Migration_Dry_Run`;
- `Block_Migration_Batch_Plan`;
- `Block_Migration_Lock`;
- `Block_Migration_Readiness_Smoke`.

Validação local: 24/24 PASS + lint.

O smoke T098 é full-corpus e read-only: prepara journal somente em memória, valida stale-source e batches, não persiste journal, não adquire lock e não chama writer.

Pacote:

- `0.4.0-g245-readiness-t098.1`;
- SHA-256 `df08c57d9c7b7c6df8a66b026ebec1bd6c3d16e993c92ea65afac122e6d63ddf`;
- 50 PHP lint PASS pré/pós ZIP;
- UX-002 byte parity PASS;
- writer OFF.

## Próximo passo

1. instalar T098;
2. `Base de Conhecimento > Block Migration Readiness G-245`;
3. executar T098 e devolver JSON;
4. se PASS, T099A testa apenas journal store + lock com cleanup, sem write editorial;
5. T099B gera Authorization Pack de um único `legacy_html` de baixo risco;
6. T099C canário real só com autorização específica e rollback.

PASS esperado: `gate_result.t098_block_migration_readiness_pass=true`.

## Guardrails

- não mexer na UX-002 sem UX-SPEC;
- plugin Gutenberg não é requisito;
- Elementor não é removido agora;
- source mixed continua humano;
- nenhum write em `post_content` ou `_elementor_data` está autorizado;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
