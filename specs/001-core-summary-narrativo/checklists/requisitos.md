# Checklist de Requisitos — SPEC-001

## Definition of Ready

- [x] T097 autoriza a SPEC.
- [x] HEAD/branch de entrada confirmados.
- [x] baseline e referências lidos.
- [x] target `post` comprovado no baseline.
- [x] post types suportados explicitados: somente `post`.
- [x] Matriz de Mutação criada.
- [x] Matriz de Evidência criada.
- [x] contratos empty/delete/limite/sanitização definidos.
- [x] fault injection B-006 definido.
- [x] UI/UX/browser acceptance definido.
- [x] aceite/não aceite/rollback definidos.
- [x] princípio de negação aplicado.
- [x] fora de escopo explícito.

## Guardrails durante implementação

- [ ] nenhum runtime fora da pasta/plugin definido pelo plano;
- [ ] nenhuma tabela/schema/migration;
- [ ] nenhuma REST/AJAX/SPA;
- [ ] nenhuma IA/Search/Analytics/Classificação/Review;
- [ ] nenhuma escrita em `_elementor_data`;
- [ ] nenhuma escrita em `post_content`/`post_title`;
- [ ] capability por objeto no ponto de mutação;
- [ ] POST + nonce;
- [ ] allowlist exata de três campos;
- [ ] read-after-write;
- [ ] B-006 implementado e testado;
- [ ] CONTINUIDADE atualizado após mudança material.

## Definition of Done

Usar `docs/DEFINITION-OF-DONE.md` integralmente. Este checklist não o substitui.
