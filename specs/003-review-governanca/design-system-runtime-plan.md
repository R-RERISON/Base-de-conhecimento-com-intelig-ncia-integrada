# SPEC-003 — Design System Runtime Foundation

**Status:** AUTORIZADO / EM EXECUÇÃO
**Base visual:** UX-001 / Design System v1
**Base funcional:** `0.3.0-dev.2`, G-030 ambiental PASS

## Decisão

O Design System não será postergado para o fim da SPEC-003. A fundação visual entra no runtime **antes** da abertura da UI de Review, em uma mudança isolada que não altera stores, writers, schemas ou regras de negócio.

A razão é separar duas preocupações:

1. **fundação visual do produto** — tokens, superfícies, hierarquia, densidade, responsividade e componentes do Workspace/List;
2. **capacidade funcional de Review** — handler HTTP, transições e ações, que continua sujeita ao Gate G-070.

Assim evitamos tanto uma UI nativa acumulativa quanto a introdução precoce de ações de Review não homologadas.

## Escopo do primeiro runtime visual

### Entra agora

- CSS Custom Properties canônicas do Design System v1;
- background/surface/border/radius/spacing tipados;
- Context Header do artigo;
- cards/panels para Summary e Classificação;
- tabela/lista administrativa com densidade e hierarquia coerentes;
- botões e links refinados sem substituir semântica do WordPress;
- empty/legacy blocks coerentes;
- reflow `<=782px`;
- foco visível;
- compatibilidade com stack administrativa do WordPress.

### Não entra nesta etapa

- Review disponível como ação;
- badges `approved`, `AI Ready` ou scores;
- nova sidebar paralela dentro do wp-admin;
- Search/Resolvedor;
- dashboard/analytics;
- JS de SPA;
- framework CSS externo;
- novo writer por motivo visual.

## Contrato de não regressão

A fundação visual não pode alterar:

- Summary Store/handler;
- Classification Store/handler;
- Review Store;
- taxonomias canônicas;
- metadados canônicos;
- `post_content` / `_elementor_data`;
- nonce/capability/PRG existentes.

## Sequência da SPEC-003

1. R-001 — PASS;
2. R-010 — PASS;
3. G-001 — PASS;
4. G-030 — PASS real;
5. **DS-010 — Design System Runtime Foundation**;
6. G-070 — handler HTTP Review;
7. G-110 — Knowledge Workspace com Review real;
8. G-130 — package limpo/lifecycle.

## Critério DS-010

PASS quando:

- telas existentes adotarem tokens/superfícies/hierarquia do Design System v1;
- Summary e Classificação continuarem funcionais;
- nenhum Review falso estiver exposto;
- desktop e `<=782px` permanecerem utilizáveis;
- a mudança for predominantemente CSS/markup, sem alteração de domínio.

## Regra

Design System entra **agora**, porém feature funcional continua entrando somente após seus gates. Aparência não autoriza comportamento.
