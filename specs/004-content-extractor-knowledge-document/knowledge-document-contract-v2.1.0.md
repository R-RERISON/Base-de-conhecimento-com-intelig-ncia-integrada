# Knowledge Document 2.1.0 — Hierarchy Fidelity Contract

Status: **FROZEN FOR G-240 VALIDATION** — 2026-09-16.

Derivado do FAIL humano controlado do G-240 v2/KD 2.0.1. Este contrato continua estritamente **read-only** e não autoriza writer, migration ou normalização Elementor.

## 1. Objetivo

Evoluir o KD 2.0.1 de fidelidade estrutural por cardinalidade para fidelidade de **relações hierárquicas**, preservando determinismo, ordem editorial, texto original e auditabilidade.

## 2. Princípios invariantes

1. DOM explícito é autoridade primária.
2. Contagens estruturais permanecem obrigatórias, porém não suficientes.
3. Relações pai→filho, profundidade e ordem entre irmãos fazem parte do contrato.
4. Inferência por numeração só é permitida com sinal forte, coerente, determinístico e contextual.
5. Token numérico isolado nunca prova hierarquia.
6. A inferência não pode atravessar `heading_path`, source boundary, tabela ou estrutura explícita incompatível.
7. Toda relação hierárquica deve declarar proveniência e confiança.
8. Conflitos não são silenciosamente resolvidos.
9. Nenhuma regra deste contrato pode alterar conteúdo editorial ou produzir Elementor data.

## 3. Relationship Fidelity

O KD 2.1.0 expõe `hierarchy.relationship_fidelity` com `expected`, `actual`, `complete` e `reasons`.

### 3.1 Métricas mínimas de listas

- `list_parent_edges`;
- `list_root_count`;
- `list_max_depth`;
- `list_sibling_order_signature`;
- `list_tree_signature`.

### 3.2 Métricas mínimas de headings

- `heading_path_transitions`;
- `heading_parent_edges`;
- `heading_tree_signature`.

A expectativa deve ser derivada de fonte estrutural independente quando disponível. A projeção atual é derivada dos fragments materializados. A comparação não inclui conteúdo editorial nas evidências; apenas contagens, topologia e hashes estruturais.

### 3.3 Mismatches bloqueantes

Perda comprovada de relação explícita deve produzir reason/warning com prefixo específico, incluindo:

- `HIERARCHY_EDGE_MISMATCH:*`;
- `HIERARCHY_DEPTH_MISMATCH:*`;
- `HIERARCHY_ORDER_MISMATCH:*`.

Qualquer mismatch explícito torna `relationship_complete=false`, `structure_complete=false` e `ai_readiness.status=not_ready`.

## 4. Numbered Hierarchy Resolver

Primeira gramática autorizada:

- `1`;
- `1.1`;
- `1.2`;
- `1.2.1`.

### 4.1 Condições de inferência

- token no início do texto semanticamente materializado;
- separador inequívoco entre token e conteúdo;
- no máximo três níveis nesta versão;
- parent prefix precisa ter aparecido anteriormente no mesmo contexto;
- contexto é limitado por source e `heading_path`;
- um único token não ativa inferência;
- DOM/lista explícita sempre vence;
- versão, IP, data ou identificador sem sequência coerente não devem produzir aresta.

### 4.2 Proveniência e confiança

Valores autorizados de `hierarchy_source`:

- `explicit_dom`;
- `numbering_inferred`;
- `heading_inferred`;
- `flat`.

Valores autorizados de `hierarchy_confidence`:

- `authoritative`;
- `deterministic`;
- `ambiguous`.

A projeção `hierarchy.numbered_hierarchy` deve registrar somente metadados relacionais necessários à auditoria: ordinal, token, depth, parent ordinal, source/confidence, edges e warnings. Não deve duplicar texto editorial.

### 4.3 Conflitos e ambiguidade

- DOM explícito × numeração conflitante → `HIERARCHY_NUMBERING_CONFLICT:*` + `review_required`;
- sinal hierárquico forte sem parent resolvível → `HIERARCHY_AMBIGUOUS:*` + `review_required`;
- relação explícita perdida → `not_ready`.

## 5. AI Readiness

`candidate_ready` exige simultaneamente:

- cardinalidade estrutural completa;
- relationship fidelity explícita completa quando aplicável;
- nenhum sinal hierárquico forte não reconciliado;
- nenhuma razão crítica preexistente.

O KD 2.1.0 deve expor separadamente:

- `cardinality_complete`;
- `relationship_complete`;
- `structure_complete = cardinality_complete && relationship_complete`;
- `numbered_hierarchy_status`.

`review_required` continua significando limitação explícita/auditável; não equivale automaticamente a perda estrutural.

## 6. Acceptance Gate G-240

O runner humano deve separar três dimensões:

- `human_pass`: quatro critérios observáveis aprovados pelo humano;
- `system_status`: `candidate_ready|review_required|not_ready|not_applicable`;
- `gate_pass`: combinação de aceite humano, estabilidade da amostra, repeatability e bloqueios técnicos.

Regras:

1. `not_ready` é bloqueante.
2. `review_required` não reprova automaticamente um slot com `human_pass=true`.
3. stale, sample mismatch e repeatability failure permanecem bloqueantes.
4. O gate global só pode fechar quando todos os oito slots tiverem `gate_pass=true` e não houver mutação editorial.

## 7. Determinismo e hashing

A projeção de hierarquia faz parte do `source_hash` e do `document_hash` do KD 2.1.0. Duas materializações consecutivas da mesma fonte devem produzir os mesmos hashes e o mesmo canonical JSON.

## 8. Não objetivos

- não usar LLM para inferir estrutura;
- não usar embeddings para determinar hierarquia;
- não reescrever, corrigir ou renumerar texto;
- não criar listas sintéticas no conteúdo editorial;
- não converter HTML para Elementor;
- não persistir resultados de aceite;
- não suportar alfabetos, romanos ou outras gramáticas sem evidência real e novo contrato.

## 9. Condição para liberar G-245

Este contrato não libera G-245 por si só. É obrigatório, nesta ordem:

1. testes unitários/sintéticos PASS;
2. full-corpus ambiental PASS com KD 2.1.0;
3. determinismo/repeatability PASS;
4. mesmos oito casos A/B com estrutura humana preservada 8/8;
5. `not_ready=0` no gate aceito;
6. PR #3 permanecer DRAFT até a evidência ser versionada e revisada.
