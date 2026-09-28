# R-260D2 — Structural Runtime Promotion Contract v1

**Status:** FROZEN PARA IMPLEMENTAÇÃO / HOMOLOGAÇÃO  
**Data:** 2026-09-28  
**Owner:** SPEC-004 + SPEC-005 joint review  
**Arquitetura:** Option C — Dedicated Structural Projection shared by consumers

## Evidência de autorização

RC10 / R-260D1 confirmou:

- corpus: 623/623;
- extractor errors: 0;
- strong gaps sem heading context: 1.294;
- confidence deterministic: 548;
- confidence ambiguous: 746;
- deterministic candidates: 548;
- TOC-like: 77;
- body-bearing: 289;
- uncertain: 182;
- R-260B: 211 promotable_shadow + 78 duplicate_candidate_ambiguous = 289 body-bearing;
- capacity para todos os 289 body-bearing: nenhum post excede 64 Sections;
- R-260D1 sobre os 211 unique-label promotable:
  - title_unique: 131;
  - context_unique: 66;
  - context_ambiguous: 0;
  - context_insufficient: 2;
  - context_not_matched: 12;
  - title_not_rendered: 0;
  - effective unique: 197/211;
  - unresolved: 14.

R-260D1 = PASS / DISCOVERY CLOSED.

## Decisão

A recuperação estrutural não pertence ao Content Extractor e não altera Knowledge Document.

Será criada uma **projeção estrutural derivada, versionada e read-only**, reutilizável por consumidores.

Search consome essa projeção, mas não é owner da semântica recuperada.

## Fonte elegível

Um candidate estrutural só existe quando:

- fragment kind = paragraph;
- hierarchy_source = numbering_inferred;
- hierarchy_confidence = deterministic;
- depth > 1;
- não possui heading contextual;
- pertence ao conjunto diagnosticado por R-260A.

## Disposição canônica

Cada deterministic candidate recebe exatamente uma disposição:

1. `toc_suppressed`
   - TOC signal;
   - não vira estrutura runtime.

2. `body_projected`
   - body signal comprovado;
   - vira nó da Structural Projection;
   - inclui labels únicos e duplicados.

3. `existing_heading_redundant`
   - label já representado por heading real;
   - não duplica Section.

4. `uncertain`
   - sem TOC signal e sem body signal suficiente;
   - não é promovido;
   - permanece fail-closed.

Duplicidade de label **não elimina retrieval**. Ela afeta apenas navegabilidade/anchor.

## Identidade estrutural

Para um `body_projected`, a identidade não depende do ordinal absoluto.

Identity base:

`post_id | numbered_paragraph | depth | hierarchy_token | title_norm | occurrence`

Onde:
- `occurrence` é a ocorrência determinística do mesmo identity base em ordem editorial;
- `section_key = SHA-256(identity base)`.

Inserção de parágrafo não relacionado acima do candidate não pode, por si só, mudar `section_key`.

## Corpo estrutural

O corpo do nó começa após o pseudo-heading e termina antes da próxima boundary:

- heading real; ou
- node `numbering_inferred` com `depth > 1`.

Kinds elegíveis:
- paragraph;
- list_item;
- table_caption;
- table_row;
- quote;
- code;
- image.

Texto normalizado continua bounded a 4.000 caracteres no Search Section.

## Deep-link

A Structural Projection pode carregar resolução de target, mas não grava conteúdo editorial.

Estados de target:
- `title_unique`;
- `context_unique`;
- `context_ambiguous`;
- `context_insufficient`;
- `context_not_matched`;
- `title_not_rendered`.

Somente `title_unique|context_unique` autorizam `anchor_state=generated`.

Todo outro estado vira:
- `anchor_state=unresolved`;
- `anchor_id=''`;
- Section continua elegível ao retrieval.

## Duplicados body-bearing

Os 78 `duplicate_candidate_ambiguous` de R-260B passam a ser Sections runtime porque:
- possuem body signal;
- o contrato de Section já suporta identities distintas;
- duplicidade de título não é motivo para apagar uma seção relevante.

Na primeira promoção:
- retrieval = habilitado;
- anchor = fail-closed;
- resolver v2 pode provar target e gerar anchor somente se houver unicidade contextual real.

## Capacity

Search combina headings reais + body_projected e ordena por source_ordinal.

Bound:
- máximo 64 Sections/post.

Qualquer overflow ambiental:
- não autoriza truncamento silencioso como PASS;
- T590-15 falha;
- evidência deve registrar post/count.

RC10 mostrou capacity máxima <= 55 para headings + todos os body-bearing no corpus atual.

## Versionamento

- Structural Projection: `r260-structural-projection-v1.0.0`;
- Section Projection: `search-section-projection-v1.1.0`;
- Search Document permanece `search-document-v1.1.0`;
- Search Projection schema permanece `1.1.0`.

Mudança de Section Projection deve levar lifecycle a degraded/stale até rebuild explícito.

## Guardrails

D2 não pode:
- alterar Content Extractor output;
- alterar KD 2.1.0;
- escrever post_content;
- escrever _elementor_data;
- escrever metadata editorial;
- mudar lexical-ranker-v1.0.0;
- criar segunda tabela Search;
- promover confidence=ambiguous;
- promover TOC;
- promover uncertain;
- usar first/nth occurrence;
- usar substring/fuzzy match para anchor;
- chamar rede externa.

## Aceite ambiental D2

Obrigatório provar:
- 623/623;
- extractor errors=0;
- deterministic candidate partition íntegra;
- body_projected esperado = 289 no corpus atual, salvo mudança editorial comprovada;
- projected runtime count = body-bearing count;
- TOC/uncertain/ambiguous-confidence promotion count = 0;
- overflow=0;
- stable identity/determinism PASS;
- anchor generated/unresolved partition fechada;
- todo generated anchor probe materializa;
- visible text idêntico;
- parent Golden permanece PASS;
- T590-14/16/17/18 permanecem PASS;
- fingerprint editorial idêntico.
