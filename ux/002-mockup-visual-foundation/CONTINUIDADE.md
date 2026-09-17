# Continuidade — UX-002 Mockup Visual Foundation

## Estado

- branch: `ux002-mockup-visual-foundation`;
- baseline original: `main@72f26121373b12fa08f08ea8d38b4c8d73f8637c`;
- PR: `#6`;
- `scr/`: 10 mockups PNG presentes;
- UX-001 Design System/protótipo: baseline visual anterior;
- build visual homologada: `0.4.0-ux002.3`;
- aceite visual humano: **PASS / APPROVED em 2026-09-17**;
- UX-002: **PASS / CLOSED**.

## Implementado e aprovado

1. Constituição 1.2.0 com autoridade visual dos mockups/Design System e princípio de IA orientado à redução de esforço de leitura/tempo para resposta confiável.
2. `visual-contract-v2.md` como contrato permanente do produto.
3. `assets/css/visual-foundation.css` como camada visual compartilhada.
4. `Visual_Foundation` aplicando o shell visual BDC também às telas nativas dos vocabulários sem substituir a Taxonomy API.
5. Build final `0.4.0-ux002.3`.
6. Knowledge List com busca, métricas, badges e tabela densa.
7. Workspace com hero único, tabs, área principal e painel contextual.
8. Summary, Classificação, Review e Histórico convergidos para o mesmo Design System.
9. Vocabulários com hero BDC, formulários/tabelas reestilizados e retorno contextual ao artigo de origem.
10. Ações de vocabulário em botões/chips discretos.
11. Dashicons nativos como iconografia discreta, sem biblioteca visual externa.
12. Responsividade e hierarquia visual preservadas.

## Validação

- PHP lint da build visual: 29/29 PASS;
- CSS final: 182/182 blocos balanceados;
- nenhuma dependência visual externa;
- nenhuma mudança de payload/capability/nonce/storage;
- nenhuma escrita adicional em `post_content` ou `_elementor_data`;
- homologação humana: PASS.

## Regra permanente

Toda nova UI ou alteração material de UI deve seguir `visual-contract-v2.md` e os mockups aplicáveis de `scr/`. O contrato visual passa a ser gate de Definition of Done e requisito para agentes humanos/IA.

WordPress continua shell/plataforma. Aparência genérica do wp-admin não é resultado final aceitável para superfícies BDC, salvo exceção explicitamente justificada e documentada.

## Invariantes

- UX não autoriza feature funcional inexistente;
- UX não altera a autoridade editorial do WordPress/Elementor;
- SPEC-004/G-245 continua independente;
- writer/migration Elementor continuam sujeitos aos gates próprios da SPEC-004;
- divergência visual deliberada exige decisão explícita e evidência.

## Próximo passo do projeto

Após promover o PR #6 para `main`, sincronizar `spec004-g245-production-readiness` com a nova baseline visual e retomar a SPEC-004 a partir do T082, preservando T081 PASS e mantendo writer/migration disabled-by-default.

> Quem não sabe onde está, não sabe para onde quer ir.
