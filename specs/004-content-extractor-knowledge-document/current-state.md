# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline e gates
- SPEC-001/002/003: concluídas.
- UX-001/UX-002: concluídas; UX-002 `0.4.0-ux002.3` é contrato visual obrigatório.
- G-240: PASS / CLOSED / promovido para `main`.
- KD 2.1.0: PASS técnico full-corpus + PASS humano 8/8.
- G-245: REBASELINED / IN PROGRESS; PR #4 DRAFT / NÃO MERGEAR.
- ADR-004-001: Core Blocks como destino editorial canônico.
- T091/T093/T094/T096/T097/T098.2/T099A: PASS AMBIENTAL.
- T095: PASS LOCAL / READ-ONLY.
- T098.1: FAIL CONTROLADO / SEM MUTAÇÃO.
- T099B: IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE / READ-ONLY.
- T099C: BLOCKED por autorização específica.

## T099A — PASS AMBIENTAL
Evidência: `evidence/g245-t099a-storage-lock-20260917T193102Z.json`.
SHA-256 bruto: `671e6f26de0d232569a7728651e7d42348677607b7ac5bcfc194012031d21ba8`.

Ambiente: WordPress 6.9.4 / PHP 8.5.10 / build `0.4.0-g245-storage-lock-t099a.1`.

Target: post 358, `legacy_html`, dry-run `ready`.

Resultado:
- journal before_count=0, persisted=true, readback=true, cleanup=true, after_count=0;
- lock present_before=false, acquired=true, readback=true, cleanup=true, present_after=false;
- `post_content` SHA-256 unchanged;
- `_elementor_data` SHA-256 unchanged;
- errors=[];
- somente postmeta privado temporário;
- `t099a_storage_lock_pass=true`.

## T099B — Authorization Pack
Contrato: `t099b-authorization-pack-contract-v1.md`.
Runtime: `includes/class-block-migration-authorization-pack-smoke.php`.

Objetivo: selecionar deterministicamente um único `legacy_html` low-risk e gerar o pack que vincula o futuro T099C a `post_id + authorization_id` específicos.

Critérios de baixo risco:
- dry-run ready;
- zero journal/lock residual;
- `_elementor_data` vazio;
- conteúdo <=30 KB;
- zero shortcode registrado;
- zero script/iframe/form/object/embed/style;
- zero `<!-- wp:`;
- até 10 links, 1 imagem e 1 tabela.

Post 358 é preferido apenas se continuar satisfazendo todos os critérios. Caso contrário, seleciona-se deterministicamente o menor `risk_score`.

T099B não persiste journal, não adquire lock, não escreve conteúdo, não renderiza blocks/shortcodes e não chama rede.

## Próximos passos
- homologar T099B e versionar o Authorization Pack;
- solicitar aprovação humana que cite o `post_id` e `authorization_id` do pack;
- somente então preparar/executar T099C apply + verify + rollback imediato;
- T100 batches homologados;
- T101 inventário residual Elementor / gate de retirada;
- G-250 Lifecycle/RC.

## Guardrails
- UX-002 não pode regredir.
- plugin Gutenberg não é dependência.
- Elementor não é removido antes de dependência zero.
- source mixed continua humano.
- nenhum writer editorial está autorizado antes do T099C específico.
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
