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
- T099A Journal Store + Lock Smoke: **PASS AMBIENTAL**.
- T099B Authorization Pack: **IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE / READ-ONLY**.
- T099C canário real: BLOCKED por autorização específica.
- G-250: NOT_RUN.

## Baseline visual
UX-002 `0.4.0-ux002.3` permanece contrato visual obrigatório.

## T098.2 — PASS AMBIENTAL
Evidência: `evidence/g245-t098-readiness-pass-20260917T191436Z.json`.
SHA-256 bruto: `f7bf589858db6c6629cb937ba28dad4ef658d1e265225185dad77e55dd342ff0`.

Resultado: 623/623 em duas passagens; errors/throwables/safety violations 0; ready 611, noop 7, review_required 5; 25 batches cobrem 611 elegíveis sem duplicidade/cursor failure; fingerprint editorial unchanged; gate PASS.

## T099A — PASS AMBIENTAL
Evidência: `evidence/g245-t099a-storage-lock-20260917T193102Z.json`.
SHA-256 bruto: `671e6f26de0d232569a7728651e7d42348677607b7ac5bcfc194012031d21ba8`.

Target ambiental: post 358, `legacy_html`, dry-run `ready`.

Resultado:
- journal 0 → persistido/readback PASS → cleanup PASS → 0;
- lock ausente → adquirido/readback PASS → cleanup PASS → ausente;
- `post_content` SHA-256 before/after igual;
- `_elementor_data` SHA-256 before/after igual;
- errors 0;
- nenhum write editorial;
- `t099a_storage_lock_pass=true`.

## T099B — Authorization Pack
Contrato: `t099b-authorization-pack-contract-v1.md`.
Runtime: `includes/class-block-migration-authorization-pack-smoke.php`.

Seleção conservadora:
- `legacy_html` + dry-run `ready`;
- zero journal/lock residual;
- `_elementor_data` vazio;
- 1–30.000 bytes;
- zero shortcode registrado;
- zero script/iframe/form/object/embed/style;
- zero comentário Core Block;
- até 10 links, 1 imagem e 1 tabela;
- post 358 é preferido apenas se continuar low-risk; caso contrário, menor `risk_score` determinístico.

O pack exporta hashes, block names esperados, risco agregado, identificação humana do artigo e `authorization_id`. Não exporta corpo editorial/URLs e não persiste nada.

## Próximos subgates

- [ ] executar T099B e versionar Authorization Pack.
- [ ] solicitar autorização explícita para `post_id + authorization_id` específicos.
- [ ] T099C: aplicar canário de 1 artigo, verificar e fazer rollback imediato.
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
