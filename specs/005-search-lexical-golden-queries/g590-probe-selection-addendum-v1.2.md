# G-590 — Probe Selection Addendum v1.2

**Status:** FROZEN PARA HOMOLOGAÇÃO  
**Data:** 2026-09-21

## Problema

O runner ambiental originalmente aceitava como probe apenas headings cujo `title_norm` fosse globalmente único no corpus.

Isso não é um requisito do produto. Títulos legítimos como `Pré-requisitos`, `Procedimento` ou `Observações` podem existir em muitos artigos, inclusive em source kinds pequenos.

O gate poderia portanto produzir falso NO-GO por fragilidade metodológica, não por falha de Section Retrieval.

## Decisão

A seleção de probes passa a ser determinística e orientada à capacidade real.

### Estratégia 1 — `unique_title`

Quando o título da seção é único no corpus:

```text
probe_query = section.title
```

### Estratégia 2 — `title_plus_rare_section_token`

Quando o título é repetido:

1. normalizar tokens da seção;
2. remover tokens já presentes no título;
3. ordenar os candidatos por frequência global crescente;
4. desempatar lexicalmente;
5. escolher o primeiro token cuja query final respeite o Search Query Contract;
6. executar:

```text
probe_query = section.title + rare_section_token
```

## Evidência

Cada probe registra:

- `query`;
- `query_strategy`;
- `title_frequency`;
- `discriminator_token`;
- `discriminator_frequency`;
- `section_key`;
- `section_query_found_expected`;
- `anchor_materialized`;
- `visible_text_equal`.

## Regra de aprovação

A estratégia de query não relaxa o resultado esperado.

O probe continua PASS somente quando:
- o parent scope autorizado contém o artigo esperado;
- a Section exata é encontrada pela `section_key`;
- o anchor projetado materializa;
- o texto visível não muda.

Se um source kind com anchors geráveis não possuir nenhum probe formulável segundo essas regras, ele permanece em `unprobed_source_kinds` e G-590 não passa.

## Não objetivos

Este addendum:
- não altera o algoritmo de produção;
- não altera pesos do parent ranker;
- não altera Section Ranker;
- não cria sinônimos;
- não adiciona heurística para mascarar retrieval ruim.

Ele corrige exclusivamente a metodologia de evidência ambiental.
