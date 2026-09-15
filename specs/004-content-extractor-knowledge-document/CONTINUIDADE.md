# Continuidade — SPEC-004 Content Extractor e Knowledge Document

## Estado atual

- SPEC-003: concluída.
- baseline de entrada: `0.3.0-rc.1`.
- SPEC-004: **ATIVA**.
- etapa ativa: **R-200 — Current State do corpus**.
- build ativo: **`0.4.0-profile.1` — profiler read-only pronto para execução ambiental**.
- nenhuma mudança permanente de runtime da SPEC-004 ainda autorizada.

## Decisões herdadas

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

## Build ativo — `0.4.0-profile.1`

Escopo exclusivo:

- profiler de corpus temporário;
- somente `manage_options`;
- POST + nonce;
- somente leitura;
- exporta estatísticas e nomes estruturais, nunca corpo textual;
- não exporta títulos, URLs ou IDs dos artigos;
- não executa shortcode/widget;
- não renderiza Elementor nem dynamic blocks;
- mede fingerprint editorial antes/depois;
- não persiste progresso/resultado;
- deve ser removido antes do RC.

Package: `package-profile1.md`.

SHA-256 do ZIP:

`eeae2f7a5c37dead27bd21f486bea7a64b75d510392a35b742ec6eb338a59bdd`

Validação local:

- PHP lint: **12/12 PASS**;
- JavaScript permanente: syntax PASS;
- source parity: **16/16**;
- ZIP integrity: PASS.

## Próximo passo exato

1. instalar/substituir o plugin por `0.4.0-profile.1`;
2. confirmar a versão na tela de plugins;
3. abrir **Base de Conhecimento → Profiler SPEC-004**;
4. evitar edição concorrente de posts durante a execução;
5. clicar **Executar profiler read-only e baixar JSON**;
6. retornar `bdc-kb-spec004-content-profile-*.json`;
7. exigir `safety.editorial_fingerprint_equal=true`;
8. exigir `safety.changed_posts_during_run=0`;
9. exigir corpus count before = after;
10. analisar distribuição Elementor/Gutenberg/legacy/widgets/shortcodes/tamanhos;
11. somente então fechar R-200 e escrever o `Extraction Contract v1`.

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

**R-200 permanece NOT_RUN até a evidência ambiental.**
