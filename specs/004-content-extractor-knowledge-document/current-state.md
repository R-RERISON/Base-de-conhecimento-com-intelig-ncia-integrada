# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline e gates
- SPEC-001/002/003: concluídas.
- UX-001/UX-002: concluídas; UX-002 `0.4.0-ux002.3` é contrato visual obrigatório.
- G-240: PASS / CLOSED / promovido para `main`.
- KD 2.1.0: PASS técnico full-corpus + PASS humano 8/8.
- G-245: REBASELINED / IN PROGRESS; PR #4 DRAFT / NÃO MERGEAR.
- ADR-004-001: Core Blocks como destino editorial canônico.
- T091/T093/T094/T096/T097: PASS AMBIENTAL.
- T095: PASS LOCAL / READ-ONLY.
- T098.1: FAIL CONTROLADO / SEM MUTAÇÃO.
- T098.2: **PASS AMBIENTAL**.
- T099A: **IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE**.

## T098.2 — PASS AMBIENTAL
Evidência: `evidence/g245-t098-readiness-pass-20260917T191436Z.json`.
SHA-256 bruto recebido: `f7bf589858db6c6629cb937ba28dad4ef658d1e265225185dad77e55dd342ff0`.

Resultado:
- corpus 623/623 em duas passagens;
- errors 0; throwables 0; safety violations 0;
- dry-run hash mismatches 0; journal hash mismatches 0;
- dry-run: ready 611, noop 7, review_required 5;
- 611 journals preparados apenas em memória;
- batch planner: 611 elegíveis, 25 batches, cobertura 611, duplicidades 0, cursor failures 0;
- corpus/fingerprint editorial unchanged;
- `t098_block_migration_readiness_pass=true`.

## T099A — Journal Store + Lock ambiental
Objetivo: provar no WordPress real que os contratos Block Migration de persistência defensiva funcionam antes do canário.

O smoke:
1. seleciona automaticamente um candidato `legacy_html`/`plain_text` com dry-run `ready` e sem journal/lock Block Migration pré-existente;
2. captura hashes de `post_content` e `_elementor_data`;
3. prepara e persiste um journal Block Migration em postmeta privado;
4. faz readback e valida integridade;
5. adquire lock exclusivo temporário e faz readback;
6. libera lock;
7. remove o evento de journal criado pelo smoke;
8. exige contagem de journal 0→0, lock ausente no final e hashes editoriais idênticos.

Postmeta temporário permitido:
- `_bdc_kb_block_migration_journal`;
- `_bdc_kb_block_migration_lock`.

O T099A NÃO executa `wp_update_post`, não escreve `_elementor_data`, não executa shortcode, não renderiza blocos e não habilita writer/migration.

## Próximos passos
- T099A: homologar journal store + lock + cleanup.
- T099B: selecionar 1 `legacy_html` de baixo risco e gerar Authorization Pack completo.
- T099C: canário real de 1 artigo + rollback, somente após autorização específica.
- T100: batches homologados.
- T101: inventário residual Elementor e gate de retirada.
- G-250: Lifecycle/RC.

## Guardrails
- UX-002 não pode regredir.
- plugin Gutenberg não é dependência.
- Elementor não é removido antes de dependência zero.
- source mixed continua humano.
- nenhum writer editorial está autorizado.
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
