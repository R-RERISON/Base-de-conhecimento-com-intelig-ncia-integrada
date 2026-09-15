# SPEC-004 — Content Extractor e Knowledge Document

**Status:** ATIVA — R-200 PASS / R-210 PASS / G-220 IMPLEMENTED-LOCAL-PASS / ENV-SMOKE-PENDING  
**Baseline de entrada:** `0.3.0-rc.1`  
**Package ambiental ativo:** `0.4.0-smoke.1`  
**Pré-requisito:** SPEC-003 concluída — PASS.  
**Contrato ativo de extração:** `extraction-contract-v1.1.md` — `FROZEN v1.1.0`.

## 1. Problema

Busca lexical, busca semântica, IA assistida, chunks e embeddings precisam consumir uma representação semântica confiável do conteúdo editorial. Usar diretamente HTML, `_elementor_data` ou blocos serializados como conhecimento introduz ruído, instabilidade, detalhes de layout e risco de execução de componentes terceiros.

R-200 também mostrou uma base fortemente histórica/legada, enquanto **Elementor é o editor operacional padrão atual da equipe**. Portanto a arquitetura resolve dois problemas diferentes sem misturá-los:

1. extrair conhecimento de qualquer fonte histórica com segurança;
2. permitir futura convergência editorial controlada para Elementor.

## 2. Planos independentes

### Knowledge plane — read-only

- Content Extractor;
- Knowledge Document;
- hashes/proveniência;
- consumidores futuros de busca/IA.

Este plano é editor-independent e nunca altera a fonte editorial.

### Editorial migration plane — explícito

- Projection Plan;
- Elementor Gateway;
- dry-run;
- canário/lotes;
- journal/rollback;
- stale-source guard;
- validação no editor/frontend.

Este plano não é efeito colateral de leitura, instalação, activation ou update.

## 3. Resultado esperado

### Content Extractor

- identifica flags/fontes efetivas;
- extrai Elementor, Gutenberg, HTML legado e plain text;
- preserva boundaries e ordem;
- reconhece shortcodes sem execução arbitrária;
- isola conteúdo inválido/corrompido;
- é determinístico e side-effect-free;
- informa readiness para futura convergência Elementor.

### Knowledge Document

- projeção canônica, determinística e reconstruível;
- schema versionado;
- `source_hash` e `document_hash` determinísticos;
- não é segundo CMS;
- não depende de Elementor como formato interno;
- não exige storage durável sem evidência.

### Normalização Elementor futura

Todo post elegível deverá convergir para:

- Elementor nativo válido; ou
- projection/migration aprovada; ou
- exceção formal `review_required`/`blocked` com motivo rastreável.

## 4. Invariantes constitucionais

A extração/projeção de conhecimento **NUNCA** pode, como efeito colateral:

- escrever em `post_content`;
- escrever em `_elementor_data`;
- alterar `post_status` ou datas editoriais;
- criar revisão;
- publicar/atualizar post;
- executar shortcodes/widgets arbitrários;
- renderizar dynamic blocks por padrão;
- depender de IA/Foundry/vetor/rede externa;
- transformar Knowledge Document em segundo CMS.

Além disso:

- activation/update não executa migration editorial em massa;
- rollback de package e rollback editorial são mecanismos independentes;
- source modificado após snapshot não pode ser sobrescrito por migration atrasada.

## 5. Estratégia de extração WordPress-first

1. `WP_Post` e APIs nativas;
2. flags independentes de Elementor/blocos/HTML/plain/shortcodes;
3. Elementor válido por traversal allowlisted;
4. Gutenberg por `parse_blocks()`/estrutura estática;
5. Legacy HTML com boundaries estruturais;
6. plain text normalizado;
7. fallback fail-soft entre representações permitidas;
8. renderização completa somente com autorização futura baseada em evidência.

`ext-dom` é otimização, não dependência rígida. Sem `DOMDocument`, o Legacy adapter usa fallback estrutural determinístico e emite warning.

## 6. Readiness Elementor

A saída intermediária inclui:

```text
elementor_compatibility
  status
  reasons[]
```

Estados:

- `native`;
- `projectable`;
- `review_required`;
- `blocked`.

Este diagnóstico não grava Elementor e não acopla o Knowledge Document ao editor.

Contrato: `elementor-normalization-contract-v1.md`.

## 7. Knowledge Document — shape provisório

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

O schema final/canonicalização pertence ao G-230.

## 8. R-200 — descoberta ambiental

Profiler read-only executado sobre 622 posts.

Principais achados:

- 79,74% `legacy_html` na classificação estatística;
- Elementor presente em 80: 39 JSON válidos / 41 inválidos;
- Gutenberg presente em 9;
- widgets Elementor confirmados: `text-editor` e `shortcode`;
- blocos: `core/freeform`, `core/heading`, `core/paragraph`, `core/list`, `core/table`;
- shortcode detection textual continha falsos positivos por colchetes técnicos;
- `post_content` max 156.636 B;
- `_elementor_data` max 110.029 B;
- profiler 1.253 ms / peak ~28 MiB;
- fingerprint before/after idêntico, 0 posts alterados, corpus 622 → 622.

Esses dados descrevem o legado; não redefinem o padrão editorial futuro, que permanece Elementor.

## 9. G-220 — Content Extractor implementado

Componentes permanentes:

- `Content_Normalizer`;
- `Shortcode_Inspector`;
- `Legacy_HTML_Adapter`;
- `Content_Source`;
- `Elementor_Adapter`;
- `Gutenberg_Adapter`;
- `Content_Extractor`.

Validação local:

- PHP lint PASS;
- 14/14 unit tests PASS;
- zero-write após cada cenário;
- repetibilidade PASS;
- fallback sem `DOMDocument` PASS.

Evidência: `g220-local-validation.md`.

## 10. Package ambiental `0.4.0-smoke.1`

Finalidade exclusiva: fechar G-220 no WordPress real de homologação.

Flags:

- `BDC_KB_SPEC004_PROFILE_BUILD=false`;
- `BDC_KB_SPEC004_SMOKE_BUILD=true`.

O package contém um runner temporário `Content_Extractor_Smoke`, restrito a `manage_options`, POST+nonce, sem persistência ou exportação de conteúdo editorial.

Validação do ZIP:

- PHP lint antes/depois de extrair: 20/20 PASS;
- JS syntax PASS;
- ZIP integrity PASS;
- source parity dos 9 arquivos novos/alterados: 9/9 PASS;
- 14/14 unit tests executados contra os arquivos extraídos do próprio ZIP.

SHA-256:

`3dad9f5f7c01e7970d314a0d0788756ad694cc9f3b9327a2834ead90b86e4c8f`

Documento: `package-smoke1.md`.

O runner e sua flag são temporários e não podem chegar ao RC/produção.

## 11. Produção e migrations

Contrato: `production-rollout-contract-v1.md`.

Categorias distintas:

1. runtime code upgrade;
2. plugin-state/schema migration;
3. editorial/content migration.

A categoria 3 nunca é automática. Fluxo obrigatório:

`preflight → dry-run → canário → batch → validação → rollback window`

Versões independentes quando aplicável:

- plugin version;
- schema version;
- Knowledge Document schema version;
- Elementor projection schema version;
- migration plan version.

## 12. Elementor writer futuro

Writer editorial não está autorizado no G-220.

Quando implementado, ficará atrás de `Elementor_Gateway` version-gated, preferencialmente usando o lifecycle oficial do Document do Elementor. Escrita direta dispersa em metas internas fica proibida por padrão.

Antes de writer:

- G-220 ambiental fechado;
- G-230 concluído;
- G-240 aprovado;
- production preflight;
- projection plan read-only;
- journal/rollback;
- canário;
- matriz de compatibilidade Elementor.

## 13. Gates

### R-200

**PASS.**

### R-210

**PASS.** `v1.0.0` + amendment `v1.1.0`.

### G-220

**IMPLEMENTED / LOCAL PASS / ENV SMOKE PENDING.**

PASS ambiental exige no smoke:

- fingerprint equal;
- changed posts = 0;
- corpus count unchanged;
- extractor errors = 0;
- throwables = 0.

### G-230

Bloqueado até G-220 ambiental; depois schema/hash/canonicalização.

### G-240

Real Content Acceptance com prova de zero mutação.

### G-245

Elementor Normalization & Production Readiness: preflight, projection plan, gateway, journal/rollback, stale-source, dry-run e canário.

### G-250

Cleanup de runners temporários, clean install/update/deactivate/activate, regressão e package final.

## 14. Fora de escopo agora

- índice lexical/chunks/embeddings;
- MariaDB Vector;
- Azure Foundry/RAG;
- IA para reparar parsing;
- IA para migrar/reescrever artigos;
- migration editorial automática;
- storage durável de Knowledge Document sem consumidor que justifique.

## 15. Definition of Done

A SPEC-004 termina quando existir:

- Content Extractor determinístico e read-only;
- Knowledge Document canônico/versionado/hashado;
- aceitação em conteúdo real;
- zero mutação por leitura comprovada;
- estratégia segura de produção;
- caminho de convergência Elementor governado, reversível e separado do CMS/editor.
