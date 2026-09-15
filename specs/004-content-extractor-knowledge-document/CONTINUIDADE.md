# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- baseline de entrada: `0.3.0-rc.1`.
- SPEC-004: **ATIVA**.
- etapa ativa: **R-200 — Current State do corpus**.
- nenhuma mudança permanente de runtime da SPEC-004 ainda autorizada.

## Decisões já herdadas

- WordPress/Elementor são fonte editorial.
- Content Extractor é read-only.
- Knowledge Document é projeção reconstruível, não segundo CMS.
- sem write em `post_content` ou `_elementor_data`.
- sem alteração de `post_status`/publicação/revisões por leitura.
- sem dependência de Foundry/IA/vetor.
- sem tabela própria nesta etapa.
- renderização completa somente como fallback controlado.

## Prior art analisado

KB2Ops `Content_Extractor` confirma valor de:

- traversal de `_elementor_data`;
- fallback Elementor protegido por `Throwable`;
- allowlist de shortcodes;
- preservação de boundaries;
- facts estruturais.

A implementação histórica não será copiada; Gutenberg dedicado, hashes e Knowledge Document versionado são lacunas desta nova SPEC.

## Próximo build

`0.4.0-profile.1`

Escopo exclusivo:

- profiler de corpus temporário;
- somente `manage_options`;
- somente leitura;
- exporta estatística/nomes estruturais, nunca corpo textual;
- não executa shortcode/widget;
- mede fingerprint editorial antes/depois;
- não persiste progresso;
- deve ser removido antes do RC.

## Critério R-200

O JSON ambiental deve permitir responder objetivamente:

1. quantos posts são Elementor, Gutenberg, legacy/plain e mixed;
2. quais widget types Elementor existem;
3. quais block names Gutenberg existem;
4. quais shortcode tags existem;
5. quais estruturas semânticas predominam;
6. quais documentos exigem provável fallback;
7. quais budgets de tamanho/performance são necessários;
8. se a execução foi realmente read-only.

Somente depois disso o `Extraction Contract v1` pode ser fechado.
