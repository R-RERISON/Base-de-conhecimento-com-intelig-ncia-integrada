# Package `0.3.0-dev.8` — Browser Acceptance G-110

## Objetivo

Fechar o Gate G-110 com evidência reproduzível no browser real, sem ampliar o domínio funcional aprovado no `0.3.0-dev.7`.

## Regra de escopo

O runtime funcional do Workspace permanece congelado no comportamento já homologado no `dev.7`.

O `dev.8` adiciona somente instrumentação temporária de aceitação:

- `class-workspace-browser-diagnostics.php`;
- `assets/js/browser-acceptance.js`;
- flag `BDC_KB_WORKSPACE_BROWSER_DIAGNOSTICS_BUILD`.

Esses artefatos devem ser removidos no G-130 antes do RC.

## Segurança das fixtures

O runner:

- exige `manage_options`;
- cria um único post `draft` de fixture;
- cria quatro termos temporários, um por taxonomia canônica;
- não usa posts reais;
- não escreve `post_content` ou `_elementor_data` de conteúdo real;
- usa os writers reais de Summary, Classificação e Review contra a fixture;
- remove eventos, post e termos da fixture ao finalizar;
- limpa fixture stale no próximo start caso uma execução seja interrompida.

## Browser Acceptance

A execução cobre:

- cinco tabs autorizadas do Workspace;
- Context Header + Main Work Area;
- ausência de `AI Ready`/score não contratado;
- teclado `ArrowLeft`, `ArrowRight`, `Home`, `End`;
- foco visível e semântica nativa de links;
- save real de Summary e permanência na tab;
- save real de Classificação e permanência na tab;
- `unreviewed -> in_review`;
- `NO_CHANGE` sem novo evento;
- `needs_changes` sem nota rejeitado;
- `needs_changes` com nota;
- `approved`;
- Histórico read-only coerente;
- UI sem ação de reviewer quando a capability é negada pelo probe assinado;
- reflow em 1440, 1024, 782 e 492 px;
- zero overflow indevido no escopo do plugin.

O servidor também valida antes do cleanup:

- `post_status` preservado;
- `post_content` preservado;
- `_elementor_data` preservado;
- Summary canônico;
- Classificação canônica;
- exatamente três eventos válidos de Review;
- estado final `approved`.

## Critério de PASS

G-110 somente pode avançar quando o JSON real apresentar:

- `browser_fail = 0`;
- `server_fail = 0`;
- `overall = PASS`;
- `residual_posts = 0`;
- `residual_terms = 0`;
- `residual_review_events = 0`.

Qualquer FAIL mantém G-110 aberto e deve ser tratado sem mascarar a evidência.

## Package instalável

Versão: `0.3.0-dev.8`

SHA-256:

`661e819b2b093bb4297cedfe82c43aa6b5a02b0846b731b36d3b57961ab328e4`

Validação pré-package:

- PHP lint: PASS em 14 arquivos PHP;
- `workspace.js`: syntax PASS;
- `browser-acceptance.js`: syntax PASS;
- estrutura instalável WordPress: PASS.

## Próximo passo

Instalar o `dev.8`, abrir **Base de Conhecimento** como administrador e executar **Browser Acceptance G-110**. Retornar o `bdc-kb-g110-browser-acceptance-*.json` gerado. Não promover G-110 a PASS antes dessa evidência.