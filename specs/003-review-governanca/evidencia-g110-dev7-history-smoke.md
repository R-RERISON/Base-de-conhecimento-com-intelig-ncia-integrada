# Evidência G-110 — Smoke ambiental do Histórico `0.3.0-dev.7`

## Ambiente observado

Captura real fornecida pelo operador após instalação do build `0.3.0-dev.7` no ambiente WordPress de homologação.

## Resultado observado

A tab **Histórico** está presente no Knowledge Workspace e foi renderizada como superfície própria, sem writer.

A captura demonstra:

- navegação do Workspace preservada com `Visão geral`, `Summary`, `Classificação`, `Review & Governança` e `Histórico`;
- Context Header do artigo preservado;
- Histórico renderizado como projection read-only;
- eventos exibidos do mais recente para o mais antigo;
- transição `from -> to` visível;
- ator e timestamp visíveis;
- badge do estado final visível;
- nota exibida quando presente;
- nenhuma métrica, score ou `AI Ready` introduzido.

Sequência visível no smoke:

1. `Não revisado -> Em revisão`;
2. `Em revisão -> Aprovado`;
3. `Aprovado -> Em revisão`.

A sequência é compatível com a máquina de estados aprovada da SPEC-003 e comprova que a UI está projetando eventos reais do domínio de Review & Governança.

## Decisão

- **W-003 Histórico read-only: PASS ambiental inicial.**
- Desktop amplo: **PASS visual inicial**, sem overflow horizontal visível na captura.
- Teclado/foco: ainda não validado ambientalmente.
- Viewports 1024/782/~492: ainda não validados ambientalmente.
- G-110 permanece **ACTIVE**.

## Próximo gate

Para fechar G-110 ainda é obrigatório validar no browser real:

- `ArrowLeft` / `ArrowRight` / `Home` / `End` entre tabs;
- foco visível;
- 1024px / 782px / ~492px;
- ausência de overflow horizontal indevido;
- regressão final de Summary/Classificação;
- fluxo de Review e histórico consistente após transição.
