# Tarefas — SPEC-004 Content Extractor e Knowledge Document

## Estado resumido

- R-200: PASS.
- R-210: PASS.
- G-220: PASS.
- G-230/v1: PASS de determinismo; v1 superseded for AI.
- G-240: PASS/CLOSED/main com KD 2.1.0.
- G-245: REBASELINED / IN PROGRESS; PR #4 DRAFT / NÃO MERGEAR.
- ADR-004-001: ACEITA — WordPress Core Blocks como destino editorial canônico.
- T083B Durable Journal Storage: PASS AMBIENTAL.
- T087C writer Elementor: CANCELADO / SUPERSEDED antes de implementação.
- T090: PASS LOCAL / READ-ONLY.
- T091: PASS AMBIENTAL.
- T092: PASS LOCAL / READ-ONLY.
- T093: PASS AMBIENTAL.
- T094: PASS AMBIENTAL.
- T095 Migration Fidelity Source v1: PASS LOCAL / READ-ONLY.
- T096 Lossless Core Block Serialization: PASS AMBIENTAL.
- T097 Static Editorial Parity + stale-source: PASS AMBIENTAL.
- T098.1 Block Migration Readiness: FAIL CONTROLADO / SEM MUTAÇÃO.
- T098.2 Block Migration Readiness: PASS AMBIENTAL.
- T099A Journal Store + Lock Smoke: PASS AMBIENTAL.
- T099B Authorization Pack: **PASS AMBIENTAL / READ-ONLY**.
- T099C canário real: **BLOCKED até autorização humana exata**.
- G-250: NOT_RUN.

## Baseline visual
UX-002 `0.4.0-ux002.3` permanece contrato visual obrigatório.

## T099B — PASS AMBIENTAL
Evidência: `evidence/g245-t099b-authorization-pack-20260917T193757Z.json`.
SHA-256 bruto: `04d47e7839d67b34af6abf8b4ace87238f934f7ca751bc222e427206d813d8ce`.

Candidato congelado:
- post_id: 358;
- título: `LIA | Laboratório de Inteligência Analítica`;
- status: publish;
- source_kind: legacy_html;
- risk_tier: low;
- bytes: 1764;
- links: 2;
- images: 0;
- tables: 1;
- registered shortcodes: 0;
- dangerous embeds/scripts: 0;
- Core block comments: 0;
- `_elementor_data` bytes: 0.

Identidade do canário:
- fidelity_hash_before: `5e4695f159494ad3a1715741bdf485da43d77a991f9f39ecdab02901d6b2bd2e`;
- serialization_hash: `9e96a95d451c9463fa6bf37d7eec31c005df39da1774bd8de7ae110ee72f91cf`;
- dry_run_hash: `b533eb953b705b85fd48c3ab26c9bc5d6b61ee4223449370662f6e606123fab3`;
- post_content_sha256_before: `eb7f1c9c4e5426c9c5c473ade6c6b02312f526cad9a375b3bbe30ef0221479d0`;
- serialized_post_content_sha256_expected: `af4101dda487e6f6239b8bb0546439a75e1691062fd27f9322cbb0d9c2f84050`;
- expected block: `core/freeform`;
- authorization_id: `1557c1ee50e1a7a46df7d7952032cb1dd0cb374c7222de8656bb9c055f561bc9`.

## T099C — escopo exato autorizado somente após aprovação
Escopo permitido após aprovação explícita:
1. post 358 somente;
2. revalidar `authorization_id` e hashes imediatamente antes do write;
3. persistir journal durável antes do write;
4. adquirir lock exclusivo;
5. reexecutar stale-source guard;
6. escrever somente `WP_Post.post_content` com a serialização Core Block determinística;
7. preservar `_elementor_data` inalterado;
8. verificar hash/paridade após write;
9. executar rollback imediato obrigatório para o snapshot anterior;
10. verificar restauração exata;
11. liberar lock e manter journal de auditoria conforme contrato.

Até a autorização específica, `authorized=false` e nenhum write editorial é permitido.

## Próximos subgates

- [x] executar T099B e versionar Authorization Pack.
- [ ] obter autorização explícita para `post_id=358` + `authorization_id=1557c1ee50e1a7a46df7d7952032cb1dd0cb374c7222de8656bb9c055f561bc9`.
- [ ] T099C: aplicar canário, verificar e fazer rollback imediato.
- [ ] T100: batches homologados.
- [ ] T101: dependência residual Elementor / gate de retirada.
- [ ] G-250 Lifecycle/RC.

## Regras
1. Core Blocks são o destino canônico.
2. Plugin Gutenberg não é requisito.
3. Elementor permanece até dependência zero.
4. Nenhum writer `_elementor_data` será implementado como destino.
5. Write em `post_content` exige gates e autorização explícitos.
6. KD não é representação editorial lossless.
7. Raw payload não sai em runners de corpus.
8. Mixed não é decidido automaticamente.
9. UX-002 não pode regredir.
10. Trabalho incompleto permanece fora de `main`.
