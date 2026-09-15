# SPEC-004 — Content Extractor e Knowledge Document

**Status:** ATIVA — Discovery / Current State  
**Baseline de entrada:** `0.3.0-rc.1`  
**Pré-requisito:** SPEC-003 concluída — PASS.

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

Ordem preferencial de leitura:

1. `WP_Post` e APIs nativas;
2. Gutenberg via estrutura de blocos (`parse_blocks`/contratos de bloco), sem renderização dinâmica como primeira opção;
3. Elementor via `_elementor_data` decodificado e traversal semântico controlado;
4. HTML legado em `post_content`, preservando limites estruturais relevantes;
5. renderização completa apenas como fallback explícito, mensurável e protegido contra falha.

## 5. Referência histórica

O KB2Ops possui um `Content_Extractor` comprovado que:

- mantém o post como fonte da verdade;
- tenta extrair `_elementor_data` de forma determinística;
- usa renderização Elementor somente como fallback;
- restringe expansão de shortcodes a uma allowlist pequena;
- produz texto e fatos estruturais.

Esse comportamento é **referência de requisitos e casos de falha**, não autorização para copiar a classe ou suas escolhas integralmente.

## 6. Knowledge Document — shape mínimo provisório

O contrato final só será congelado após o profiler do corpus real, mas a projeção deve convergir para algo equivalente a:

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

## 7. Hashes

### `source_hash`

Representa a fonte editorial relevante lida pelo extrator. Deve mudar quando o conteúdo que afeta a extração mudar e permanecer estável para leituras idênticas.

### `document_hash`

Representa a serialização canônica do Knowledge Document, excluindo o próprio `document_hash`. Deve ser reproduzível entre execuções idênticas.

Algoritmo inicial candidato: SHA-256 sobre representação canônica UTF-8.

## 8. Descoberta obrigatória antes do runtime

Antes de fechar o contrato do extrator, executar profiler read-only no corpus real para medir, sem exportar conteúdo editorial:

- quantidade de posts por status;
- presença e validade de `_elementor_data`;
- posts com blocos Gutenberg;
- HTML legado/plain/shortcodes;
- combinações/mistos;
- tipos de widgets Elementor;
- nomes de blocos Gutenberg;
- tags de shortcodes;
- tamanhos de `post_content`/`_elementor_data`;
- incidência de tabelas, headings, listas, imagens, links e código;
- casos em que traversal estrutural não encontra texto suficiente e um fallback poderia ser necessário.

O profiler deve comprovar não mutação por snapshot/hash antes/depois.

## 9. Gates

### R-200 — Current State

PASS quando o corpus real tiver sido perfilado e os formatos/fallbacks necessários estiverem documentados sem mutação editorial.

### R-210 — Extraction Contract

PASS quando source selection, normalização, boundaries, shortcodes, fallback e política de erros estiverem congelados.

### G-220 — Extractor determinístico

PASS com testes unitários cobrindo Elementor, Gutenberg, HTML legado, conteúdo vazio/corrompido, shortcodes e repetibilidade.

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
