# SPEC-004 — Content Extractor e Knowledge Document

**Status:** ATIVA — R-200 PASS / R-210 PASS / G-220 READY  
**Baseline de entrada:** `0.3.0-rc.1`  
**Pré-requisito:** SPEC-003 concluída — PASS.  
**Contrato ativo:** `extraction-contract-v1.md` — `FROZEN v1.0.0`.

## 1. Problema

Busca lexical, busca semântica, IA assistida, chunks e embeddings precisam consumir uma representação semântica confiável do conteúdo editorial. Usar diretamente HTML, `_elementor_data` ou blocos serializados como conhecimento introduz ruído, instabilidade, detalhes de layout e risco de execução de componentes terceiros.

A fonte editorial continua sendo WordPress/Elementor. Esta SPEC cria somente uma **projeção derivada e reconstruível**.

## 2. Resultado esperado

Entregar dois componentes conceituais:

1. **Content Extractor read-only**
   - identifica a fonte editorial efetiva;
   - extrai conteúdo semântico de Elementor, Gutenberg/blocos e HTML/conteúdo legado;
   - preserva ordem e limites semânticos relevantes;
   - não executa código arbitrário como caminho principal;
   - possui fallback de renderização controlado somente quando justificado.

2. **Knowledge Document**
   - projeção canônica, determinística e reconstruível;
   - schema explicitamente versionado;
   - contém somente dados necessários aos consumidores futuros;
   - possui `source_hash` e `document_hash` determinísticos;
   - não é fonte editorial e não possui writer editorial;
   - não exige tabela própria nesta SPEC sem evidência de necessidade.

## 3. Invariantes constitucionais

A execução de extração/projeção **NUNCA** pode, como efeito colateral:

- escrever em `post_content`;
- escrever em `_elementor_data`;
- alterar `post_status`;
- alterar data editorial/publicação;
- criar revisão editorial;
- publicar ou atualizar o post;
- transformar o Knowledge Document em segundo CMS;
- executar shortcodes/widgets arbitrários por padrão;
- depender de IA, Foundry, vetor ou serviço externo.

O contrato editorial existente continua integralmente válido.

## 4. Estratégia WordPress-first

Ordem conceitual de leitura:

1. `WP_Post` e APIs nativas;
2. detecção por flags independentes de Elementor/blocos/HTML/plain/shortcodes;
3. Gutenberg via estrutura de blocos, sem renderização dinâmica como primeira opção;
4. Elementor válido via `_elementor_data` decodificado e traversal semântico controlado;
5. HTML legado em `post_content`, preservando limites estruturais relevantes;
6. plain text quando não houver markup estrutural;
7. renderização completa apenas como fallback explicitamente autorizado após evidência real.

A precedência e os detalhes definitivos estão congelados em `extraction-contract-v1.md`.

## 5. Referência histórica

O KB2Ops possui um `Content_Extractor` comprovado que:

- mantém o post como fonte da verdade;
- tenta extrair `_elementor_data` de forma determinística;
- usa renderização Elementor somente como fallback;
- restringe expansão de shortcodes a uma allowlist pequena;
- produz texto e fatos estruturais.

Esse comportamento é **referência de requisitos e casos de falha**, não autorização para copiar a classe ou suas escolhas integralmente.

## 6. Knowledge Document — shape mínimo provisório

A projeção deve convergir para algo equivalente a:

```text
schema_version
post_id
source_kind
source_hash
document_hash
title
canonical_url
modified_gmt
sections[]
  kind
  heading
  text
  ordinal
structure
  headings
  lists
  tables
  images
  links
  code_blocks
  shortcodes
extraction
  strategy
  fallback_used
  warnings[]
```

Restrições:

- nenhum HTML/JSON bruto é necessário no documento canônico;
- conteúdo derivado deve manter ordem determinística;
- campos vazios ou puramente visuais não devem poluir a projeção;
- IDs internos de Elementor só entram se provarem valor semântico/operacional;
- Summary, Classificação e Review não são duplicados automaticamente dentro do corpo editorial do Knowledge Document; integrações futuras devem consumir seus próprios owners.

O schema final e canonicalização serão congelados em G-230.

## 7. Hashes

### `source_hash`

Representa a fonte editorial relevante lida pelo extrator. Deve mudar quando o conteúdo que afeta a extração mudar e permanecer estável para leituras idênticas.

### `document_hash`

Representa a serialização canônica do Knowledge Document, excluindo o próprio `document_hash`. Deve ser reproduzível entre execuções idênticas.

Algoritmo inicial candidato: SHA-256 sobre representação canônica UTF-8.

## 8. R-200 — descoberta ambiental concluída

Profiler read-only executado em `2026-09-15T21:33:42Z` sobre 622 posts com:

- fingerprint editorial before/after idêntico;
- zero posts alterados;
- corpus count 622 antes/depois;
- sem execução de shortcode, Elementor ou dynamic blocks;
- sem persistência de resultados.

Evidências:

- `evidence/r200-content-profile-20260915T213342Z.json`;
- `r200-corpus-analysis.md`.

Principais achados:

- 79,74% `legacy_html` no source kind estatístico;
- Elementor presente em 80 posts, com 39 JSON válidos e 41 inválidos;
- Gutenberg presente em 9 posts;
- widgets Elementor confirmados: `text-editor` e `shortcode`;
- blocos confirmados: `core/freeform`, `core/heading`, `core/paragraph`, `core/list`, `core/table`;
- shortcode detection textual contém falsos positivos por colchetes técnicos;
- `post_content` máximo observado 156.636 B;
- `_elementor_data` máximo observado 110.029 B;
- não há evidência atual que justifique cache/storage durável ou renderização completa como caminho normal.

## 9. Gates

### R-200 — Current State

**PASS.** Corpus real perfilado e formatos/fallbacks necessários documentados sem mutação editorial.

### R-210 — Extraction Contract

**PASS.** Source selection, normalização, boundaries, shortcodes, fallback, budgets e política de erros congelados em `extraction-contract-v1.md` v1.0.0.

### G-220 — Extractor determinístico

**READY.** PASS com testes unitários cobrindo Elementor, Gutenberg, HTML legado, conteúdo vazio/corrompido, shortcodes, guardrails e repetibilidade.

### G-230 — Knowledge Document

PASS quando o mesmo input produzir exatamente o mesmo documento/hash e alterações semânticas relevantes alterarem o hash esperado.

### G-240 — Real Content Acceptance

PASS em posts reais representativos quando:

`post real → extração → Knowledge Document → hash → validação humana/estrutural`

com confirmação antes/depois de que `post_content`, `_elementor_data`, `post_status` e estado editorial relevante permaneceram inalterados.

### G-250 — Lifecycle / package

PASS somente após remover instrumentação temporária, gerar package limpo e validar deactivate/activate sem regressão das SPECs 001–003.

## 10. Fora de escopo

Esta SPEC não implementa:

- índice lexical;
- tabela de chunks;
- embeddings;
- MariaDB Vector;
- Azure Foundry;
- RAG;
- ranking de busca;
- telemetria de busca;
- geração ou correção editorial por IA;
- persistência durável de projeções sem justificativa específica.

Esses consumidores passam a ter um contrato confiável para usar nas SPECs posteriores.

## 11. Definition of Done

A SPEC-004 termina somente quando existir um extrator WordPress-first determinístico, um Knowledge Document versionado/hashado, aceitação em conteúdo real e evidência objetiva de **zero mutação editorial**.
