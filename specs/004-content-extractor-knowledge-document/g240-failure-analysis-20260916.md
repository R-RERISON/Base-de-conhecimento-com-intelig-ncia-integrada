# G-240 — Failure Analysis: perda estrutural no Knowledge Document v1

**Data:** 2026-09-16  
**Gate:** G-240 Real Content Acceptance  
**Resultado:** **FAIL controlado**  
**Evidência:** `evidence/g240-acceptance-20260916T085721Z.json`

## 1. Resumo

A aceitação humana revisou 8/8 slots representativos. O tooling permaneceu seguro e determinístico, porém nenhum slot passou o gate final.

Resultados objetivos:

- reviewed slots: 8/8;
- passed slots: 0/8;
- stale slots: 0;
- repeatability failures: 0;
- selection mismatches: 0;
- editorial fingerprint before/after: igual;
- changed posts: 0.

A falha não é de lifecycle, write, repetibilidade ou seleção. É de **fidelidade estrutural**.

## 2. Padrão observado

Em todos os 8 casos o revisor marcou:

- `coverage_complete=true`;
- `order_preserved=true`;
- `no_invented_text=true`.

Em 7/8 casos:

- `structure_adequate=false`;
- reason=`structure_loss`.

O caso `elementor_native_typical` teve estrutura considerada adequada, mas o revisor corretamente não marcou `acceptable_for_knowledge_use`, pois esse critério exigia conhecimento especializado sobre RAG/IA e não deveria ser subjetivo.

Conclusão: o extractor v1 preserva texto e ordem na amostra, mas o modelo intermediário/Knowledge Document v1 **achata relações semânticas**.

## 3. Causa raiz no código v1

### 3.1 Listas

`Legacy_HTML_Adapter` transforma cada `<li>` em um fragmento `list_item`, porém não preserva:

- identidade da lista;
- `ul` versus `ol`;
- profundidade;
- item pai;
- relação entre listas aninhadas;
- índice do item dentro da lista.

Além disso, `visible_text()` pode incorporar texto de listas aninhadas no item pai, destruindo a fronteira semântica.

### 3.2 Tabelas

Cada `<tr>` vira `table_row` e suas células são concatenadas com `" | "`.

São perdidos:

- identidade da tabela;
- `th` versus `td`;
- índice da linha;
- índice da célula/coluna;
- `colspan`/`rowspan`;
- caption;
- vínculo célula → linha → tabela.

### 3.3 Hierarquia por headings

O nível do heading é preservado, mas parágrafos/listas/tabelas não carregam `heading_path`. Consumidores futuros teriam que inferir contexto apenas olhando elementos anteriores.

### 3.4 Elementor

O adapter atual trabalha principalmente com `text-editor` e shortcode allowlisted. A estrutura HTML interna do `text-editor` é encaminhada ao parser Legacy; portanto os mesmos problemas de lista/tabela se propagam para Elementor.

Containers/sections/columns do Elementor são layout visual e não devem ser promovidos automaticamente a semântica. O foco deve ser o conteúdo semanticamente significativo dentro dos widgets.

### 3.5 Gutenberg

Blocos conhecidos são reduzidos ao mesmo parser HTML. O nome do bloco/proveniência existe, porém listas/tabelas continuam achatadas pelo modelo v1.

### 3.6 Knowledge Document v1

`sections[]` é uma lista linear de fragments com `kind/text/ordinal/source/meta`.

O schema não possui uma representação canônica para:

- árvore de listas;
- tabela estruturada;
- heading ancestry;
- agrupamento semântico de blocos.

Logo o problema não pode ser resolvido apenas com CSS/UI da ferramenta G-240; o schema precisa evoluir.

## 4. Impacto para RAG/IA

A geração de embeddings continuaria tecnicamente possível com texto plano, mas isso não é critério suficiente de qualidade.

Para conhecimento corporativo, listas, headings e tabelas frequentemente carregam relações que alteram significado. Perder essas relações pode prejudicar:

- chunking por seção;
- recuperação de trechos com contexto;
- associação item → requisito/procedimento;
- interpretação linha/coluna de tabelas;
- grounding de respostas;
- explicabilidade da origem do trecho recuperado.

Portanto o Knowledge Document v1 não será promovido como representação final para IA.

## 5. Decisão

1. Registrar G-240 v1 como **FAIL controlado**.
2. Manter a evidência, sem reescrevê-la ou forçar PASS.
3. Congelar Knowledge Document v1 como histórico de determinismo, porém **superseded para consumo de IA**.
4. Introduzir `Knowledge Document Contract v2.0.0`.
5. Preservar estrutura semântica sem copiar HTML/JSON bruto.
6. Remover `acceptable_for_knowledge_use` do veredito humano.
7. Criar `ai_readiness` calculado pelo sistema; o revisor humano valida fidelidade, não decide sozinho se a estrutura é adequada para embeddings/RAG.
8. Rerodar regressão determinística/zero-write após a mudança de schema.
9. Executar novo G-240 sobre os mesmos slots/corpus antes de G-245.

## 6. Modelo estrutural v2

O v2 deve manter uma visão linear `sections[]` para diagnóstico/compatibilidade, mas adicionar estrutura semântica canônica `blocks[]`:

- heading com nível e `heading_path`;
- paragraph;
- list com `ordered/unordered`, itens e listas filhas;
- table com caption, linhas, células, `header/data`, `rowspan/colspan`;
- code;
- quote;
- image alt quando existir.

`heading_path` deve acompanhar blocos não-heading, permitindo chunking contextual futuro sem depender de heurística posterior.

## 7. AI readiness

Novo campo calculado:

- `candidate_ready`: estrutura objetiva consistente e sem gaps conhecidos;
- `review_required`: conteúdo estruturalmente consistente, mas há warnings semânticos não resolvidos (ex.: shortcode/dynamic block/widget não suportado);
- `not_ready`: perda estrutural objetiva, hard limit, fallback crítico ou ausência inesperada de conteúdo;
- `not_applicable`: fonte editorial realmente vazia.

Esse status não substitui G-240. Ele elimina a necessidade de o operador adivinhar se um documento é “bom para IA”.

## 8. Gate de saída da correção

A correção só pode retornar a G-240 quando:

- testes sintéticos provarem lista aninhada, heading path e tabela estruturada;
- hash/JSON v2 forem determinísticos;
- zero writes continuar comprovado;
- package parity/lint/integrity passar;
- novo acceptance tool exibir `blocks[]` semanticamente, não apenas fragments planos.

G-245 permanece bloqueado até novo G-240 PASS.
