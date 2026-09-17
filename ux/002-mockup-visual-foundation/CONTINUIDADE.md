# Continuidade — UX-002 Mockup Visual Foundation

## Estado

- branch: `ux002-mockup-visual-foundation`;
- baseline: `main@72f26121373b12fa08f08ea8d38b4c8d73f8637c`;
- PR: `#6` DRAFT;
- `scr/`: 10 mockups PNG presentes;
- UX-001 Design System/protótipo: baseline visual anterior;
- build de homologação: `0.4.0-ux002.1`;
- implementação base: READY FOR HOMOLOGATION;
- aceite visual humano: PENDING.

## Implementado

1. Constituição 1.2.0 com autoridade visual dos mockups/Design System e princípio de IA orientado à redução de esforço de leitura/tempo para resposta confiável.
2. `visual-contract-v2.md` como contrato da convergência visual.
3. `assets/css/visual-foundation.css` como camada visual compartilhada e escopada em `.bdc-kb-admin`.
4. `Visual_Foundation` carrega a camada somente na superfície administrativa BDC.
5. Build elevada para `0.4.0-ux002.1` para evitar cache de assets antigos.
6. Componentes/tokens base para hero, botão, panel/card, tabs, tabela, paginação, formulário, badges, notices, toolbar, métricas, grid e empty state.
7. Responsividade 900/782/520px sem segunda sidebar.

## Validação técnica

- 29 arquivos PHP do pacote: lint PASS;
- CSS: 87/87 blocos balanceados;
- nenhum seletor global `body` introduzido;
- dependências da Visual Foundation: PASS;
- ZIP reextraído: PASS;
- raiz única: PASS;
- SHA-256: `d2e95e49600a0cd5776a8546ff34cc41109835b6838018fd379900d01179a215`.

## Decisões

1. WordPress continua shell; aparência interna é BDC.
2. Não criar segunda sidebar.
3. Não introduzir framework frontend.
4. Reutilizar classes atuais antes de criar abstrações.
5. Mockups não autorizam features ainda inexistentes.
6. Toda tela futura deve reutilizar o Visual Contract v2.
7. Mudanças visuais são progressivas: não construir telas futuras antes da SPEC funcional correspondente.

## Invariantes

- nenhuma mudança de `post_content` ou `_elementor_data`;
- payloads/capabilities/nonces/storage permanecem iguais;
- SPEC-004/G-245 continua independente;
- UX-002 deve poder ser revertida sem migration.

## Próximo passo exato

Instalar `0.4.0-ux002.1` em homologação e executar revisão visual humana das telas atuais em desktop, 782px e 520px comparando com `scr/`, seguida de smoke funcional de Summary, Classificação, Review e Histórico.

## Gate de aceite

Não promover PR #6 enquanto não houver:

- comparação humana satisfatória com mockups aplicáveis;
- foco/teclado/contraste preservados;
- responsividade válida em 1440/782/520;
- zero regressão funcional nos writers existentes.

> Quem não sabe onde está, não sabe para onde quer ir.
