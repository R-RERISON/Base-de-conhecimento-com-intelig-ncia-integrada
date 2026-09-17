# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Repositório / branch
- Repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`.
- Branch ativa: `spec004-g245-production-readiness`.
- PR #4: DRAFT / NÃO MERGEAR.
- SPEC ativa: SPEC-004.

## Estado atual
- UX-002 `0.4.0-ux002.3`: PASS/CLOSED, contrato visual obrigatório.
- G-240: PASS/CLOSED/main.
- ADR-004-001: Core Blocks como destino editorial canônico.
- T091/T093/T094/T096/T097: PASS AMBIENTAL.
- T095: PASS LOCAL / READ-ONLY.
- T098.1: FAIL CONTROLADO / SEM MUTAÇÃO.
- T098.2: **PASS AMBIENTAL**.
- T099A: **IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE**.
- Nenhum writer editorial autorizado.

## Evidência T098.2
Arquivo: `evidence/g245-t098-readiness-pass-20260917T191436Z.json`.
SHA-256 bruto: `f7bf589858db6c6629cb937ba28dad4ef658d1e265225185dad77e55dd342ff0`.

Resultado:
- 623/623 em duas passagens;
- errors/throwables/safety violations 0;
- dry/journal hash mismatches 0;
- ready 611, noop 7, review_required 5;
- batch planner: 611 elegíveis, 25 batches, cobertura 611, zero duplicidade, zero cursor failure;
- fingerprint editorial unchanged;
- `t098_block_migration_readiness_pass=true`.

## T099A — próximo gate exato
O smoke ambiental usa apenas postmeta privado temporário:

- `_bdc_kb_block_migration_journal`;
- `_bdc_kb_block_migration_lock`.

Fluxo:
1. selecionar automaticamente um `legacy_html`/`plain_text` com dry-run ready e sem resíduos de journal/lock;
2. capturar hashes editoriais before;
3. persistir journal preparado e validar readback;
4. adquirir lock exclusivo e validar readback;
5. liberar lock;
6. remover o journal criado pelo smoke;
7. validar cleanup completo;
8. confirmar hashes `post_content` e `_elementor_data` idênticos antes/depois.

Nenhum `wp_update_post`, nenhum writer `_elementor_data`, nenhum render/shortcode e nenhuma chamada externa.

## Depois do T099A
- T099B: Authorization Pack de um único `legacy_html` de baixo risco.
- T099C: canário real + rollback, somente com autorização específica.
- T100: batch controlado.
- T101: dependência residual Elementor / gate de retirada.
- G-250 Lifecycle/RC.

## Guardrails absolutos
- UX-002 intacta.
- plugin Gutenberg não é dependência.
- Elementor não é removido agora.
- mixed exige humano.
- nenhum write editorial sem gate + autorização específica.
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
