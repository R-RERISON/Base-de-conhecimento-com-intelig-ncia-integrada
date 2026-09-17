# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Repositório / branch

- Repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`.
- Branch ativa: `spec004-g245-production-readiness`.
- PR #4: DRAFT / NÃO MERGEAR.
- SPEC ativa: SPEC-004.

Antes de qualquer alteração, reler `AGENTS.md`, `.specify/PROJECT_MANIFEST.md`, `.specify/memory/constitution.md`, SPEC-004, ADRs vigentes e `docs/DEFINITION-OF-DONE.md`.

## Estado atual

- UX-002 `0.4.0-ux002.3`: PASS/CLOSED, contrato visual obrigatório.
- G-240: PASS/CLOSED/main.
- KD 2.1.0: PASS técnico + humano 8/8.
- ADR-004-001: ACEITA — Core Blocks como destino editorial canônico.
- T083B Durable Journal Storage: PASS AMBIENTAL.
- T091: PASS AMBIENTAL.
- T092: PASS LOCAL.
- T093: PASS AMBIENTAL.
- T094 Editorial Fidelity: PASS AMBIENTAL.
- T095 Migration Fidelity Source v1: PASS LOCAL / READ-ONLY.
- T096 Lossless Core Block Serialization + round-trip: **PASS AMBIENTAL**.
- T097 Static Editorial Parity + generic stale-source: **PASS LOCAL / HOMOLOGAÇÃO PENDENTE**, 20/20 local.
- Nenhum writer/migration está autorizado.

## Decisão arquitetural vigente

`WP_Post.post_content` + WordPress Core Blocks são o destino editorial canônico futuro.

- plugin Gutenberg NÃO é dependência de produção;
- somente APIs estáveis do WordPress Core homologado;
- Elementor é fonte legada temporária/read-only;
- `_elementor_data` deve ser preservado durante a transição;
- nenhum writer futuro usa `_elementor_data` como destino.

Constituição da branch: v1.3.0.

## T096 comprovado

Evidência resumida:
`evidence/g245-lossless-t096-summary-20260917T182930Z.json`.

SHA-256 do JSON bruto recebido:
`7c71a436db3393fbf9be8f0add11fd32590d3a9804452e57173090e425925192`.

Ambiente:

- WordPress 6.9.4;
- PHP 8.5.10;
- Migration Fidelity Source 1.0.0;
- Lossless Serializer 1.0.0;
- Gutenberg plugin dependency false.

Resultado:

- corpus 623;
- duas passagens 623/623;
- errors 0;
- throwables 0;
- safety violations 0;
- raw round-trip mismatches 0;
- parse/serialize mismatches 0;
- fidelity hash mismatches 0;
- serialization hash mismatches 0;
- source ready 615;
- review_required 5;
- not_applicable 3;
- serialized_in_memory 611;
- native_noop 4;
- `core/freeform` 611;
- `core/shortcode` 1;
- 5 `mixed` permanecem review_required;
- corpus unchanged;
- fingerprint editorial before/after igual;
- `gate_result.t096_lossless_roundtrip_pass=true`.

## Arquitetura de duas projeções

### Conhecimento

`fonte -> Content Extractor -> Knowledge Document`

Uso: busca, IA, hierarquia, qualidade e guardrail semântico.

### Migração lossless

`fonte -> Migration Fidelity Source -> Core Block Lossless Serializer`

Uso: preservar material editorial e migrar para Core Blocks sem perda.

Mapeamento inicial:

- Gutenberg -> `native_noop`;
- legacy HTML/plain text -> `core/freeform`;
- Elementor text-editor -> `core/freeform`;
- Elementor shortcode -> `core/shortcode`;
- mixed/unsupported -> fail-closed.

## T097 — Static Editorial Parity

Contrato:
`core-block-editorial-parity-contract-v1.md`.

Runtime:

- `includes/class-block-migration-stale-source-guard.php`;
- `includes/class-core-block-editorial-parity.php`;
- `includes/class-core-block-editorial-parity-smoke.php`.

Validação local: **20/20 assertions PASS + PHP lint PASS**.

O smoke executa duas passagens full-corpus e valida:

1. `core/freeform` e `core/shortcode` registrados no Block Registry;
2. block name parseado igual ao mapping esperado;
3. SHA-256 do `innerHTML` parseado igual ao raw source unit;
4. Gutenberg nativo byte-equivalent;
5. stale-source guard comparando `fidelity_hash`, `source_kind`, `post_content_sha256` e `elementor_data_sha256`;
6. manifest determinístico entre passagens;
7. corpus/fingerprint unchanged;
8. zero render de blocks/shortcodes, zero write/network e zero export de conteúdo/URLs/post IDs.

## Pacote T097

- `0.4.0-g245-parity-t097.1`;
- SHA-256 `79d82b6e8f7929247a4121c9ccbc2f94972902eab3039b12770b0714f99f11e0`;
- 44 PHP files lint PASS pré/pós ZIP;
- UX-002 byte parity PASS;
- T096 smoke OFF;
- T097 smoke ON;
- writer/migration OFF.

## Próximo passo exato

1. instalar pacote T097 em homologação;
2. abrir `Base de Conhecimento > Editorial Parity G-245`;
3. executar `Executar T097 e baixar JSON`;
4. devolver o JSON;
5. versionar evidência;
6. se PASS, iniciar T098 para generalizar journal/dry-run/lock/batch para Block Migration;
7. depois montar Authorization Pack para exatamente 1 canário mutável.

Gate esperado:

`gate_result.t097_static_editorial_parity_pass=true`.

## Guardrails absolutos

- não mexer na UX-002 sem UX-SPEC;
- não instalar/declarar plugin Gutenberg como requisito;
- não remover Elementor agora;
- não escrever `_elementor_data`;
- não escrever `post_content`;
- não usar KD como fonte editorial lossless;
- não exportar raw payload em runners;
- não decidir mixed source automaticamente;
- não executar shortcodes nos gates read-only;
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
