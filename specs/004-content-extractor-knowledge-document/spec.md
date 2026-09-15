# SPEC-004 — Content Extractor e Knowledge Document

**Status:** ATIVA — R-200 PASS / R-210 PASS / G-220 IMPLEMENTED-LOCAL-PASS  
**Baseline de entrada:** `0.3.0-rc.1`  
**Build de desenvolvimento:** `0.4.0-dev.1`  
**Pré-requisito:** SPEC-003 concluída — PASS.  
**Contrato ativo de extração:** `extraction-contract-v1.1.md` — `FROZEN v1.1.0`.

## 1. Problema

Busca lexical, busca semântica, IA assistida, chunks e embeddings precisam consumir uma representação semântica confiável do conteúdo editorial. Usar diretamente HTML, `_elementor_data` ou blocos serializados como conhecimento introduz ruído, instabilidade, detalhes de layout e risco de execução de componentes terceiros.

Ao mesmo tempo, o R-200 mostrou uma base fortemente histórica/legada, enquanto **Elementor é o editor operacional padrão atual da equipe**. Portanto a arquitetura precisa resolver dois problemas diferentes sem misturá-los:

1. extrair conhecimento de qualquer fonte histórica com segurança;
2. permitir futura convergência editorial controlada para Elementor.

## 2. Princípio arquitetural

Existem dois planos independentes.

### 2.1 Knowledge plane — read-only

- Content Extractor;
- Knowledge Document;
- hashes/proveniência;
- consumidores futuros de busca/IA.

Este plano é editor-independent e nunca altera a fonte editorial.

### 2.2 Editorial migration plane — explícito

- projection plan para Elementor;
- migration administrativa;
- canário/lotes;
- journal/rollback;
- validação no editor/frontend.

Este plano não faz parte do efeito colateral de leitura, instalação, activation ou update.

## 3. Resultado esperado

### Content Extractor read-only

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

### Elementor normalization futura

- todos os posts elegíveis convergem para `native`, `projectable/migrated` ou exceção formal;
- writer separado do extractor;
- versão do Elementor explicitamente homologada;
- dry-run, stale-source guard, canário e rollback obrigatórios.

### Production readiness

- homologação é uma cópia da produção, mas diferenças futuras devem ser detectadas por preflight;
- migrations de plugin/schema e migrations editoriais são categorias distintas;
- migration editorial nunca roda automaticamente na ativação/update.

## 4. Invariantes constitucionais

A execução de extração/projeção de conhecimento **NUNCA** pode, como efeito colateral:

- escrever em `post_content`;
- escrever em `_elementor_data`;
- alterar `post_status`;
- alterar data editorial/publicação;
- criar revisão editorial;
- publicar ou atualizar post;
- executar shortcodes/widgets arbitrários;
- renderizar dynamic blocks como caminho padrão;
- transformar Knowledge Document em segundo CMS;
- depender de IA, Foundry, vetor ou serviço externo.

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

`ext-dom` é otimização de parsing, não dependência rígida: há fallback estrutural determinístico quando `DOMDocument` não estiver disponível.

## 6. Readiness Elementor

A saída intermediária do extractor inclui:

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

Este diagnóstico não grava `_elementor_data` e não acopla o Knowledge Document ao editor.

Contrato: `elementor-normalization-contract-v1.md`.

## 7. Knowledge Document — shape mínimo provisório

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

- nenhum HTML/JSON bruto é obrigatório no documento canônico;
- conteúdo derivado mantém ordem determinística;
- campos puramente visuais não poluem a projeção;
- IDs Elementor não são semântica do Knowledge Document;
- Summary, Classificação e Review continuam com owners próprios.

O schema final/canonicalização pertence ao G-230.

## 8. R-200 — descoberta ambiental

Profiler executado sobre 622 posts, sem mutação editorial.

Principais achados:

- 79,74% classificados estatisticamente como `legacy_html`;
- Elementor presente em 80 posts: 39 JSON válidos / 41 inválidos;
- Gutenberg presente em 9 posts;
- widgets confirmados: `text-editor` e `shortcode`;
- blocos confirmados: `core/freeform`, `core/heading`, `core/paragraph`, `core/list`, `core/table`;
- shortcodes textuais continham falsos positivos por colchetes técnicos;
- `post_content` máximo observado: 156.636 B;
- `_elementor_data` máximo: 110.029 B;
- runtime do profiler: 1.253 ms; peak ~28 MiB.

Esses dados descrevem o legado existente, mas não definem a direção editorial futura. A direção operacional permanece Elementor.

## 9. G-220 — implementação

Build `0.4.0-dev.1` contém serviços sem hooks/jobs automáticos:

- `Content_Normalizer`;
- `Shortcode_Inspector`;
- `Legacy_HTML_Adapter`;
- `Content_Source`;
- `Elementor_Adapter`;
- `Gutenberg_Adapter`;
- `Content_Extractor`.

Profiler temporário permanece no source por rastreabilidade, porém `BDC_KB_SPEC004_PROFILE_BUILD=false`; sua UI/runner não são registrados no build dev.

Validação local:

- PHP lint: PASS;
- 14/14 unit tests SPEC-004: PASS;
- zero-write verificado após cada caso de teste;
- repetibilidade: PASS;
- teste local executado também sem `DOMDocument`, validando o fallback de portabilidade.

Evidência: `g220-local-validation.md`.

G-220 ainda exige smoke no WordPress de homologação antes do fechamento ambiental.

## 10. Produção e migrations

Contrato: `production-rollout-contract-v1.md`.

Categorias separadas:

1. runtime code upgrade;
2. plugin-state/schema migration;
3. editorial/content migration.

A categoria 3 nunca é automática. Fluxo obrigatório futuro:

`preflight → dry-run → canário → batch → validação → rollback window`

Versões independentes devem existir quando aplicável:

- plugin version;
- schema version;
- Knowledge Document schema version;
- Elementor projection schema version;
- migration plan version.

## 11. Elementor writer futuro

Writer editorial não está autorizado no G-220.

Quando implementado, ficará atrás de `Elementor_Gateway` version-gated, preferencialmente usando o lifecycle oficial do Document do Elementor. Escrita direta dispersa em metas internas do Elementor fica proibida por padrão.

Antes de writer:

- G-220 estável;
- G-230 concluído;
- G-240 aprovado;
- production preflight;
- projection plan read-only;
- journal/rollback;
- canário;
- matriz de compatibilidade Elementor.

## 12. Gates

### R-200 — Current State

**PASS.**

### R-210 — Extraction Contract

**PASS.** `v1.0.0` + amendment compatível `v1.1.0`.

### G-220 — Extractor determinístico

**IMPLEMENTED / LOCAL PASS.** Falta smoke ambiental do build `0.4.0-dev.1`.

### G-230 — Knowledge Document

Bloqueado até smoke G-220; depois, schema/hash/canonicalização.

### G-240 — Real Content Acceptance

Validação real representativa com prova de zero mutação.

### G-245 — Elementor Normalization & Production Readiness

Planejado. Exige preflight, projection plan, gateway, journal/rollback, stale-source guard, dry-run e canário.

### G-250 — Lifecycle / package

Somente após remover instrumentação temporária, validar clean install/update/deactivate/activate, regressão das SPECs anteriores e package final.

## 13. Fora de escopo neste momento

- índice lexical;
- chunks;
- embeddings/MariaDB Vector;
- Azure Foundry/RAG;
- IA para reparar parsing;
- IA para migrar/reescrever artigos;
- migration editorial automática;
- persistência durável de Knowledge Document sem consumidor que a justifique.

## 14. Definition of Done

A SPEC-004 termina quando existir:

- Content Extractor determinístico e read-only;
- Knowledge Document canônico/versionado/hashado;
- aceitação em conteúdo real;
- zero mutação por leitura comprovada;
- estratégia segura de produção;
- caminho de convergência Elementor explicitamente governado, sem transformar o plugin em CMS e sem migration editorial implícita.
