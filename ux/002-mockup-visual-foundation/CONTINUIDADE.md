# Continuidade — UX-002 Mockup Visual Foundation

## Estado

- branch: `ux002-mockup-visual-foundation`;
- baseline: `main@72f26121373b12fa08f08ea8d38b4c8d73f8637c`;
- `scr/`: 10 mockups PNG presentes;
- UX-001 Design System/protótipo: baseline visual anterior;
- objetivo: convergir runtime para mockups sem alterar contratos WordPress/dados;
- build de homologação prevista: `0.4.0-ux002.1`.

## Decisões

1. WordPress continua shell; aparência interna é BDC.
2. Não criar segunda sidebar.
3. Não introduzir framework frontend.
4. Reutilizar classes atuais antes de criar abstrações.
5. Mockups não autorizam features ainda inexistentes.
6. Toda tela futura deve reutilizar o Visual Contract v2.

## Invariantes

- nenhuma mudança de `post_content` ou `_elementor_data`;
- payloads/capabilities/nonces/storage permanecem iguais;
- SPEC-004/G-245 continua independente;
- UX-002 deve poder ser revertida sem migration.

## Próximo passo

Aplicar foundation CSS aos arquivos `admin.css`, `workspace.css`, `history.css`, elevar build para `0.4.0-ux002.1`, validar lint/estrutura e gerar pacote para homologação visual.

## Gate de aceite

Não promover enquanto não houver revisão humana em desktop + 782px + 520px contra mockups aplicáveis, além de smoke funcional dos fluxos já homologados.

> Quem não sabe onde está, não sabe para onde quer ir.
