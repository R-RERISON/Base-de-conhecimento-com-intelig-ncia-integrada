# Base de Conhecimento com Inteligência Integrada

Plataforma WordPress de **gestão, curadoria, governança, busca e inteligência aplicada à Base de Conhecimento**.

> **Mantra do projeto:** “Quem não sabe onde está, não sabe para onde quer ir”.

## Estado atual

O projeto já possui runtime funcional e evolui por vertical slices governados por SPECs.

Baseline consolidada em `main`: SPEC-000/001/002/003/004 concluídas; UX-001/002/003 concluídas; SPEC-004 CLOSED/main após G-240/G-245/G-250. Release homologada: `0.4.0-spec004-rc2`; Knowledge Document schema `2.1.0`.

Frente atual: **SPEC-005 — Search Lexical e Golden Queries / ATIVA / DISCOVERY**, na branch `spec005-search-lexical-golden-queries`.

- R-500: PASS/CLOSED; superfície inicial ADMIN-FIRST.
- T510 e T511.2: PASS AMBIENTAL.
- T511.2: seis candidates; admin atual atende 2/6; admin relevância e publish nativo atendem 6/6 até max_rank histórico.
- R-510: OPEN; próxima etapa é revisão humana T513 e diversidade T514.
- Engine bloqueada até R-500 + R-510 + G-520.

[Estado detalhado](specs/005-search-lexical-golden-queries/current-state.md) · [Prompt de continuidade](specs/005-search-lexical-golden-queries/CONTINUIDADE.md).

## Regra de produto mais importante

O WordPress é a autoridade editorial e de acesso. `WP_Post.post_content` + WordPress Core Blocks são o destino editorial canônico, conforme Constituição v1.3.0.

- Elementor permanece adapter legado temporário, com `_elementor_data` preservado.
- O plugin Gutenberg não é dependência de produção.
- Search e demais projeções não reescrevem conteúdo editorial.
- Knowledge Document, índices, chunks e embeddings são derivados reconstruíveis.
- IA é assistiva: sugere; humano decide; WordPress persiste.

## Runtime atual

A plataforma já consolida:

- Core WordPress e navegação integrada;
- Summary narrativo;
- Classificação de Conhecimento;
- Review & Governança com event log append-only via Comments API;
- Histórico read-only;
- Content Extractor para Elementor, Gutenberg/blocos, HTML legado e plain text;
- Knowledge Document canônico e determinístico;
- análise estrutural/hierárquica conservadora;
- gates de aceite com evidência de zero mutação editorial.

No fechamento de G-240, o corpus de homologação com 622 posts foi processado em duas passagens completas sem errors, throwables, mismatches de hash/JSON, `structure_incomplete` ou `not_ready`. O aceite humano dos oito casos fixos fechou 8/8 para cobertura, ordem, ausência de texto inventado e preservação estrutural.

## SPEC-005 — fronteira atual

O baseline de busca foi medido antes de qualquer engine própria. A ordenação administrativa por `modified DESC` não será promovida como ranking de Search. Gaps de Summary/Content Extractor justificam avaliar Search Document semântico; persistência depende de G-520.

O próximo passo é [revisar as seis expectativas e completar diversidade](specs/005-search-lexical-golden-queries/r510-golden-candidate-review-v1.md). O baseline T511.2 já foi executado; não há novo pacote nesta consolidação. `0.5.0-r510-t511.1` está SUPERSEDED e não deve ser instalado.

ASI é referência histórica, não dependência: T511.2 consome seed próprio. A comprovação operacional com ASI desativado permanece no gate G-585 antes do RC.

## Projetos de referência

Os três repositórios abaixo permanecem fontes obrigatórias de aprendizado e comportamento comprovado, nunca dependências de runtime:

1. **KB2Ops — Operational Knowledge Engine**

   https://github.com/R-RERISON/KB2Ops-Operational-Knowledge-Engine

2. **Advanced Search Intelligence (ASI)**

   https://github.com/R-RERISON/Advanced-search-Intelligence

3. **Gerenciador de Resumo Executivo da Base de Conhecimento**

   https://github.com/R-RERISON/Gerenciador-de-Resumo-Executivo-da-Base-de-Conhecimento

## Princípios fundamentais

1. **WordPress-first.** Avaliar Core, hooks e APIs nativas antes de infraestrutura própria.
2. **Princípio de negação.** Toda complexidade precisa justificar sua existência.
3. **Fonte editorial única.** WordPress/Core Blocks como destino; Elementor como adapter legado temporário.
4. **Vertical slices.** Mudanças pequenas, homologáveis, reversíveis e com gate explícito.
5. **Sem regressão silenciosa.** Paridade e evidência antes de substituição.
6. **Humano como autoridade editorial.** IA sugere; humano decide.
7. **Dados derivados são reconstruíveis.** Knowledge Document, índices, chunks e vetores não substituem a fonte.
8. **Segurança WordPress.** Capability, nonce, validação, sanitização, escaping e menor privilégio.
9. **Custo e observabilidade.** IA e processamento intensivo exigem limites e telemetria.
10. **Português do Brasil.** Artefatos humanos do projeto permanecem em pt-BR.

## Ordem de leitura antes de alterar o projeto

1. `AGENTS.md`
2. `.specify/PROJECT_MANIFEST.md`
3. `.specify/memory/constitution.md`
4. `docs/DEFINITION-OF-DONE.md`
5. `specs/ROADMAP.md`
6. SPEC ativa e seus artefatos
7. `CONTINUIDADE.md` da SPEC ativa

## Status

🟡 **DESENVOLVIMENTO / HOMOLOGAÇÃO CONTROLADA** — SPEC-004 CLOSED/main; SPEC-005 ATIVA/DISCOVERY, com R-510 OPEN e engine ainda bloqueada.

**GO de desenvolvimento/homologação não equivale a GO de produção.**
