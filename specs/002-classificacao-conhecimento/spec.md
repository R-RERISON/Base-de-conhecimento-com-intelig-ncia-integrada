# SPEC-002 — Classificação de Conhecimento

**Status:** PLANEJAMENTO / S001 PROFILING  
**Baseline de abertura:** `main @ 8ec60e67c42afc6459ea6266c018c59730d86588`  
**Baseline funcional congelada:** plugin `0.1.0-rc.1`  
**Pré-requisito:** SPEC-001 concluída para desenvolvimento/homologação.

## 1. Problema

A plataforma já possui Summary narrativo governado, porém ainda não possui um owner canônico para classificação de conhecimento. Hoje conceitos classificatórios estão espalhados entre GRE, KB2Ops, taxonomias editoriais existentes e metadados históricos com cardinalidade, semântica e consumidores diferentes.

Sem resolver isso, qualquer Search, Review, Analytics ou IA posterior herdará ambiguidade e duplicidade.

## 2. Objetivo

Definir e implementar, por vertical slice, a camada canônica de **Classificação de Conhecimento**, preservando WordPress/Elementor como fonte editorial e evitando migração/dual-write por inércia.

A SPEC começa por profiling read-only. **Nenhum novo write classificatório está autorizado até o fechamento do DoR e da decisão Taxonomy vs Post Meta.**

## 3. Conceitos sob profiling

Dez conceitos lógicos, originados em onze stores históricos:

1. audiência — `_bdc_es_target_audience` e `_kb2ops_target_audience`;
2. equipe responsável — `_bdc_es_responsible_team`;
3. item de catálogo — `_bdc_es_catalog_item`;
4. serviço — `_kb2ops_service`;
5. serviço afetado — `_bdc_es_affected_service`;
6. tecnologias — `_kb2ops_technologies`;
7. sistemas envolvidos — `_bdc_es_systems_involved`;
8. tipo de conhecimento — `_kb2ops_knowledge_type`;
9. keywords — `_kb2ops_keywords`;
10. versões — `_kb2ops_versions`.

## 4. Hipótese de primeiro vertical slice

Após profiling, o primeiro runtime candidato será limitado a no máximo quatro conceitos:

- audiência;
- equipe responsável;
- tipo de conhecimento;
- item de catálogo.

Esta lista é **hipótese**, não decisão. Pode ser reduzida ou alterada pelos dados reais.

## 5. Jornada candidata

`selecionar artigo -> ler classificação atual -> editar conceitos autorizados -> salvar -> reler -> confirmar estado`

A jornada só será autorizada após S001/S002.

## 6. Princípios não negociáveis

1. Um conceito canônico possui um único owner lógico.
2. Não mesclar conceitos apenas por nomes parecidos.
3. `service` e `affected_service` permanecem distintos até evidência.
4. `technologies` e `systems_involved` permanecem distintos até evidência.
5. Audiência possui provável equivalência lógica, mas a convergência física depende de profiling.
6. Taxonomias editoriais existentes não são sequestradas pelo plugin.
7. Dual-write permanente é proibido.
8. Compatibilidade pode usar leitura adaptativa temporária, com gate explícito de remoção.
9. Nenhum write em `_elementor_data`, `post_content` ou `post_title`.
10. Classificação não aprova conteúdo, não define AI READY e não executa Search.

## 7. Taxonomy vs Post Meta — regra de decisão

A primitive física será decidida conceito a conceito.

### Taxonomy favorecida quando

- vocabulário reutilizável entre muitos posts;
- cardinalidade controlável;
- necessidade real de faceta/filtro;
- normalização e descoberta de termos trazem valor;
- permissão de criação/edição de termos pode ser governada.

### Post Meta favorecida quando

- atributo é altamente específico do post;
- cardinalidade é alta ou quase única;
- valor é técnico/versionado/freeform;
- faceta global não agrega valor;
- taxonomia criaria vocabulário ruidoso.

Nenhuma primitive é escolhida antes de T020/T021.

## 8. Fora do escopo

- Review/Governança/AI READY;
- Content Extractor;
- Search/Golden Queries;
- Analytics/telemetria;
- jobs/queue/indexação;
- schema/tabela customizada;
- REST/AJAX/SPA sem nova decisão;
- Foundry/LLM/embeddings/vector/rerank/agentes;
- migração destrutiva;
- remoção de GRE/KB2Ops/ASI;
- cutover produtivo.

## 9. Gates

- **C-001 Profiling:** cobertura, cardinalidade, representação e colisões conhecidas.
- **C-010 Primitive:** decisão Taxonomy vs Meta documentada por conceito do slice.
- **G-001 Editorial:** nenhuma alteração editorial.
- **G-030 Classification:** owner, contratos, multi-value, empty/remove, diff/read-after-write e compatibilidade.
- **G-070 Segurança:** capability, nonce, IDOR, allowlist, XSS, mass assignment.
- **G-110 UI/UX:** fluxo wp-admin, labels, teclado, viewport e feedback.
- **G-130 Lifecycle/package:** activation/deactivation/package sem resíduos de homologação.

## 10. Critérios de NO-GO

A implementação não começa se:

- profiling estiver incompleto ou inconclusivo;
- a mesma semântica estiver sendo gravada em dois owners permanentes;
- Taxonomy/Meta for escolhida apenas por preferência técnica;
- houver necessidade de tabela própria sem ADR e benchmark;
- a compatibilidade com stores legados não tiver leitura/rollback definidos;
- o slice exigir Search, Review ou IA para funcionar.

## 11. Próximo passo exato

Executar **S001 — Profiling classificatório read-only** no WordPress real, gerar JSON estruturado e decidir o escopo físico do primeiro vertical slice somente depois da análise.
