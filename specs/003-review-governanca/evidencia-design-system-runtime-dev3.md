# SPEC-003 — Evidência visual do Design System Runtime / 0.3.0-dev.3

## Origem

Evidência fornecida pelo operador a partir do ambiente real após instalação do `0.3.0-dev.3`.

Foram observadas as seguintes superfícies:

1. Knowledge List / listagem de artigos;
2. contexto do artigo;
3. Summary narrativo;
4. Classificação de Conhecimento.

## Resultado observado

A fundação visual do Design System v1 está efetivamente presente no runtime:

- header/surface com identidade de produto;
- cards/surfaces com bordas, radius e elevação discreta;
- hierarquia tipográfica superior ao baseline nativo do wp-admin;
- botões e links com linguagem visual consistente;
- tabela/listagem com densidade e estrutura mais claras;
- Summary e Classificação visualmente agrupados em surfaces próprias;
- bloco de referência legada visualmente secundário;
- nenhuma regressão funcional reportada pelo operador.

## Achado importante

A captura confirma também que o runtime ainda usa a arquitetura vertical histórica:

`Summary -> Classificação`

Esse formato é aceitável para a **fundação DS-010**, porém NÃO é o Knowledge Workspace final. Ele mantém o anti-padrão de crescimento vertical que a UX-001 explicitamente bloqueia quando novos domínios forem adicionados.

Decisão:

- DS-010 valida tokens, surfaces e linguagem visual no runtime;
- a reorganização estrutural para Workspace/tabs permanece pertencendo ao G-110;
- Review não será acrescentado como um terceiro bloco vertical;
- quando o writer HTTP de Review estiver aprovado, Summary, Classificação e Review devem convergir para a navegação canônica do Knowledge Workspace.

## Densidade / largura

Na listagem desktop, o conteúdo ainda ocupa largura conservadora e deixa área livre significativa em viewport largo. Isso não é blocker de DS-010, mas deve ser refinado no Workspace/List final para aproximar a densidade operacional do benchmark KB2Ops.

## Responsividade

As capturas fornecidas são desktop. Portanto não constituem evidência humana de `<=782px` ou `~492px` para esta revisão de CSS.

A validação definitiva de reflow/foco continua em G-110 Browser Acceptance; não deve ser inferida a partir destas capturas.

## Decisão

**Gate DS-010: PASS — Runtime Foundation.**

Escopo do PASS:

- linguagem visual e tokens presentes no runtime real;
- surfaces principais coerentes;
- sem regressão funcional observada.

Não significa aprovação do layout final do Knowledge Workspace.

## Próxima consequência

S004 / G-070 pode iniciar.

A UI funcional de Review só entra após a prova do handler HTTP. Quando entrar, deve fazê-lo dentro do shell Workspace, evitando empilhamento vertical adicional.
