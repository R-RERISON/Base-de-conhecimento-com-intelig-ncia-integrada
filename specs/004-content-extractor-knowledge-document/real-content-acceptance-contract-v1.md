# Real Content Acceptance Contract — SPEC-004

## Histórico

### v1.0.0 — G-240 inicial

O primeiro contrato exigia cinco critérios humanos, incluindo `acceptable_for_knowledge_use`.

A execução real em `0.4.0-acceptance.1` resultou em **FAIL CONTROLADO**:

- 8/8 slots revisados;
- 0/8 aprovados;
- 7/8 com `structure_loss`;
- zero stale/repeatability/selection/write failures.

Evidência: `evidence/g240-acceptance-20260916T085721Z.json`.

O resultado demonstrou duas coisas:

1. o Knowledge Document v1 preservava cobertura textual, ordem e ausência de invenção na amostra;
2. a representação estrutural era insuficiente para listas, tabelas e contexto hierárquico.

Também ficou claro que “aceitável para IA” não deve ser uma decisão subjetiva do operador.

## Amendment v1.1 — reteste estrutural com Knowledge Document v2

**Estado:** FROZEN para remediação G-240.  
**Knowledge Document:** `2.0.0`.  
**Amostra:** os mesmos oito posts que falharam no v1, congelados para comparação A/B.

### 1. Natureza

O G-240 continua sendo inspeção humana de fidelidade, read-only e sem persistência.

A ferramenta:

- não altera posts/metas/termos/options;
- não executa shortcodes, blocos dinâmicos ou render Elementor arbitrário;
- não usa IA para decidir se a extração “parece boa”;
- exibe a fonte editorial real somente dentro do wp-admin;
- exporta JSON sem corpo, título ou URL;
- pode exportar post ID para rastreabilidade da amostra;
- usa fingerprints para stale guard;
- reconstrói o Knowledge Document mais de uma vez para repeatability.

### 2. Critérios humanos v2

O revisor marca somente o que pode comparar objetivamente:

- `coverage_complete`;
- `order_preserved`;
- `no_invented_text`;
- `structure_preserved`.

`acceptable_for_knowledge_use` foi removido.

### 3. AI readiness

O sistema calcula e exibe:

- `candidate_ready`;
- `review_required`;
- `not_ready`;
- `not_applicable`.

Esse status é produzido por invariantes estruturais/warnings e não pelo operador.

O G-240 não exige que todos os documentos sejam `candidate_ready`: um caso `review_required` pode passar o gate humano quando a representação expõe fielmente seu conteúdo e também expõe a limitação que exige revisão. `not_ready` bloqueia consumidores futuros, mas a razão deve permanecer observável.

### 4. Amostra A/B congelada

- `elementor_native_typical`: post 44981;
- `elementor_or_mixed_complex`: post 1290;
- `legacy_typical`: post 370;
- `legacy_complex`: post 1307;
- `gutenberg`: post 45782;
- `shortcode_or_table`: post 36431;
- `review_required`: post 1289;
- `empty_or_corrupt`: post 28748.

Os fingerprints da evidência v1 são baseline. Mudança editorial posterior torna o slot `stale` e invalida comparação A/B até nova baseline consciente.

### 5. Ordem operacional

Antes do aceite humano v2:

1. executar full-corpus `Validação KD v2`;
2. confirmar schema `2.0.0`;
3. duas passagens completas;
4. zero errors/throwables;
5. zero hash/canonical JSON mismatch;
6. zero `structure_incomplete`;
7. fingerprint editorial igual;
8. zero changed posts.

Somente então executar `Aceitação G-240 v2`.

### 6. PASS humano v2

PASS exige, para todos os oito slots válidos da baseline:

- quatro critérios humanos `true`;
- `stale=false`;
- `repeatable=true`;
- post ID esperado preservado;
- fingerprint de geração da evidência sem mudança;
- zero write.

Falha em qualquer item mantém G-240 bloqueado.

### 7. Evidência e privacidade

O JSON pode exportar:

- ambiente/versões;
- post ID da amostra;
- source/document hashes;
- `ai_readiness` e reasons enum;
- verdict flags;
- stale/repeatability;
- fingerprints agregados.

O JSON não exporta:

- `post_content`;
- `_elementor_data`;
- título;
- URL;
- texto das `sections[]`/`blocks[]`.

### 8. Relação com G-245

G-245 permanece bloqueado enquanto G-240 v2 não passar.

A normalização para Elementor não pode ser usada para esconder uma falha do Knowledge Document. Primeiro provamos leitura/estrutura; depois projetamos/migramos armazenamento editorial.
