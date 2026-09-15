# Current State — SPEC-004 Content Extractor e Knowledge Document

## Baseline de entrada

- plugin: `0.3.0-rc.1`;
- SPEC-001 Summary: concluída;
- SPEC-002 Classificação: concluída;
- SPEC-003 Review & Governança: concluída;
- Workspace/Histórico: baseline ambiental aprovada;
- runtime temporário de homologação: removido.

## O que existe no novo plugin

O runtime atual não possui `Content_Extractor`, `Knowledge_Document`, índice, chunks, embeddings ou tabela derivada de conteúdo. Portanto a SPEC-004 começa sem dívida de implementação própria nesse domínio.

O plugin atual já possui contratos que a SPEC-004 não pode quebrar:

- Summary tem owner próprio;
- Classificação tem owner próprio;
- Review usa event log canônico;
- Histórico é read-only;
- Workspace é a superfície administrativa integrada;
- fonte editorial não pertence ao plugin.

## Fonte editorial autorizada

A arquitetura do projeto define:

- `WP_Post` + Elementor como fonte editorial;
- leitura autorizada de `post_content`;
- leitura autorizada de `_elementor_data`;
- HTML renderizado apenas como fallback controlado;
- escrita em `_elementor_data` proibida.

## Ambiente conhecido

Do inventário anterior do ambiente WordPress:

- WordPress corporativo em produção/homologação;
- Elementor predominante;
- conteúdo misto e histórico;
- presença de posts privados/restritos;
- base suficiente para exigir extração que não dependa de um único editor/formato.

A distribuição exata entre Elementor, Gutenberg, HTML legado, plain text e shortcodes ainda não foi medida para esta SPEC. Essa lacuna impede congelar o contrato definitivo agora.

## Prior art — KB2Ops

Referência: `R-RERISON/KB2Ops-Operational-Knowledge-Engine`, `plugin/kb2ops/includes/class-content-extractor.php`.

Comportamentos úteis observados:

- valida post type;
- mantém caches somente in-request;
- detecta Elementor pela presença de `_elementor_data`;
- percorre JSON Elementor com allowlist de campos textuais;
- tenta fallback renderizado apenas quando necessário;
- captura `Throwable` de renderização;
- expande somente shortcodes de tabela permitidos;
- preserva boundaries antes de remover markup;
- produz contagens estruturais de imagens/tabelas/headings/shortcodes.

Limitações que não devem ser herdadas automaticamente:

- traversal genérico por nome de chave pode misturar semântica de widgets diferentes;
- lista de campos Elementor é fixa e precisa ser confrontada com o corpus real;
- Gutenberg não possui tratamento estrutural dedicado;
- `html()` mistura conceito de extração estrutural com possível renderização;
- não há Knowledge Document versionado/hashado;
- não há `source_hash`/`document_hash`;
- não há contrato explícito de warnings/proveniência por seção;
- fallback renderizado precisa ser mensurado e auditável, não apenas implícito.

## Hipóteses a validar no R-200

1. A maioria dos posts Elementor pode ser extraída sem renderização completa.
2. Há widgets/shortcodes corporativos além da allowlist histórica do KB2Ops.
3. Existe quantidade não nula de conteúdo Gutenberg/blocos ou HTML legado.
4. Alguns posts podem possuir `_elementor_data` junto com `post_content` residual; a regra de precedência precisa ser baseada em evidência.
5. Tabelas e conteúdo em shortcode precisam de política própria para não perder conhecimento operacional.
6. O corpus possui documentos grandes o suficiente para exigir limites de CPU/memória e estratégia incremental futura.

## Risco principal

O maior risco da SPEC-004 não é algoritmo de limpeza de HTML. É produzir uma representação aparentemente limpa que silenciosamente omita conteúdo operacional importante ou execute componentes terceiros durante a extração.

## Próximo passo

Executar um profiler read-only de corpus, sem exportar conteúdo textual, para fechar R-200 antes da implementação permanente.
