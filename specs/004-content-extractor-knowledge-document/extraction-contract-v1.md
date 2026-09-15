# Extraction Contract v1 — SPEC-004

**Contract version:** `1.0.0`  
**Gate:** R-210  
**Baseado em:** R-200 PASS / corpus real de 622 posts  
**Natureza:** contrato de leitura e normalização; não autoriza persistência editorial nem execução arbitrária.

## 1. Objetivo

Definir de forma determinística como um `WP_Post` é inspecionado, classificado, extraído e normalizado antes da construção do Knowledge Document.

O contrato prioriza fidelidade semântica e isolamento de falhas sobre aparência renderizada.

## 2. Invariantes

A extração:

- é read-only;
- não chama APIs de escrita editorial;
- não altera `post_content`, `_elementor_data`, status, datas, revisões ou termos;
- não executa shortcode callback por padrão;
- não renderiza dynamic blocks por padrão;
- não renderiza Elementor por padrão;
- não depende de IA, Azure Foundry, embeddings, vetor ou rede externa;
- não persiste documento/progresso/cache durável nesta SPEC;
- nunca trunca conteúdo silenciosamente;
- nunca transforma erro de parsing em conteúdo inventado.

## 3. Modelo de detecção

A detecção não deve reduzir prematuramente um post a uma única origem. O detector produz flags independentes e uma estratégia efetiva.

Flags mínimas:

- `has_elementor_meta`;
- `elementor_json_valid`;
- `has_blocks`;
- `has_html`;
- `has_registered_shortcode_syntax`;
- `has_plain_text`;
- `is_empty`.

`source_kind` do Knowledge Document representa a estratégia efetivamente utilizada e pode assumir inicialmente:

- `elementor`;
- `gutenberg`;
- `legacy_html`;
- `plain_text`;
- `mixed`;
- `empty`.

O rótulo estatístico do profiler não é reutilizado cegamente como decisão de runtime.

## 4. Source selection / precedence

### 4.1 Elementor válido

Se `_elementor_data` existir e decodificar para estrutura JSON válida:

1. executar traversal Elementor allowlisted;
2. preservar ordem estrutural dos elementos;
3. se produzir conteúdo semântico suficiente, usar `elementor`;
4. se o mesmo post também possuir blocos Gutenberg semanticamente relevantes, usar `mixed` e processar ambas as fontes com proveniência explícita;
5. se traversal não produzir conteúdo suficiente, tentar `post_content` estruturalmente;
6. se ainda insuficiente, marcar candidato a fallback renderizado — sem renderizar automaticamente.

### 4.2 Elementor inválido

Se `_elementor_data` existir mas for JSON inválido:

- emitir `ELEMENTOR_JSON_INVALID`;
- não lançar fatal para o documento inteiro;
- não tentar traversal parcial não confiável;
- tentar `post_content` por Gutenberg/legacy/plain conforme estrutura detectada;
- `source_kind` refletirá a fonte que efetivamente produziu o documento, com warning de proveniência;
- renderização Elementor permanece desabilitada por padrão.

### 4.3 Gutenberg

Se `has_blocks=true`:

- usar `parse_blocks()` ou contrato nativo equivalente;
- visitar blocos em ordem;
- blocos estáticos suportados são extraídos estruturalmente;
- blocos dinâmicos não chamam `render_block()` como caminho padrão;
- `core/freeform` é delegado ao adapter Legacy HTML;
- bloco desconhecido preserva conteúdo interno estático quando disponível e gera warning, sem executar callback.

### 4.4 Legacy HTML

Quando `post_content` contiver HTML sem estrutura Gutenberg efetiva, usar Legacy HTML como adapter de primeira classe.

O parser deve preservar boundaries e texto visível sem depender do tema/front-end.

### 4.5 Plain text

Quando não houver markup estrutural relevante, preservar texto em ordem, normalizando somente whitespace conforme este contrato.

### 4.6 Empty

Se nenhuma estratégia produzir texto ou estrutura semântica:

- `source_kind=empty`;
- `sections=[]`;
- emitir `SOURCE_EMPTY` quando a fonte não for editorialmente vazia de forma inequívoca;
- não criar texto sintético.

## 5. Elementor traversal v1

O traversal deve ser orientado por widget/field conhecido, não por busca genérica recursiva de qualquer chave textual.

### Widgets confirmados pelo corpus

- `text-editor` → campo semântico `editor`;
- `shortcode` → campo `shortcode`, tratado pela política de shortcode e nunca executado diretamente.

### Regras

- containers/sections/columns podem ser percorridos apenas para manter ordem;
- IDs, layout, CSS, `content_width` e configurações visuais não entram como texto;
- unknown widget não é automaticamente interpretado por nomes de chaves arbitrários;
- unknown widget com conteúdo não extraído gera `ELEMENTOR_WIDGET_UNSUPPORTED:<widget_type>`;
- traversal nunca chama método de renderização do widget.

O allowlist pode crescer somente com fixture/teste e evidência de corpus.

## 6. Gutenberg contract v1

Blocos confirmados e comportamento:

| Bloco | Estratégia |
|---|---|
| `core/freeform` | Legacy HTML adapter |
| `core/heading` | seção/heading preservado |
| `core/paragraph` | parágrafo preservado |
| `core/list` | itens preservados individualmente e em ordem |
| `core/table` | linhas/células preservadas estruturalmente |

Para qualquer outro bloco:

- se houver `innerBlocks`, percorrer filhos;
- se houver `innerHTML`/`innerContent` estático, processar sem executar callback;
- se depender de renderização dinâmica, emitir `GUTENBERG_DYNAMIC_NOT_RENDERED:<block_name>`.

## 7. Legacy HTML contract v1

### Elementos com boundary obrigatório

- `h1`–`h6`;
- `p`;
- `br`;
- `li`;
- `tr`/`th`/`td`;
- `pre`/`code`;
- `blockquote`;
- containers apenas quando necessários para evitar concatenação indevida.

### Elementos excluídos do texto

- `script`;
- `style`;
- `noscript`;
- markup puramente visual/controle sem texto útil.

### Links

- preservar texto âncora na posição original;
- URL não é injetada automaticamente no corpo textual;
- contagem/proveniência estrutural pode registrar link separadamente.

### Imagens

- imagem não gera texto fictício;
- `alt` não vazio pode ser preservado como metadado/fragmento semântico de imagem;
- `src` não compõe o corpo textual.

### Entidades e encoding

- entrada e saída canônica em UTF-8;
- entidades HTML são decodificadas uma vez;
- não normalizar termos técnicos além de whitespace.

## 8. Shortcode policy v1

### 8.1 Reconhecimento

Texto entre colchetes não é shortcode por si só.

Uma ocorrência só é tratada como shortcode quando:

1. a sintaxe é compatível com parser de shortcode do WordPress; e
2. a tag está registrada no runtime e/ou consta de allowlist explícita do extrator.

A detecção não executa callback.

### 8.2 Execução

`do_shortcode()` é proibido como caminho genérico.

Adapters especializados podem existir somente quando:

- forem determinísticos;
- fizerem leitura controlada;
- não chamarem callback arbitrário do shortcode;
- tiverem fixture/teste;
- estiverem explicitamente allowlisted.

### 8.3 Conteúdo interno

Para shortcode de container sem adapter:

- preservar texto interno quando puder ser separado com segurança da marcação;
- gerar `SHORTCODE_NOT_EXPANDED:<tag>`.

Para shortcode self-closing sem conteúdo interno:

- gerar placeholder estrutural não textual ou warning;
- não inventar o resultado renderizado.

### 8.4 Tags observadas

`table`, `n2`, `wpt`, `caption`, `aaaammdd`, `dbc_table`, `faq_wd`, `bdc_resumo_executivo` são candidatas a validação/fixture, não autorização automática de execução.

Entradas `hkey_*`, `seu`, `tipo`, `banco` e equivalentes devem permanecer texto normal salvo se realmente registradas como shortcode.

`bdc_resumo_executivo` não materializa o Summary no corpo editorial do Knowledge Document: Summary continua sob owner próprio.

## 9. Normalização canônica

A normalização ocorre depois da extração estrutural.

Regras:

1. converter CRLF/CR para LF;
2. remover NUL e controles incompatíveis, preservando `\n` e tab quando semanticamente útil;
3. normalizar whitespace horizontal repetido fora de `pre/code` para um espaço;
4. remover espaços no início/fim de fragmentos;
5. preservar boundaries como fragmentos/seções, não por concatenação cega;
6. preservar conteúdo de `pre/code` sem colapsar whitespace interno;
7. eliminar fragmentos vazios após normalização;
8. não aplicar stemming, tradução, correção ortográfica, resumo ou reescrita;
9. não reordenar fragmentos.

A serialização posterior do Knowledge Document deve ser determinística, mas sua canonicalização final pertence ao G-230.

## 10. Suficiência semântica

Não será usada heurística baseada apenas em número arbitrário de caracteres para declarar sucesso.

Uma estratégia é suficiente quando:

- produz ao menos um fragmento textual não vazio **ou** estrutura semanticamente relevante aceita pelo contrato; e
- não termina exclusivamente em placeholders/warnings.

Casos ambíguos seguem para fallback estrutural permitido e, se ainda insuficientes, são marcados para aceitação real.

## 11. Fallback renderizado

### Estado v1

**Desabilitado por padrão.**

Motivo: no corpus perfilado, todo Elementor com JSON válido apresentou campo semântico conhecido. Não há evidência de necessidade de renderização completa para o caminho válido atual.

### Elegibilidade

Um post pode receber `RENDER_FALLBACK_CANDIDATE` somente quando:

1. fonte estrutural esperada existe;
2. parsing/traversal seguro foi tentado;
3. `post_content` aplicável também foi tentado;
4. resultado permanece semanticamente insuficiente.

### Ativação futura

Exige decisão explícita baseada em G-240, medição de latência/memória e isolamento `Throwable`. Não pode implicitamente autorizar shortcodes/widgets arbitrários.

## 12. Budgets

Derivados do R-200:

- soft limit por fonte: **256 KiB**;
- hard safety limit por fonte: **1 MiB**;
- nenhuma fonte observada atualmente excede o soft limit;
- acima de 256 KiB: processar e emitir `SOURCE_OVERSIZE_SOFT`;
- acima de 1 MiB: não processar aquela representação integral; emitir `SOURCE_OVERSIZE_HARD` e tentar outra estratégia permitida;
- truncamento silencioso é proibido.

Não criar tabela, object cache ou cache persistente por causa desses budgets.

## 13. Warnings / integrity codes v1

Códigos mínimos:

- `SOURCE_EMPTY`;
- `SOURCE_OVERSIZE_SOFT`;
- `SOURCE_OVERSIZE_HARD`;
- `ELEMENTOR_JSON_INVALID`;
- `ELEMENTOR_WIDGET_UNSUPPORTED:<type>`;
- `ELEMENTOR_SEMANTIC_EMPTY`;
- `GUTENBERG_BLOCK_UNSUPPORTED:<name>`;
- `GUTENBERG_DYNAMIC_NOT_RENDERED:<name>`;
- `SHORTCODE_NOT_EXPANDED:<tag>`;
- `HTML_PARSE_RECOVERED`;
- `RENDER_FALLBACK_CANDIDATE`.

Warnings:

- são ordenados deterministicamente pela ordem de ocorrência + código;
- não contêm corpo editorial integral;
- não causam write;
- não são usados para esconder perda de conteúdo.

## 14. Error isolation

- erro em um adapter não pode derrubar o request inteiro se existir fallback seguro;
- parsing de conteúdo não confiável deve ser encapsulado com tratamento de `Throwable` onde APIs/plugins de terceiros puderem lançar;
- erro inesperado produz warning estruturado e permite estratégia seguinte quando autorizada;
- nenhum erro é convertido em sucesso silencioso.

## 15. Proveniência mínima para o Knowledge Document

O extractor deve entregar ao builder, no mínimo:

- `source_kind` efetivo;
- estratégia(s) usadas em ordem;
- `fallback_used` boolean;
- warnings;
- fragments/sections em ordem;
- facts estruturais;
- material canônico necessário ao cálculo posterior de `source_hash`.

O extractor não precisa conhecer persistência, índice, embedding ou consumidor de busca.

## 16. Critérios de teste derivados

G-220 deve obrigatoriamente testar:

- Elementor válido `text-editor`;
- Elementor `shortcode` sem execução;
- Elementor JSON inválido com fallback para `post_content`;
- mixed Elementor + blocks;
- Gutenberg `freeform`, heading, paragraph, list e table;
- bloco desconhecido/dinâmico sem renderização;
- legacy HTML com heading/list/table/link/image/code;
- plain text;
- empty;
- shortcode real registrado sem execução;
- colchetes técnicos que não são shortcode;
- source acima do soft limit;
- source acima do hard limit;
- repetibilidade byte-a-byte da saída intermediária normalizada;
- zero write editorial.

## 17. Decisões explicitamente adiadas

Não fazem parte do R-210:

- schema final do Knowledge Document (`G-230`);
- algoritmo final de canonical JSON (`G-230`);
- storage durável;
- chunking;
- busca lexical;
- embeddings/vetores;
- renderização completa habilitada;
- expansão genérica de shortcode;
- IA para reparar conteúdo.

## 18. Gate R-210

R-210 passa quando este contrato estiver versionado no repositório, tasks correspondentes estiverem fechadas e nenhum ponto essencial de source selection, traversal, Gutenberg, HTML, shortcode, fallback, normalização, budgets ou warnings depender de decisão implícita.

**Estado deste documento:** `FROZEN v1.0.0`.
