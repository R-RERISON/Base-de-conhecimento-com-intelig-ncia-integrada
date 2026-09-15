# Plano — SPEC-004 Content Extractor e Knowledge Document

## Estratégia

A implementação seguirá slices pequenas, com gates explícitos. Nenhum consumidor de busca/IA nasce antes do contrato do documento.

## Slice 1 — R-200 / Corpus Profiler

Objetivo: medir o corpus real sem alterar ou exportar conteúdo editorial.

Entregas:

- build temporário `0.4.0-profile.1`;
- profiler admin restrito a `manage_options`;
- JSON sanitizado com distribuição de formatos/estruturas;
- fingerprint editorial antes/depois;
- nenhuma persistência de progresso;
- nenhum serviço externo;
- remoção obrigatória do profiler antes do RC.

## Slice 2 — R-210 / Extraction Contract

Com base no profiler:

- fechar source precedence;
- fechar tabela de widgets Elementor suportados;
- fechar estratégia Gutenberg;
- fechar HTML legado;
- fechar shortcodes allowlisted/placeholder;
- fechar fallback renderizado;
- fechar warnings e códigos de erro;
- fechar normalização de whitespace e boundaries.

## Slice 3 — G-220 / Content Extractor

Implementar runtime permanente side-effect-free, preferencialmente separado em componentes pequenos:

- `Content_Source` / detecção de origem;
- `Content_Extractor` / orquestração;
- adaptador Elementor;
- adaptador Gutenberg;
- adaptador Legacy HTML;
- normalizador textual/estrutural.

Não criar interface abstrata complexa sem necessidade; manter WordPress-first.

## Slice 4 — G-230 / Knowledge Document

Implementar projeção canônica em memória:

- `Knowledge_Document::build(post_id)`;
- schema version;
- sections ordenadas;
- facts estruturais;
- warnings/proveniência;
- `source_hash`;
- `document_hash`;
- canonical JSON determinístico.

Persistência durável fica bloqueada até existir consumidor que justifique custo/complexidade.

## Slice 5 — G-240 / Real Content Acceptance

Escolher conjunto representativo do corpus real, baseado no R-200:

- Elementor típico;
- Elementor complexo;
- Gutenberg se existir;
- HTML legado;
- shortcode/tabela relevante;
- conteúdo vazio/corrompido quando houver.

Para cada caso:

1. snapshot editorial;
2. extração;
3. Knowledge Document;
4. repetição para estabilidade de hash;
5. inspeção humana do conteúdo derivado;
6. snapshot pós-execução;
7. comprovação de zero mutação.

## Slice 6 — G-250 / Lifecycle

- remover profiler/runners temporários;
- package clean `0.4.0-rc.1`;
- source parity;
- lint/syntax;
- deactivate/activate;
- smoke das SPECs 001–003;
- smoke de extração sem write.

## Regra de avanço

Nenhuma slice posterior pode compensar lacuna da anterior. Em particular:

- não implementar extractor definitivo antes de R-200;
- não criar tabela/cache durável antes de demonstrar necessidade;
- não executar shortcode/widget arbitrário para “melhorar cobertura”;
- não usar IA para corrigir falha determinística de extração;
- não alterar fonte editorial para facilitar parsing.
