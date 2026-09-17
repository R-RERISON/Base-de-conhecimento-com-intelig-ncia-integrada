# Current State — UX-002

## Baseline

`main`: `72f26121373b12fa08f08ea8d38b4c8d73f8637c`.

A baseline contém 10 referências PNG em `scr/` e mantém UX-001 como contrato visual anterior: Design System v1 + protótipo UI-as-Code.

## Problema resolvido

O runtime era WordPress-first funcionalmente, porém apresentava drift visual e ainda herdava aparência genérica do wp-admin em fluxos centrais.

## Implementação concluída

A UX-002 promoveu para o runtime uma fundação visual compartilhada e progressiva, preservando WordPress como shell/plataforma e suas APIs como primitives funcionais.

A build visual homologada é **`0.4.0-ux002.3`**.

Foram homologados como conjunto coerente:

- Knowledge List;
- Knowledge Workspace;
- Summary;
- Classificação;
- Review & Governança;
- Histórico;
- telas nativas dos quatro vocabulários com shell visual BDC;
- navegação contextual entre Classificação e vocabulários, preservando o artigo de origem;
- ações discretas para gerenciamento de vocabulários;
- iconografia discreta com Dashicons nativos;
- responsividade e hierarquia visual da fundação.

Nenhuma alteração de payload, endpoint, capability, nonce, storage, `post_content` ou `_elementor_data` foi introduzida pela UX-002.

## Validação técnica

Na build visual final:

- PHP lint do pacote: **29/29 PASS**;
- CSS: **182/182 blocos balanceados**;
- JavaScript existente: válido;
- raiz única do pacote: PASS;
- instalação/upgrade em homologação: executados durante as iterações visuais;
- nenhuma dependência visual externa adicionada.

## Gate humano

**PASS / APPROVED em 2026-09-17.**

A homologação humana aprovou a fundação após as iterações `.1`, `.2` e `.3`. A `.3` fechou os ajustes finais de navegabilidade, ações de vocabulário e iconografia.

## Contrato permanente

A partir deste fechamento, qualquer nova tela, formulário, tabela, estado, componente ou alteração material de UI pertencente ao produto DEVE:

1. seguir `ux/002-mockup-visual-foundation/visual-contract-v2.md`;
2. usar `scr/` como referência quando houver mockup aplicável;
3. reutilizar tokens/componentes BDC antes de criar variantes;
4. preservar WordPress como shell sem aceitar aparência genérica do wp-admin como resultado final do produto;
5. passar validação visual/responsiva antes de ser considerada Done.

Divergência intencional exige decisão explícita e documentada.

## Estado

- UX-002: **PASS / CLOSED**;
- build visual homologada: **`0.4.0-ux002.3`**;
- Constituição 1.2.0: vigente;
- Visual Contract v2: obrigatório;
- PR #6: autorizado para promoção a `main`;
- SPEC-004/G-245: permanece independente e não é promovida por este fechamento visual.
