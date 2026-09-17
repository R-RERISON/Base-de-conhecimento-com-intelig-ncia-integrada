# G-240 v2 — Hierarchy Fidelity Gap Analysis

Data: 2026-09-16

## Evidência

Arquivo: `evidence/g240-v2-acceptance-20260916T153610Z.json`.

Resultado do aceite humano A/B no build `0.4.0-acceptance.11` / KD `2.0.1`:

- 8/8 slots revisados;
- 0 stale;
- 0 repeatability failure;
- 0 sample mismatch;
- cobertura completa=true em 8/8;
- ordem preservada=true em 8/8;
- nenhum texto inventado=true em 8/8;
- estrutura preservada=true em 5/8;
- estrutura preservada=false em 3/8: post IDs 1290, 370, 1307;
- `gate_pass=false`.

## Diagnóstico

### 1. `structure_complete` atual valida cardinalidade, não relações

`Semantic_Structure::structure_complete()` compara somente contagens esperadas/atuais de headings, listas, itens, tabelas, linhas e células.

Isso comprova presença/quantidade, mas não comprova:

- arestas pai→filho;
- profundidade máxima;
- ordem entre irmãos;
- pertencimento correto de sublistas ao item pai;
- hierarquia inferível por numeração semântica (`1`, `1.1`, `1.2`, `1.2.1`).

Por isso o full-corpus pôde atingir `structure_incomplete=0` e ainda existir perda hierárquica humana em 3/8 amostras.

### 2. `children` atual depende apenas de estrutura explícita

`Semantic_Structure::render_list_tree()` cria `children` somente quando `parent_item_id` da lista filha corresponde a `item_id` do item pai.

Não existe inferência por prefixo textual/numeração.

Logo, quando um conteúdo histórico apresenta hierarquia visual/semântica, porém HTML plano, o KD não tem regra suficiente para reconstruí-la.

O post 28748 demonstra o caso positivo: a hierarquia ficou bem reconstruída porque a relação estrutural era resolvível. Isso não comprova inferência textual de `1→1.1→1.2`; o código atual não possui tal inferência.

### 3. Falso negativo do gate para `review_required`

`Real_Content_Acceptance_V2` considera `system_ready=true` somente para `candidate_ready|not_applicable`.

Assim, slots humanamente aprovados mas corretamente classificados como `review_required` falham automaticamente. Isso ocorreu em 36431, 1289 e 28748.

Esse comportamento conflita com o contrato de readiness: `review_required` representa limitação explícita/auditável, não necessariamente perda estrutural.

## Determinação

G-240 v2 permanece **FAIL CONTROLADO — HIERARCHY FIDELITY**.

Não autorizar:

- merge do PR #3;
- G-245;
- writer/migration Elementor.

## Evolução proposta — Knowledge Document `2.1.0`

### A. Relationship Fidelity

Adicionar expectativa e validação independentes para relações, não só contagens:

- `list_parent_edges`;
- `list_max_depth`;
- `list_sibling_order`;
- `heading_parent_edges`/heading path transitions quando aplicável;
- assinatura determinística de árvore por lista.

O full-corpus só poderá ser estruturalmente PASS quando cardinalidade **e relações explícitas** reconciliem.

### B. Conservative Numbered Hierarchy Resolver

Adicionar resolver determinístico após extração, antes da projeção final, somente para sinal forte e não ambíguo.

Primeira gramática suportada, baseada na evidência observada:

- `1`;
- `1.1`;
- `1.2`;
- `1.2.1`.

Regras mínimas:

1. token numérico precisa estar no início do item/parágrafo/heading, seguido por separador/whitespace;
2. parent prefix precisa existir anteriormente no mesmo contexto semântico;
3. não cruzar heading_path, tabela, source boundary ou lista incompatível;
4. sequência precisa ter coerência mínima (não inferir por um token isolado como versão/IP);
5. DOM explícito vence inferência textual;
6. conflito DOM×numeração gera `HIERARCHY_NUMBERING_CONFLICT` e `review_required`;
7. sinal forte não resolvido gera `HIERARCHY_AMBIGUOUS` e nunca `candidate_ready`.

### C. Proveniência da hierarquia

Cada relação hierárquica deve indicar origem:

- `explicit_dom`;
- `numbering_inferred`;
- `heading_inferred`;
- `flat`.

E confiança:

- `authoritative` para DOM explícito;
- `deterministic` para numeração resolvida pelas regras;
- `ambiguous` quando houver sinal sem resolução segura.

### D. Ajuste do AI readiness

`candidate_ready` não pode ser emitido quando existe sinal hierárquico forte não reconciliado.

Isso corrige o falso positivo observado no post 370: sistema `candidate_ready/structure_complete=true`, mas humano marcou `structure_loss`.

### E. Ajuste do acceptance gate

No G-240 humano:

- `not_ready` continua bloqueando;
- `review_required` não deve falhar automaticamente se os quatro critérios humanos forem aprovados e as razões estiverem explicitamente registradas;
- o relatório deve distinguir `human_pass`, `system_status` e `gate_pass`, em vez de colapsá-los em um único `pass`.

## Sequência recomendada

1. congelar contrato `knowledge-document-contract-v2.1.0.md`;
2. implementar `Hierarchy_Relationships`/`Numbered_Hierarchy_Resolver` read-only;
3. adicionar testes sintéticos de árvore explícita, numeração resolvível, conflito e ambiguidade;
4. adicionar relationship fidelity ao full-corpus smoke;
5. executar novamente full-corpus;
6. repetir os mesmos oito A/B;
7. fechar G-240 somente se não houver perda hierárquica humana e nenhuma relação forte não resolvida.
