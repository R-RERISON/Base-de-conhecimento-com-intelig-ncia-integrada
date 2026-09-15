# G-110 — Knowledge Workspace / Browser Acceptance

## Estado de entrada

Gate de entrada obrigatório satisfeito:

- R-001: PASS;
- R-010: PASS;
- G-001: PASS;
- G-030: PASS;
- DS-010: PASS;
- G-070: **PASS — `0.3.0-dev.5`, 22/22, cleanup zero resíduos**.

## Autoridades

Ordem de autoridade:

1. contratos funcionais SPEC-001, SPEC-002 e SPEC-003;
2. `ux/001-product-experience-knowledge-workspace/ux-baseline-v1.md`;
3. `ux/001-product-experience-knowledge-workspace/information-architecture.md`;
4. Design System v1 / UI as Code v0.2;
5. refinamento visual local.

## Objetivo

Convergir a tela atual de artigo para o **Knowledge Workspace** canônico e introduzir Review & Governança como domínio real, sem criar um terceiro bloco vertical.

Fluxo alvo:

`Knowledge List -> Knowledge Workspace -> tab ativa -> ação -> feedback -> permanência no mesmo artigo`

## Estrutura obrigatória

O Workspace deverá conter:

- Context Header persistente;
- navegação por tabs horizontais;
- Main Work Area;
- Context Panel apenas se houver informação real/acionável;
- fluxo de uma coluna em `<=782px`.

Tabs autorizadas nesta etapa:

1. **Visão geral**;
2. **Summary**;
3. **Classificação**;
4. **Review & Governança**;
5. **Histórico** somente como projection real do event log canônico, sem writer próprio.

A inclusão de Histórico é permitida porque a SPEC-003 já definiu a sequência de eventos `bdc_kb_review_event` como fonte canônica de auditoria. Histórico não ganha schema nem persistência própria.

## Review & Governança — conteúdo permitido

Mostrar somente fatos contratados:

- estado atual: `unreviewed`, `in_review`, `needs_changes`, `approved`, `excluded`;
- última decisão válida;
- actor do último evento, quando existir;
- data GMT/local apresentada de forma consistente;
- transições permitidas para o estado atual e capabilities do usuário;
- nota da decisão;
- feedback explícito após POST;
- histórico append-only real.

## Proibições

Não introduzir:

- `AI Ready`;
- score;
- progresso percentual;
- health score;
- meta duplicada de current state;
- `_reviewed_by` ou `_reviewed_at` paralelos;
- tabela customizada;
- alteração de `post_status` por Review;
- alteração de `post_content` ou `_elementor_data`;
- novo writer para Histórico;
- terceira coluna/sidebar interna no wp-admin;
- terceiro bloco vertical abaixo de Summary/Classificação.

## Integração dos owners

### Summary

Writer e store existentes permanecem owners exclusivos.

A mudança é de composição visual/navegação, não de persistência.

### Classificação

Taxonomias e writer existentes permanecem owners exclusivos.

Links para UI nativa dos vocabulários continuam válidos.

### Review

- writer: `Review_Admin`;
- store: `Review_Store`;
- contrato: `Review_Contract`;
- histórico: eventos `bdc_kb_review_event`.

## Estratégia de implementação sem regressão

### W-001 — Shell do Workspace

Refatorar o markup da tela de artigo para Context Header + tabs + área de conteúdo, sem alterar qualquer writer.

Critério:

- Summary e Classificação continuam salvando exatamente como antes;
- mesma URL/contexto de artigo;
- nenhuma mutação nova.

### W-002 — Tab Review

Adicionar projection do estado atual e formulário separado para transição.

Critério:

- usa `Review_Admin::ACTION` real;
- nonce post-bound;
- somente targets permitidos;
- nota condicionada pelo contrato;
- capability refletida na UI, mas segurança continua server-side.

### W-003 — Tab Histórico

Render read-only dos eventos canônicos.

Critério:

- ordem determinística;
- actor/data/estado/nota;
- ausência de evento = empty state, nunca dado inventado;
- nenhum botão de edição/exclusão de evento.

### W-004 — Feedback e estados

Cobrir:

- saved;
- no_change;
- validation_error;
- forbidden;
- invalid_nonce;
- fail_safe;
- critical;
- erro de integridade de leitura.

Estado não pode depender apenas de cor.

### W-005 — Responsividade e acessibilidade

Viewports mínimos:

- 1440px;
- 1024px;
- 782px;
- ~492px.

Validar:

- zero overflow horizontal;
- tabs acessíveis por teclado;
- foco visível;
- labels associados;
- ordem de tabulação coerente;
- uma coluna em `<=782px`;
- Context Panel, se existir, reflowa e nunca bloqueia conteúdo.

## Browser Acceptance

Runner/browser acceptance temporário deverá usar somente fixtures controladas e gerar JSON reproduzível.

Casos mínimos:

- abrir Knowledge List;
- abrir fixture no Workspace;
- alternar tabs;
- Summary render/salva sem regressão;
- Classificação render/salva sem regressão;
- Review inicia `unreviewed` sem evento;
- submeter `in_review`;
- confirmar feedback + projection do novo estado;
- `NO_CHANGE` sem evento extra;
- reviewer autorizado executa `needs_changes` com nota;
- reviewer autorizado executa `approved`;
- Histórico exibe exatamente os eventos persistidos;
- usuário sem capability não recebe ação indevida;
- teclado/foco;
- 1440/1024/782/~492;
- cleanup zero resíduos.

## Critério de aceite G-110

G-110 só pode ser marcado PASS quando:

- Workspace substitui o empilhamento vertical atual;
- Summary permanece íntegro;
- Classificação permanece íntegra;
- Review usa somente owner/contrato canônico;
- Histórico é read-only e derivado do event log;
- nenhuma feature proibida aparece;
- Browser Acceptance real = PASS;
- reflow/foco = PASS;
- cleanup das fixtures = zero resíduos.

## Próximo build

Próximo build de desenvolvimento sugerido: **`0.3.0-dev.6`**.

O `dev.6` deve priorizar **W-001 + W-002**, mantendo instrumentação temporária de G-070 disponível até existir evidência suficiente para iniciar o cleanup do G-130.