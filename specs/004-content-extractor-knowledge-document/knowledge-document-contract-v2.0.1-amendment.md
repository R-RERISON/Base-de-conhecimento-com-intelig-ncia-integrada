# Knowledge Document Contract v2.0.1 — Semantic Expectation Amendment

**Status:** FROZEN para validação ambiental `0.4.0-acceptance.7`  
**Base:** `knowledge-document-contract-v2.md` (`2.0.0`)  
**Natureza:** patch sem mudança de forma externa do Knowledge Document; corrige a semântica de `structure` e endurece o gate de readiness.

## 1. Motivação

O smoke `0.4.0-acceptance.6` preservou segurança e determinismo, porém manteve `50/622` documentos com `structure_complete=false`.

A telemetria demonstrou que o principal delta de headings (`504 expected / 302 actual`) não era causado majoritariamente por falha de travessia. O corpus histórico contém marcação HTML que não corresponde a unidades globais de conhecimento, especialmente:

- headings vazios;
- headings usados localmente dentro de listas;
- headings usados localmente dentro de tabelas;
- estruturas aninhadas cujo conteúdo já pertence semanticamente ao container pai.

O contrato `2.0.0` comparava contagem DOM bruta contra blocos semânticos. Essas grandezas não são semanticamente equivalentes.

## 2. Princípio de independência

A expectativa estrutural continua **independente de `sections[]` e `blocks[]`**.

`Semantic_DOM_Expectation` calcula diretamente do HTML/DOM de entrada a quantidade de unidades que deveriam ser materializáveis semanticamente. O resultado é usado como `structure` esperado pelo `Semantic_Structure`.

É proibido calcular o expected a partir dos próprios blocos emitidos, porque isso eliminaria a capacidade do gate de detectar perda estrutural.

## 3. Heading global versus heading local

### Heading global

Um `h1..h6` textual que participa do outline editorial global continua sendo contado como heading esperado e deve virar bloco `heading`.

### Heading vazio

Um `h1..h6` cujo texto normalizado é vazio não representa conhecimento. Ele não conta como heading semântico esperado.

Isso não autoriza a remoção do conteúdo de elementos não vazios.

### Heading local

Um `h1..h6` dentro de containers cujo significado já é local — por exemplo lista, tabela, parágrafo, blockquote ou código — não é promovido automaticamente ao outline global.

Seu texto permanece no container semântico correspondente e deve gerar warning explicável:

`HTML_LOCAL_HEADING_FLATTENED:<count>`

Esse warning classifica o documento como `review_required`, não `candidate_ready`.

## 4. Estruturas aninhadas

### Lista dentro de tabela

Quando a lista é preservada apenas como parte do conteúdo da célula e não como árvore autônoma, emitir:

`HTML_NESTED_LIST_IN_TABLE_FLATTENED:<count>`

Classificação: `review_required`.

### Tabela aninhada não representada

Quando uma tabela estruturalmente relevante está dentro de outra tabela e a representação canônica não preserva essa tabela como bloco independente, emitir:

`HTML_NESTED_TABLE_UNREPRESENTED:<count>`

Classificação: `not_ready`.

Nenhum PASS estrutural pode compensar esse warning crítico.

## 5. Estruturas vazias

Tags estruturais vazias podem existir por herança editorial, layout antigo ou migrações históricas. Elas não contam como unidades semânticas esperadas quando não carregam conteúdo significativo nem descendentes semânticos representáveis.

A exclusão é feita na expectativa DOM, não por comparação com `blocks[]`.

## 6. Versionamento

Knowledge Document:

`2.0.1`

O formato externo permanece compatível com `2.0.0`, porém a interpretação de `structure` e os hashes mudam por design. Hashes `2.0.1` não devem ser comparados com `2.0.0` como se representassem a mesma projeção semântica.

Smoke report:

`1.3.0`

## 7. AI readiness

### `candidate_ready`

Somente quando:

- `structure_complete=true`;
- nenhum warning crítico existe;
- nenhum warning de review conhecido existe.

### `review_required`

Inclui, além dos warnings anteriores:

- `HTML_LOCAL_HEADING_FLATTENED:*`;
- `HTML_NESTED_LIST_IN_TABLE_FLATTENED:*`.

### `not_ready`

Inclui, além das causas anteriores:

- `HTML_NESTED_TABLE_UNREPRESENTED:*`.

## 8. Gate ambiental endurecido

O full-corpus smoke só pode produzir `gate.pass=true` quando simultaneamente:

- corpus antes/depois idêntico;
- fingerprint editorial idêntico;
- `changed_posts_during_run=0`;
- todas as fontes geram documento nas duas passagens;
- errors = 0;
- throwables = 0;
- hash mismatches = 0;
- canonical JSON mismatches = 0;
- `structure_incomplete=0` nas duas passagens;
- `ai_readiness.not_ready=0` nas duas passagens;
- `DOMDocument=true` no ambiente homologado.

O novo requisito `not_ready=0` impede falso PASS em situações em que as contagens reconciliam, mas existe perda semântica crítica conhecida.

## 9. O que permanece proibido

- persistir Knowledge Document/hashes durante smoke;
- alterar `post_content`, `_elementor_data`, status, revisão ou publicação;
- executar `do_shortcode()` genérico;
- executar `render_block()` ou renderização dinâmica arbitrária;
- usar IA para reparar parsing;
- migrar conteúdo para Elementor;
- liberar G-245 antes de G-240 v2 PASS.

## 10. Promoção

`2.0.1` é candidato somente após:

1. package parity Git ↔ ZIP;
2. full-corpus environmental smoke PASS;
3. repetição A/B dos mesmos oito casos reais do G-240 v1;
4. aprovação humana dos quatro critérios observáveis: cobertura, ordem, ausência de invenção e estrutura preservada.

**G-245 permanece BLOCKED e o writer Elementor permanece desautorizado.**
