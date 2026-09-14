# Manifesto do Projeto — Base de Conhecimento com Inteligência Integrada

## Identidade

**Produto:** Base de Conhecimento com Inteligência Integrada  
**Tipo:** Plugin WordPress único, modular internamente  
**Idioma:** Português do Brasil  
**Estado:** SPEC-000 concluída / SPEC-001 Core mínimo + Summary narrativo **EM IMPLEMENTAÇÃO**; runtime S002 implementado, unitário T040 PASS, integração/browser ainda não homologados  
**Mantra:** “Quem não sabe onde está, não sabe para onde quer ir”.

## Missão

Construir uma plataforma única para governar a Base de Conhecimento, integrando curadoria, resumo executivo, classificação, busca lexical, busca semântica, telemetria, qualidade, operações e inteligência artificial sem substituir o WordPress ou o Elementor como fonte editorial.

## Estado de execução

A SPEC-000 — Inventário Profundo e Contratos — foi concluída documentalmente por T097.

T097 autorizou a **SPEC-001 — Core mínimo + Summary narrativo** sob escopo estrito. O Definition of Ready documental da SPEC-001 está PASS.

Estado comprovado da SPEC-001:

- S002/runtime mínimo: **IMPLEMENTADO**;
- PHP lint do runtime: **PASS**;
- T040/unitário determinístico: **PASS 15/15**;
- harness WordPress T041/T042: **VERSIONADO E LINTADO**;
- G-001/G-020/G-070 em WordPress real: **NOT_RUN**;
- B-006 em Metadata API real: **NOT_RUN**;
- G-110/browser acceptance: **NOT_RUN**;
- G-130/lifecycle/package: **NOT_RUN**.

Logo a SPEC está **Em implementação**. Não está em Homologação, Release, produção ou cutover.

## Usuários principais

### Analista de Conhecimento
Revisa, classifica, estrutura e aprova conteúdos para consumo operacional e futuro uso de IA.

### Resolvedor / Analista de Atendimento
Precisa encontrar rapidamente a próxima ação, procedimento, erro conhecido, validação e escalonamento corretos.

### Gestor de Conhecimento
Acompanha cobertura, qualidade, lacunas, comportamento de busca, risco e evolução da base.

### Administrador WordPress / Operações
Administra configuração, saúde, indexação, filas, migrações, IA e diagnósticos.

## Fonte da verdade

### Editorial
`WP_Post` + Elementor.

### Metadados e classificação
APIs nativas de metadata e taxonomias do WordPress sempre que suficientes.

### Projeções de busca
Índices derivados e reconstruíveis, nunca fonte editorial.

### IA
Saída assistiva e rastreável. IA não é fonte editorial nem autoridade de aprovação.

## Fronteiras não negociáveis

1. O plugin não mantém conteúdo editorial de posts.
2. O plugin não escreve em `_elementor_data`.
3. Elementor continua sendo o editor dos posts.
4. O plugin pode ler e interpretar Elementor para gestão, busca e IA.
5. A publicação oficial continua seguindo o fluxo WordPress.
6. Nenhum conteúdo não aprovado entra no índice produtivo de IA quando o gate de confiança estiver ativo.
7. Dados derivados devem ser reconstruíveis a partir da fonte da verdade.

## Hierarquia tecnológica

### Nível 0 — WordPress Core
Preferir Posts, Post Meta, Taxonomies, Options/Settings, Roles & Capabilities, Nonces, Hooks/Filters, Shortcodes, admin-post, WP-Cron, Transients/Object Cache, WordPress HTTP API, Site Health e REST apenas quando houver consumidor real.

### Nível 1 — Estruturas próprias
Aceitas somente quando há motivo técnico comprovado. A SPEC-000 aprovou documentalmente apenas uma família futura no baseline: **Search Retrieval Projection reconstruível**. Outras estruturas próprias continuam condicionadas às suas SPECs e gates.

### Nível 2 — Serviços externos
Apenas para capacidades que o ambiente WordPress não deve executar sozinho. Nenhum serviço externo é necessário para a SPEC-001.

## Princípio de negação

Nenhuma arquitetura é aceita sem a pergunta:

> “Qual parte desta solução pode ser removida sem perder o resultado?”

Uma decisão mais simples vence quando entrega o mesmo requisito com menor custo, menor acoplamento e menor superfície de falha.

## Referências históricas obrigatórias

- **KB2Ops:** produto, fluxo, Design System e experiência operacional.
- **ASI:** busca, índice, ranking, privacidade, Golden Queries, telemetria e operações.
- **Gerenciador de Resumo Executivo:** WordPress-first, clean code, contrato de metadata e implementação sucinta.

Referências preservam comportamento e aprendizado; não obrigam reprodução de código, schema, menus ou dívida histórica.

## Estratégia de produto

A plataforma deve entregar experiência integrada, mas por vertical slices. Produto único não significa big-bang.

## Estratégia de desenvolvimento

- Greenfield com memória institucional.
- Vertical slices.
- Baseline antes de mudança.
- Testes antes de substituição.
- Reversibilidade.
- Sem big-bang.
- Sem refatoração simultânea de todos os domínios.
- Sem dependência de GitHub Actions.
- Build e verificação devem poder rodar localmente.
- Toda implementação material termina com Prompt de Continuidade versionado.

## SPEC-001 canônica

**Core mínimo + Summary narrativo** para o Analista de Conhecimento.

Jornada:

`selecionar artigo -> ler -> editar -> salvar -> reler -> confirmar Summary`.

Post type suportado: **`post` somente**.

Dados:

- `objective` -> `_bdc_es_objective`;
- `escalation` -> `_bdc_es_escalation`;
- `important` -> `_bdc_es_important`.

Runtime implementado:

- wp-admin server-rendered;
- listagem paginada;
- GET read-only;
- POST via `admin-post` + nonce;
- `current_user_can('edit_post', $post_id)` por objeto;
- allowlist exata dos três campos;
- limite de 32768 bytes por campo antes da sanitização;
- `trim(sanitize_textarea_field())`;
- Metadata API;
- empty = delete;
- omitido = preservar;
- NO_CHANGE = zero write;
- read-after-write;
- B-006 por snapshot, diff, writes mínimos, compensação e reread;
- POST-Redirect-GET;
- UI mínima no shell nativo do wp-admin.

Evidência atual:

- unitário: PASS 15/15;
- integração WordPress: NOT_RUN;
- browser: NOT_RUN;
- lifecycle/package: NOT_RUN.

## Fora da SPEC-001

Classificação; Review/AI READY; Content Extractor; Search/Golden/Search Knowledge; Analytics/query logging; queue; tabela/schema/migration; REST/AJAX/SPA sem nova decisão; Foundry/LLM/embeddings/vector/semantic/rerank/agentes; aliases/shortcodes de compatibilidade; remoção/desativação de GRE/KB2Ops/ASI; cutover produtivo.

## Coexistência e produção

Desenvolvimento/homologação não removem plugins legados. B-003/preflight volta antes de produção, cutover ou coexistência não controlada de writers.

**GO de desenvolvimento != GO de produção.**

## Diretriz de IA

`IA sugere -> humano revisa -> humano aprova -> WordPress persiste`.

A SPEC-001 não inclui IA.

## Diretriz de custo

Operações futuras de IA devem ser observáveis e orçadas. Não aplicável ao runtime da SPEC-001.

## Diretriz visual

O Design System do KB2Ops é referência inicial. A SPEC-001 implementa apenas os elementos necessários à tela real, sem segundo shell administrativo.

## Regra de liberação

Uma versão não é liberada apenas porque compila ou passa testes unitários. Deve possuir evidência suficiente de integridade, segurança, regressão, compatibilidade visual, dados, rollback, Matriz de Evidência e Prompt de Continuidade.

A SPEC-001 permanece **Em implementação** enquanto qualquer gate MUST de Homologação estiver `NOT_RUN`, `FAIL`, `NOT_CONFIGURED` ou `STALE`.
