# Avaliação WordPress-first — SPEC-005

## Questão

Qual é o menor mecanismo capaz de recuperar artigos com relevância suficiente, determinismo e Golden regression?

## Opção A — WP_Query nativo

Prós:
- zero schema;
- lifecycle nativo;
- visibilidade WordPress;
- baixa complexidade.

Limitações a medir:
- busca nativa não consome automaticamente Content Extractor;
- ranking/operação pode ser insuficiente;
- cobertura de Elementor/mixed precisa ser comprovada;
- explicabilidade limitada.

**Status:** baseline executada. Sozinha, insuficiente para o objetivo final.

## Opção B — WP_Query + hooks mínimos

Possíveis ajustes:
- filtros de post type/status;
- extensão de campos;
- ordenação limitada.

**Regra:** não usar hooks globais que alterem busca do site sem escopo inequívoco.

## Opção C — Search Retrieval Projection post-level

Permitida pela SPEC-000 como projeção reconstruível se B-001/benchmark/Golden justificarem.

Requisitos:
- derivada do Content Extractor;
- nunca fonte da verdade;
- WordPress revalida autorização;
- rebuild idempotente;
- versionamento;
- bounded retrieval;
- schema mínimo.

## Opção D — FULLTEXT

Só considerar dentro da Projection e apenas se:
- engine MariaDB suportar;
- benchmark mostrar benefício;
- comportamento lexical for reproduzível;
- fallback bounded existir.

## Decisão inicial

**Nenhuma tabela autorizada antes de R-500/R-510.**

A decisão G-520 deve registrar uma destas conclusões:
- A suficiente;
- B suficiente;
- C necessária;
- C + FULLTEXT necessário.

Qualquer decisão deve anexar benchmark e Golden evidence.


## Resultado R-500

### A — WP_Query nativo
Muito superior ao comportamento administrativo atual em title-token self-retrieval: 85% Top-1 / 100% Top-20. Deve ser preservado como baseline/fallback.

### B — WP_Query + hooks
Corrigir `modified DESC` é necessário, mas não resolve conteúdo semântico ausente.

### C — Search Retrieval Projection
**Justificada conceitualmente** por gap semântico 14,92%, Summary gap 77,78% e casos Elementor extremos.

Isso ainda não autoriza uma tabela. G-520 decide a forma de persistência após R-510.

### D — FULLTEXT
Continua NOT_DECIDED. Precisa de Golden + benchmark do engine/projection.


## Ownership / independência

WordPress-first **não proíbe schema próprio**.

A decisão final deve escolher o menor mecanismo correto, porém:
- se a Projection precisar persistência, a tabela será do novo plugin;
- nenhuma tabela ASI será reutilizada;
- FULLTEXT, índices e migrations serão próprios;
- WordPress continua autoridade editorial e de acesso;
- ASI pode ser removido sem degradar Search.

R-500 já forneceu evidência material suficiente para considerar uma Projection própria em G-520.
