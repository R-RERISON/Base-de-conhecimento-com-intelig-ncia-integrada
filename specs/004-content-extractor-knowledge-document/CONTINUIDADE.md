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
- T091/T093/T094/T096/T097/T098.2/T099A: PASS AMBIENTAL.
- T095: PASS LOCAL / READ-ONLY.
- T098.1: FAIL CONTROLADO / SEM MUTAÇÃO.
- T099B: IMPLEMENTADO / HOMOLOGAÇÃO PENDENTE / READ-ONLY.
- T099C: BLOCKED até autorização específica.

## Evidência T099A
Arquivo: `evidence/g245-t099a-storage-lock-20260917T193102Z.json`.
SHA-256 bruto: `671e6f26de0d232569a7728651e7d42348677607b7ac5bcfc194012031d21ba8`.

Target: post 358, `legacy_html`, dry-run ready.

Comprovado no ambiente:
- journal privado persistido/readback/cleanup PASS, 0→0;
- lock exclusivo acquire/readback/release PASS, ausente antes/depois;
- hashes de `post_content` e `_elementor_data` inalterados;
- zero errors;
- nenhum write editorial;
- `t099a_storage_lock_pass=true`.

## T099B — próximo gate exato
Contrato: `t099b-authorization-pack-contract-v1.md`.
Runtime: `includes/class-block-migration-authorization-pack-smoke.php`.

O runner é read-only e:
1. avalia os posts com dry-run;
2. restringe a `legacy_html` low-risk;
3. exige zero journal/lock residual e `_elementor_data` vazio;
4. rejeita shortcodes registrados, scripts/iframes/forms/embeds/styles e block comments;
5. limita tamanho/links/imagens/tabelas;
6. prefere o post 358 apenas se continuar low-risk;
7. gera `authorization_id` determinístico ligado aos hashes atuais;
8. descreve exatamente o futuro apply + verify + rollback do T099C.

O pack não exporta corpo editorial/URLs, não persiste journal, não adquire lock e não escreve conteúdo.

## Depois do T099B
- versionar o Authorization Pack ambiental;
- apresentar `post_id`, título, risco e `authorization_id` ao usuário;
- obter autorização explícita para esse escopo exato;
- T099C: aplicar serialização Core Blocks, verificar e fazer rollback imediato;
- T100: batch controlado;
- T101: dependência residual Elementor / gate de retirada;
- G-250 Lifecycle/RC.

## Guardrails absolutos
- UX-002 intacta.
- plugin Gutenberg não é dependência.
- Elementor não é removido agora.
- mixed exige humano.
- nenhum write editorial sem gate + autorização específica.
- PR #4 permanece DRAFT.

> Quem não sabe onde está, não sabe para onde quer ir.
