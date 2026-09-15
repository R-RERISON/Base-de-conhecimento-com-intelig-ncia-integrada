# R-200 — Análise do corpus real

**Execução ambiental:** 2026-09-15 21:33:42 UTC  
**Build:** `0.4.0-profile.1`  
**Evidência:** `evidence/r200-content-profile-20260915T213342Z.json`

## 1. Veredito do gate

**R-200: PASS.**

Condições de segurança comprovadas:

- `editorial_fingerprint_equal=true`;
- `changed_posts_during_run=0`;
- corpus `622 -> 622`;
- nenhum shortcode/widget/bloco dinâmico foi executado;
- nenhum conteúdo editorial, título, URL ou ID foi exportado;
- nenhum progresso/resultado foi persistido.

A evidência é suficiente para decidir source precedence, adapters, política de shortcode, fallback e budgets iniciais.

## 2. Composição do corpus

Total: **622 posts**.

| Source kind estatístico | Qtde | % |
|---|---:|---:|
| `legacy_html` | 496 | 79,74% |
| `elementor` | 74 | 11,90% |
| `plain_text` | 31 | 4,98% |
| `shortcode_plain` | 10 | 1,61% |
| `mixed_elementor_blocks` | 6 | 0,96% |
| `gutenberg` | 3 | 0,48% |
| `empty` | 2 | 0,32% |

Flags não exclusivas:

- Elementor presente: 80;
- Gutenberg presente: 9;
- HTML presente: 579;
- shortcode-like presente: 61;
- plain text presente: 620.

### Conclusão

A implementação não pode ser Elementor-first. **Legacy HTML é o caminho dominante do corpus atual** e deve receber adapter de primeira classe e testes equivalentes aos demais formatos.

## 3. Elementor

- `_elementor_data` presente: 80;
- JSON válido: 39 (**48,75%**);
- JSON inválido: 41 (**51,25%**);
- tipo de meta inesperado: 0;
- posts com JSON válido sem campos semânticos conhecidos: 0;
- widgets detectados: `text-editor` 39, `shortcode` 1;
- campos semânticos: `editor` 39, `shortcode` 1.

### Decisão

- JSON válido pode usar traversal estrutural controlado sem renderização.
- JSON inválido é condição normal do corpus, não exceção rara.
- `_elementor_data` inválido nunca deve derrubar a extração completa.
- `post_content` deve ser fallback estrutural preferencial para Elementor inválido antes de qualquer renderização completa.
- `content_width` não é campo textual e não entra no allowlist semântico.

## 4. Gutenberg

Somente 9 posts contêm blocos. Blocos observados:

- `core/freeform`: 46;
- `core/heading`: 17;
- `core/paragraph`: 14;
- `core/list`: 8;
- `core/table`: 3.

Não houve imagem nem code block Gutenberg na amostra agregada. O adapter deve, porém, ser extensível e tratar bloco desconhecido/dinâmico sem renderizar callback por padrão.

`core/freeform` deve ser delegado ao parser HTML legado, preservando boundaries.

## 5. Shortcodes e falsos positivos

O profiler encontrou 108 ocorrências por detecção textual. Tags mais frequentes:

- `table`: 75;
- `n2`: 8;
- `wpt`: 4;
- `caption`: 3;
- `aaaammdd`: 2;
- `dbc_table`: 2;
- `faq_wd`: 2;
- `bdc_resumo_executivo`: 2.

Também surgiram entradas como `seu`, `tipo`, `banco` e caminhos `hkey_*`, indicando que **colchetes técnicos podem ser confundidos com shortcodes**.

### Decisão

- não usar regex genérica sobre qualquer `[tag]` como verdade semântica;
- reconhecer como shortcode somente sintaxe compatível com uma tag WordPress registrada e/ou allowlisted pelo contrato;
- reconhecimento não autoriza execução;
- callback de shortcode nunca será chamado como caminho padrão;
- shortcodes sem adapter dedicado preservam conteúdo textual interno quando seguro e geram warning/placeholder estruturado;
- `bdc_resumo_executivo` não deve materializar Summary dentro do corpo editorial do Knowledge Document, pois Summary possui owner próprio.

## 6. Estrutura semântica predominante

No `post_content` bruto:

- headings: 1.020;
- listas: 3.700;
- tabelas: 513;
- imagens: 4.595;
- links: 10.208;
- code/pre: 83;
- shortcode-like: 84.

### Decisão

O normalizador deve preservar boundaries de heading, parágrafo, item de lista, linha/célula de tabela e code/pre. Imagens precisam preservar ao menos presença e texto alternativo quando disponível; links devem preservar texto âncora sem depender do destino para formar o corpo semântico.

## 7. Budgets iniciais

### `post_content`

- p50: 3.929 B;
- p95: 28.662 B;
- máximo observado: 156.636 B.

### `_elementor_data`

- p50: 0 B;
- p95: 15.419 B;
- máximo observado: 110.029 B.

### Profiler

- 622 posts em 1.253 ms;
- pico de memória: 29.360.128 B (~28 MiB).

### Budget de implementação v1

- soft warning por fonte acima de **256 KiB**;
- hard safety limit por fonte em **1 MiB**;
- sem truncamento silencioso;
- ao exceder hard limit: falhar somente aquela estratégia/fonte, emitir warning e tentar fallback estrutural permitido;
- nenhuma evidência atual justifica tabela/cache durável.

Esses limites não são metas de performance; são guardrails de segurança derivados do corpus observado e devem ser reavaliados se o corpus crescer materialmente.

## 8. Fallback renderizado

O profiler não encontrou JSON Elementor válido sem campos semânticos conhecidos. Portanto **não há evidência atual de que renderização completa seja necessária para os 39 casos Elementor válidos**.

Para v1:

1. structural extraction;
2. `post_content` como fallback estrutural quando aplicável;
3. renderização completa permanece **desabilitada por padrão**;
4. quando structural + `post_content` não produzirem conteúdo suficiente, emitir `render_fallback_candidate` e preservar o caso para G-240;
5. habilitação de renderização futura exige evidência explícita, isolamento de erro e budget próprio medido em ambiente real.

## 9. Amostragem obrigatória para G-240

Selecionar pelo menos:

- Elementor válido `text-editor`;
- Elementor válido com widget `shortcode`;
- Elementor com `_elementor_data` inválido;
- `mixed_elementor_blocks`;
- Gutenberg com `core/freeform`;
- Gutenberg heading/paragraph/list/table;
- legacy HTML típico;
- legacy HTML próximo/acima do p95;
- documento com tabela;
- documento com code/pre;
- shortcode real registrado (`table`/`dbc_table` se confirmado no ambiente);
- caso de falso positivo de colchetes técnicos;
- plain text;
- empty.

## 10. Consequência para a SPEC

R-200 está concluído. A etapa ativa passa a ser **R-210 — Extraction Contract**. Nenhum runtime permanente deve ser implementado antes de o contrato v1 estar congelado.
