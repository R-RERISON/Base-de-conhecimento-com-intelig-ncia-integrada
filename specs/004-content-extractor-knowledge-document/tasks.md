# Tarefas — SPEC-004 Content Extractor e Knowledge Document

## Estado resumido

- R-200: PASS.
- R-210: PASS.
- G-220: PASS.
- G-230/v1: PASS de determinismo; v1 superseded for AI.
- G-240: **PASS / CLOSED / PROMOVIDO PARA `main`** com KD 2.1.0.
- G-245: **REBASELINED / IN PROGRESS** em `spec004-g245-production-readiness`; PR #4 DRAFT / NÃO MERGEAR.
- ADR-004-001: **ACEITA** — WordPress Core Blocks como destino editorial canônico.
- T083B Durable Journal Storage: **PASS AMBIENTAL**.
- T087C writer Elementor: **CANCELADO / SUPERSEDED antes de implementação**.
- T090 Block Projection v1.0: PASS LOCAL / READ-ONLY.
- T091 Block Projection full-corpus: PASS AMBIENTAL.
- T092 Block Projection v1.1: PASS LOCAL / READ-ONLY.
- T093 Block Projection v1.1 full-corpus: PASS AMBIENTAL.
- T094 Editorial Fidelity Inventory: **PASS AMBIENTAL**.
- T095 Migration Fidelity Source v1: **PASS LOCAL / READ-ONLY**.
- T096 Lossless Core Block Serialization + round-trip: **PASS AMBIENTAL**.
- T097 Static Editorial Parity + generic stale-source: **PASS LOCAL / HOMOLOGAÇÃO PENDENTE**, 20/20 assertions + lint.
- G-250: NOT_RUN.

## Baseline visual obrigatória

UX-002 `0.4.0-ux002.3` permanece contrato visual obrigatório. G-245 não altera os arquivos visuais canônicos sem UX-SPEC/aceite humano.

## Arquitetura editorial vigente

- `WP_Post.post_content` + WordPress Core Blocks = destino editorial futuro;
- plugin Gutenberg = não dependência;
- somente APIs estáveis do WordPress Core homologado;
- Elementor = source adapter legado temporário;
- nenhum writer `_elementor_data` como destino;
- remoção do Elementor somente após dependência zero comprovada.

## Baseline ambiental atual

Corpus: **623 posts**.

Source kinds:

- legacy_html 536;
- plain_text 41;
- elementor 34;
- mixed 5;
- gutenberg 4;
- empty 3.

## T094 — Editorial Fidelity — PASS AMBIENTAL

O inventário comprovou que o KD 2.1 é modelo semântico, não representação editorial lossless.

Material observado:

- 6.874 links;
- 4.595 imagens;
- 25.764 ocorrências de inline formatting;
- 1.394 styled spans;
- 1.319 line breaks;
- 513 tabelas;
- 53 posts com shortcodes;
- 80 posts com meta Elementor.

Todas as 4.595 URLs de imagem do `post_content` ficaram sem attachment ID resolvido; não relinkar mídia por inferência.

## T095/T096 — canonicalização lossless

Duas projeções complementares:

1. `fonte -> Content Extractor -> Knowledge Document` para busca/IA/hierarquia/guardrail;
2. `fonte -> Migration Fidelity Source -> Lossless Core Block Serializer` para migração fiel.

Primeira canonicalização:

- Gutenberg existente -> `native_noop`;
- legacy HTML/plain text -> `core/freeform` preservando payload exato;
- Elementor `text-editor` -> `core/freeform` preservando `settings.editor`;
- Elementor `shortcode` -> `core/shortcode` preservando texto exato;
- mixed/unsupported -> fail-closed / review.

Refinamento posterior de `core/freeform` para blocos semânticos fica fora da primeira migração.

## T096 — PASS AMBIENTAL

Evidência resumida:
`evidence/g245-lossless-t096-summary-20260917T182930Z.json`.

SHA-256 do JSON bruto recebido:
`7c71a436db3393fbf9be8f0add11fd32590d3a9804452e57173090e425925192`.

Resultado:

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
- 5 mixed sources permanecem review_required;
- corpus unchanged;
- fingerprint editorial igual;
- `gate_result.t096_lossless_roundtrip_pass=true`.

## T097 — Static Editorial Parity + stale-source

Contrato:
`core-block-editorial-parity-contract-v1.md`.

Runtime:

- `includes/class-block-migration-stale-source-guard.php`;
- `includes/class-core-block-editorial-parity.php`;
- `includes/class-core-block-editorial-parity-smoke.php`.

Validação local: **20/20 assertions PASS + PHP lint PASS**.

T097 valida, sem renderizar:

- `core/freeform` e `core/shortcode` registrados no Block Registry do Core;
- block name esperado por unidade editorial;
- SHA-256 do `innerHTML` parseado igual ao raw original;
- Gutenberg `native_noop` byte-equivalent;
- stale-source por `fidelity_hash`, `source_kind`, `post_content_sha256` e `elementor_data_sha256`;
- duas passagens determinísticas;
- fingerprint editorial unchanged;
- zero export de conteúdo/URLs/post IDs.

Pacote de homologação:

- `0.4.0-g245-parity-t097.1`;
- SHA-256 `79d82b6e8f7929247a4121c9ccbc2f94972902eab3039b12770b0714f99f11e0`;
- 44 PHP files lint PASS antes/depois do ZIP;
- UX-002 byte parity PASS;
- T096 smoke OFF;
- T097 smoke ON;
- writer/migration OFF.

## Próximos subgates

- [ ] executar T097 em homologação e versionar a evidência.
- [ ] T098: generalizar journal/dry-run/lock/batch para Block Migration, removendo acoplamento nominal Elementor sem mudar comportamento defensivo.
- [ ] T099: montar Authorization Pack de um único canário e executar write + rollback real somente com autorização específica.
- [ ] T100: batches homologados.
- [ ] T101: inventário de dependência residual Elementor e gate de retirada futura.
- [ ] G-250 Lifecycle/RC.

## Regras constitucionais

1. WordPress Core Blocks são o destino canônico futuro.
2. Plugin Gutenberg não é dependência de produção.
3. Elementor permanece até dependência zero; nunca é removido automaticamente.
4. Nenhum writer `_elementor_data` será implementado como destino.
5. Qualquer write em `post_content` exige gates e autorização explícitos.
6. KD não pode ser tratado como representação editorial lossless.
7. Payload editorial lossless não pode ser exportado por runners.
8. Mixed source não pode ser decidido automaticamente.
9. Shortcode não pode ser executado por gates de migração.
10. UX-002 não pode regredir.
11. Trabalho incompleto permanece fora da `main`.
