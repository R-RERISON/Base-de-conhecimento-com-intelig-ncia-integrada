# T094 — Editorial Fidelity Contract v1

**Status:** FROZEN — READ-ONLY  
**SPEC:** 004 — G-245 Canonical Block Normalization  
**Predecessor:** T093 PASS AMBIENTAL

## 1. Problema

O Knowledge Document 2.1.0 é uma projeção semântica destinada a busca, IA, análise e validação estrutural. Ele não é uma representação editorial lossless.

O pipeline atual materializa vários elementos como texto visível. Por consequência, usar o KD como única fonte de um futuro serializer Core Blocks pode preservar texto e hierarquia e ainda perder:

- `href` de links;
- `src`/attachment reference de imagens;
- `strong`, `em` e outras marcações inline;
- spans/classes/styles editoriais relevantes;
- detalhes de figures/captions;
- estruturas complexas de tabela;
- informações específicas de widgets Elementor.

## 2. Decisão

Separar explicitamente duas projeções:

### Knowledge projection

`fonte editorial -> Content Extractor -> Knowledge Document`

Objetivo: busca, IA, chunks, embeddings, semântica, hierarquia e validação de cobertura.

### Migration fidelity projection

`fonte editorial original -> Migration Fidelity Source -> Core Block Serializer`

Objetivo: preservar fidelidade editorial suficiente para normalização WordPress Core Blocks.

O KD permanece como guardrail semântico/estrutural e checksum de não-invenção, mas não será a única fonte do futuro writer.

## 3. T094 Inventory

Antes de implementar serializer, executar inventário full-corpus read-only, sem exportar conteúdo editorial, URLs ou IDs de posts.

O inventário deve contar/agregar:

- links com `href`;
- imagens com `src`;
- referências de mídia resolvíveis/não resolvíveis;
- rich inline tags (`strong`, `b`, `em`, `i`, `u`, `mark`, `s`, `del`, `sup`, `sub`, `small`, `code`);
- spans com style/class;
- `br`, figure e figcaption;
- tabelas e células com rowspan/colspan;
- shortcodes;
- dependência Elementor;
- widgets Elementor relevantes: text-editor, image, shortcode, html, heading e outros.

## 4. Fidelity classes

Classificação agregada v1:

- `native_core_blocks`: Gutenberg/Core Blocks existentes — preservar, sem reconversão;
- `not_applicable`: fonte vazia;
- `elementor_source_adapter_required`: Elementor/mixed exige adapter editorial próprio;
- `shortcode_resolution_required`: shortcode precisa decisão/adapter antes da migração;
- `rich_html_source_required`: links, mídia ou rich inline impedem KD-only migration;
- `complex_table_source_required`: spans exigem estratégia específica;
- `kd_structure_sufficient_candidate`: candidato simples onde a estrutura/texto do KD pode ser suficiente, sujeito aos gates posteriores.

A classe é diagnóstica. Nenhuma classe autoriza writer.

## 5. Segurança

T094 obrigatoriamente:

- read-only;
- não chama `serialize_blocks()` para persistência;
- não chama `wp_update_post()`;
- não escreve `post_content`;
- não escreve `_elementor_data`;
- não executa shortcodes;
- não renderiza dynamic blocks;
- não realiza chamadas de rede;
- não exporta texto editorial;
- não exporta URLs;
- não exporta IDs de posts;
- compara fingerprint editorial before/after.

## 6. Gate T094

PASS exige:

- corpus completo;
- errors 0;
- throwables 0;
- corpus unchanged;
- fingerprint editorial before/after idêntico;
- `t094_editorial_fidelity_pass=true`.

O gate PASS significa apenas que o inventário é confiável. Não significa que todos os posts estão prontos para migração.

## 7. Impacto no roadmap

O antigo T094 “serializer imediato” é superseded por este Fidelity Gate.

Após a evidência T094:

1. definir `Migration Fidelity Source v1` para as classes realmente observadas;
2. implementar serializer Core Blocks in-memory;
3. provar `serialize_blocks()`/`parse_blocks()` round-trip;
4. comparar representação migrada com KD e métricas de fidelidade;
5. somente então reusar journal/stale/lock/dry-run/canary para persistência.

## 8. Não objetivos

- corrigir automaticamente hierarquia ambígua;
- resolver shortcodes por execução;
- baixar mídia remota;
- criar attachment automaticamente;
- remover Elementor;
- escrever conteúdo editorial;
- usar IA para preencher dados ausentes.
