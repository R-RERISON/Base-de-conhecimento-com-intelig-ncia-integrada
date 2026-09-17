# T095 — Migration Fidelity Source v1

**Status:** FROZEN — PASS LOCAL / READ-ONLY  
**SPEC:** 004 — G-245 Rebaseline  
**Target:** WordPress Core Blocks  
**ADR:** `adr/ADR-004-001-wordpress-core-blocks-canonical-editorial-target.md`

## 1. Problema

O Knowledge Document 2.1.0 é canônico para busca/IA/validação semântica, mas não é uma cópia editorial lossless. O inventário T094 comprovou que a maioria do corpus depende de HTML rico, mídia, links, formatação inline, shortcodes ou conteúdo Elementor legado.

T095 cria uma fonte editorial efêmera e lossless para migração sem alterar o KD.

## 2. Princípio

Separar responsabilidades:

- **Knowledge Document** → semântica, busca, IA, hierarquia e validação;
- **Migration Fidelity Source** → preservação byte-oriented do material editorial necessário à migração;
- nenhuma das duas é fonte editorial persistida; a fonte oficial continua o WordPress até um gate de migração autorizado.

## 3. Entrada

- `Content_Source::inspect(post_id)`;
- `Content_Extractor::extract(post_id)` apenas para decidir `source_kind` efetivo;
- nenhuma chamada externa;
- nenhuma execução de shortcode ou dynamic block.

## 4. Saída

`Migration Fidelity Source` v1 contém:

- `schema_version`;
- `post_id`;
- `source_kind`;
- `status`;
- `strategy`;
- `units[]` ordenadas;
- hash SHA-256 de cada payload bruto;
- tamanho em bytes;
- hashes de `post_content` e `_elementor_data`;
- `fidelity_hash` determinístico;
- warnings;
- invariantes de safety.

O conteúdo bruto existe apenas em memória e **não pode ser exportado por runners diagnósticos**.

## 5. Estratégias v1

### Gutenberg existente

`native_core_blocks`

- preservar `post_content` exatamente;
- futuro serializer deve retornar `native_noop`;
- nenhuma reserialização obrigatória para persistência.

### Legacy HTML

`preserve_post_content_lossless`

- uma unidade `post_content_rich_html` com o `post_content` exato;
- links, imagens, spans, tabelas, shortcodes e inline markup permanecem no payload original.

### Plain text

`preserve_post_content_lossless`

- uma unidade `post_content_plain_text` exata;
- nenhuma normalização destrutiva.

### Elementor

`extract_elementor_editorial_units`

Somente widgets já comprovados no corpus T094:

- `text-editor` → `elementor_text_editor_html` com `settings.editor` exato;
- `shortcode` → `elementor_shortcode` com `settings.shortcode` exato.

Widget não suportado gera `MIGRATION_SOURCE_ELEMENTOR_UNSUPPORTED_WIDGET:*` e `review_required`.

### Mixed

`preserve_dual_source_for_review`

- preserva os dois canais em memória;
- sempre `review_required`;
- nunca escolhe automaticamente entre Elementor e `post_content`.

## 6. Estados

- `ready`;
- `review_required`;
- `not_applicable`.

Fonte desconhecida ou Elementor sem unidade editorial segura não pode avançar automaticamente.

## 7. Determinismo

`fidelity_hash` é calculado sobre manifesto sem conteúdo editorial bruto, mas incluindo:

- source kind;
- status/strategy;
- ordem das unidades;
- kind;
- SHA-256 e bytes;
- warnings;
- hashes dos canais-fonte.

Duas leituras da mesma fonte devem produzir o mesmo hash.

## 8. Segurança

Obrigatoriamente:

- `read_only=true`;
- `persists_state=false`;
- `writes_post_content=false`;
- `writes_elementor_data=false`;
- `calls_external_network=false`;
- `executes_shortcodes=false`;
- `renders_dynamic_blocks=false`;
- `depends_on_gutenberg_plugin=false`.

## 9. Fora de escopo

- persistência;
- `wp_update_post`;
- remoção de Elementor;
- renderização de shortcode;
- download/relink de mídia;
- reconstrução visual de layout Elementor;
- IA para completar conteúdo;
- transformação semântica de HTML rico.

## 10. Evidência que motivou T095

T094 / 623 posts:

- 467 `rich_html_source_required`;
- 79 classificados conservadoramente como `elementor_source_adapter_required`;
- 37 `shortcode_resolution_required`;
- 33 `kd_structure_sufficient_candidate`;
- 4 `native_core_blocks`;
- 3 `not_applicable`.

O T095 refina a seleção usando o `source_kind` efetivo do Content Extractor. Meta Elementor residual não deve substituir automaticamente `post_content` quando o extractor determinou `legacy_html`.

## 11. Aceite local

Cobertura local combinada T095/T096: **34/34 assertions PASS + PHP lint PASS**.
