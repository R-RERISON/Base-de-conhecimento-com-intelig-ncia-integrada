# T097 — Decisão Formal de GO/NO-GO da SPEC-001

> Estado: **GO — AUTORIZADA A ABERTURA E EXECUÇÃO DA SPEC-001, SOB ESCOPO E GATES EXPLÍCITOS**.  
> Baseline de decisão: `main @ 30d655cd73d8a2ded0b726137d0e8ac5c66265f3` (fechamento T096).  
> Esta decisão encerra a SPEC-000 documentalmente e autoriza o próximo ciclo. Não é release, não é cutover de produção e não declara funcionalidade implementada.

## 1. Pergunta do gate

> **A evidência acumulada em T000–T096 é suficiente para autorizar a criação e execução de uma SPEC-001 pequena, WordPress-first, segura, testável e reversível?**

**Resposta:** SIM.

---

## 2. Fundamentação do GO

### WordPress-first — PASS

T090 confirmou que Summary pode ser implementado com primitives nativas, sem tabela própria, CMS paralelo, REST obrigatório, SPA ou infraestrutura customizada.

### Simplicidade — PASS

T091 reduziu o primeiro slice a um fluxo único e homologável. Frameworks genéricos, Search, IA, Analytics e operações avançadas ficaram fora.

### Segurança — PASS arquitetural

T092 fixou capability por objeto, POST + nonce, IDOR/mass-assignment/XSS protections, escaping contextual e NO-GO transversais.

### QA/Regressão — PASS documental

T093 definiu Matriz de Evidência por SPEC, estados explícitos de teste e cenários negativos obrigatórios.

### Produto/Conhecimento — PASS

T094 confirmou usuário, dor e valor do primeiro slice: Analista de Conhecimento gerencia Summary narrativo sem alterar conteúdo editorial.

### Blockers — PASS

T095 confirmou `BLOCKER_SPEC001 = 0` e fechou B-006 conceitualmente para o write composto.

### Relatório final — RECOMENDA GO

T096 consolidou todo o estado e recomendou GO condicionado.

---

## 3. Escopo autorizado da SPEC-001

Nome conceitual:

**SPEC-001 — Core mínimo + Summary narrativo**

Usuário primário:

**Analista de Conhecimento**.

Jornada autorizada:

`selecionar artigo -> abrir tela Summary -> ler objective/escalation/important -> editar -> salvar -> reler -> confirmar estado`

Dados autorizados:

- `objective` -> `_bdc_es_objective`;
- `escalation` -> `_bdc_es_escalation`;
- `important` -> `_bdc_es_important`.

A reutilização destas chaves GRE é deliberada para preservar dados e evitar migration/dual-write desnecessário.

---

## 4. Superfície técnica autorizada

A SPEC-001 pode planejar e, após atingir seu próprio Definition of Ready, implementar:

- bootstrap mínimo do plugin necessário ao slice;
- tela integrada ao wp-admin;
- fluxo server-rendered;
- GET apenas para leitura;
- POST para mutação;
- nonce específico;
- `current_user_can( 'edit_post', $post_id )` ou controle por objeto equivalente aprovado;
- allowlist dos três campos;
- validação/sanitização;
- escaping contextual;
- Metadata API;
- read-after-write;
- tratamento B-006 por snapshot/compensação;
- feedback de sucesso/erro;
- Design System mínimo usado pela tela real;
- testes, fixtures e artefatos necessários para provar os gates da SPEC.

---

## 5. Condições obrigatórias antes do primeiro código da SPEC-001

O GO de T097 autoriza **a SPEC-001**, não um salto direto para implementação sem baseline.

Antes do primeiro código material, a SPEC-001 deve:

1. criar sua pasta/artefatos SpecKit;
2. confirmar HEAD/branch;
3. inventariar os `post_type` reais que compõem a Base de Conhecimento no ambiente/baseline;
4. definir explicitamente os post types suportados pelo slice;
5. registrar a Matriz de Mutação de segurança;
6. registrar a Matriz de Evidência de QA;
7. fixar contratos de vazio/delete, limites e sanitização dos três campos;
8. detalhar casos B-006 e fault injection;
9. descrever UI/UX mínima e browser acceptance;
10. definir critérios de aceite/não aceite e rollback.

Se o target de post type não puder ser comprovado, a implementação permanece `NOT_READY`.

---

## 6. O que o GO NÃO autoriza

T097 **não autoriza dentro da SPEC-001 por implicação**:

- Classificação;
- Review/Governança;
- AI READY;
- Content Extractor;
- Search customizada;
- Search Retrieval Projection;
- Golden Queries de Search;
- Search Knowledge;
- Analytics/query logging;
- durable queue;
- tabela/schema/migration;
- REST API;
- AJAX sem requisito formal;
- SPA/React;
- Foundry/LLM;
- prompts de runtime;
- embeddings/vector store;
- semantic/hybrid/rerank;
- agentes/tools;
- Word Cloud;
- GAC;
- aliases/shortcodes de compatibilidade;
- remoção/desativação automática dos plugins legados;
- cutover de produção.

Qualquer expansão material exige nova SPEC/ADR ou alteração formal da SPEC-001 com os gates correspondentes.

---

## 7. Coexistência e produção

SPEC-001 pode ser desenvolvida e homologada sem remover GRE/KB2Ops/ASI.

Como as três chaves Summary já existem no GRE, produção com dois writers simultâneos exige antes:

- B-003/preflight;
- identificação de consumidores/writers reais;
- single-writer ou coexistência comprovadamente compatível;
- rollback;
- gate de remoção do legado quando aplicável.

Portanto:

**GO para desenvolvimento/homologação != GO para cutover produtivo.**

---

## 8. Gates mínimos da SPEC-001

A futura SPEC deve tratar pelo menos:

- **G-001** — editorial/Elementor read-only;
- **G-020** — Summary/Metadata/B-006;
- **G-070** — autorização, nonce, scope, IDOR, escaping;
- **G-110** — UI/UX/a11y básica;
- **G-130** — lifecycle/package quando aplicável;
- **DoD** completo para o release candidate.

Estados `NOT_RUN`, `NOT_CONFIGURED` ou `STALE` em gate ativo não podem ser PASS.

---

## 9. Critérios de não aceite

A SPEC-001 não pode ser considerada concluída se ocorrer qualquer um destes:

- write em `_elementor_data`;
- alteração silenciosa de `post_content`/`post_title`;
- capability apenas no menu;
- mutação por GET;
- mass assignment;
- sucesso falso em write parcial;
- output inseguro/XSS;
- target de post arbitrário;
- limpeza destrutiva em activation/uninstall;
- dependência de plugin legado para funcionar sem contrato explícito;
- introdução silenciosa de capacidade fora do escopo autorizado.

---

## 10. Estado da SPEC-000

Com T097:

- **SPEC-000 — CONCLUÍDA**;
- inventário e contratos finalizados;
- arquitetura futura aprovada documentalmente;
- riscos residuais vinculados aos slices corretos;
- nenhum runtime novo criado durante a SPEC-000;
- SPEC-001 formalmente autorizada a ser aberta.

---

## 11. Próximo passo exato

Criar e iniciar:

**`specs/001-core-summary-narrativo/`**

Primeiro bloco da SPEC-001:

**S001/T001 — Baseline real + post types + contratos dos três metadados + matrizes de segurança/QA, antes de código.**

Somente após o Definition of Ready da SPEC-001 ficar comprovado é permitido implementar o primeiro vertical slice.

---

## 12. Decisão final

# **GO — SPEC-001 AUTORIZADA**

Autorização restrita ao escopo e às condições deste documento.