# Roadmap SpecKit — Base de Conhecimento com Inteligência Integrada

> **Mantra:** “Quem não sabe onde está, não sabe para onde quer ir”.

Este roadmap é deliberadamente sequencial. Ele evita big-bang e exige que cada gate produza um vertical slice homologável.

## SPEC-000 — Inventário profundo e contratos de legado

**Objetivo:** entender detalhadamente os três projetos de referência antes de criar runtime.

Entregas:

- mapa de funcionalidades;
- hooks/actions/filters;
- rotas/admin/AJAX/REST;
- options/transients/cron/capabilities;
- meta/taxonomias/tabelas/índices;
- shortcodes;
- CSS/JS/templates;
- lifecycle/migrações;
- telemetria/privacidade;
- testes/Golden Queries;
- matriz MANTER/REDESENHAR/SUBSTITUIR POR WORDPRESS/EVOLUIR/DESCARTAR;
- catálogo de contratos de regressão.

**Gate:** nenhum runtime novo antes de concluir.

---

## SPEC-001 — Core WordPress e shell visual

**Objetivo:** primeiro plugin instalável, ainda sem recriar funcionalidades complexas.

Entregas previstas:

- bootstrap mínimo;
- lifecycle seguro;
- capabilities/settings mínimas;
- Design System próprio derivado do KB2Ops;
- navegação administrativa única;
- Site Health básico;
- build/release local;
- página Visão Geral vazia porém real.

**Vertical slice:** instalar → ativar → abrir shell → validar saúde → desativar sem dano.

---

## SPEC-002 — Resumo Executivo integrado

**Objetivo:** reconstruir o domínio mais simples e WordPress-first.

Entregas previstas:

- oito campos canônicos;
- preservação dos `_bdc_es_*` existentes;
- store/serviço interno;
- edição dentro do Knowledge Studio;
- renderer compatível;
- cobertura.

**Vertical slice:** abrir post no Studio → editar resumo → salvar → reler → visualizar.

---

## SPEC-003 — Knowledge Studio: revisão e classificação

**Objetivo:** reconstruir a gestão do conhecimento ao redor do post.

Entregas previstas:

- estados de revisão;
- classificação;
- avaliação de taxonomias versus metadata;
- qualidade;
- AI READY sem IA ainda;
- histórico/auditoria conforme decisão WordPress-first.

**Vertical slice:** selecionar post → revisar → classificar → aprovar → estado refletido.

---

## SPEC-004 — Content Extractor e Knowledge Document

**Objetivo:** criar projeção canônica read-only do conteúdo editorial.

Entregas previstas:

- Elementor read-only;
- Gutenberg/HTML legado;
- headings/parágrafos/tabelas/imagens/shortcodes;
- normalização;
- hashes;
- Knowledge Document reconstruível.

**Vertical slice:** post real → extrair → comparar visual/conteúdo → gerar documento canônico sem alterar post.

---

## SPEC-005 — Search lexical e Golden Queries

**Objetivo:** reconstruir a busca determinística antes da semântica.

Entregas previstas:

- Query Normalizer;
- decisão WP_Query versus FULLTEXT/tabela própria;
- ranking lexical mínimo;
- resultados no Knowledge Search;
- trechos/itens conforme necessidade comprovada;
- Golden Queries desde o primeiro release de busca.

**Vertical slice:** consulta representativa → ranking correto → abrir fonte oficial.

---

## SPEC-006 — Telemetria, Inteligência de Busca e lacunas

**Objetivo:** medir resultado real sem inflar ou confundir eventos.

Entregas previstas:

- eventos;
- interações;
- zero-result correto;
- engajamento;
- privacidade;
- relatórios;
- knowledge gaps.

**Vertical slice:** buscar → clicar → evento correlacionado → relatório atualizado.

---

## SPEC-007 — Operações, jobs, indexação e migrações

**Objetivo:** adicionar infraestrutura operacional apenas onde o produto provar necessidade.

Entregas previstas:

- WP-Cron como wake-up;
- fila durável se justificada;
- indexação incremental;
- reprocessamento;
- migração retomável;
- diagnóstico;
- rollback.

**Vertical slice:** mudança de post → job → projeção/index atualizado → estado verificável.

---

## SPEC-008 — Semantic Search e Vetores

**Objetivo:** adicionar recuperação semântica sem quebrar busca lexical.

Entregas previstas:

- capability detection do banco;
- chunks;
- embeddings;
- vector store;
- semantic retrieval;
- hybrid ranker;
- comparação lexical versus híbrida;
- fallback lexical.

**Vertical slice:** Golden Query difícil → lexical + semântico → melhoria comprovada sem regressão global.

---

## SPEC-009 — Plataforma de IA e Microsoft Foundry

**Objetivo:** criar subsystem de IA governado e desacoplado.

Entregas previstas:

- provider contract;
- Foundry adapter;
- secrets/configuração segura;
- Prompt Registry;
- usage/cost tracking;
- retries/timeouts;
- diagnóstico.

**Vertical slice:** teste configurado → chamada pequena → resposta validada → custo registrado.

---

## SPEC-010 — Revisão Assistida por IA

**Objetivo:** IA ajudar o analista sem assumir autoridade editorial.

Entregas previstas:

- sugestões de resumo;
- classificação;
- inconsistências;
- versões/obsolescência;
- riscos;
- aceite/rejeição humano;
- diff e auditoria.

**Vertical slice:** post → IA sugere → analista aceita/rejeita → somente decisão humana persiste.

---

## SPEC-011 — Resolução assistida / síntese com evidências

**Objetivo:** oferecer resposta operacional baseada em retrieval confiável.

Entregas previstas:

- contexto recuperado;
- resposta estruturada;
- evidências/fontes;
- limitações;
- custo;
- fallback sem IA.

**Vertical slice:** query → retrieval → síntese → fontes → artigo oficial.

---

## SPEC-012 — Paridade, cutover e aposentadoria controlada dos plugins anteriores

**Objetivo:** substituir os três plugins apenas depois de paridade comprovada.

Entregas previstas:

- matriz de paridade completa;
- migração/coexistência;
- Golden Queries;
- dados históricos;
- rollback;
- desativação em homologação;
- release gate completo.

**Gate final:** somente aqui os plugins anteriores deixam de ser necessários.

---

## Regra de alteração do roadmap

O roadmap pode evoluir, mas nunca deve ser encurtado apenas para acelerar entrega. Juntar Specs exige provar que isso reduz complexidade sem aumentar risco ou custo de erro.