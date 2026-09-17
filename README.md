# Base de Conhecimento com Inteligência Integrada

Plataforma WordPress de **gestão, curadoria, governança, busca e inteligência aplicada à Base de Conhecimento**.

> **Mantra do projeto:** “Quem não sabe onde está, não sabe para onde quer ir”.

## Estado atual

O projeto já possui runtime funcional e evolui por vertical slices governados por SPECs.

Baseline consolidada em `main` após o fechamento de G-240 da SPEC-004:

- SPEC-000 — Inventário Profundo e Contratos: concluída;
- SPEC-001 — Core mínimo + Summary narrativo: concluída;
- SPEC-002 — Classificação de Conhecimento: concluída;
- UX-001 — Product Experience & Knowledge Workspace: concluída;
- SPEC-003 — Review & Governança: concluída (`0.3.0-rc.1`);
- SPEC-004 — Content Extractor e Knowledge Document: ativa;
- R-200/R-210/G-220/G-230/G-240: PASS;
- Knowledge Document atual: schema `2.1.0`;
- build de aceite G-240: `0.4.0-acceptance.12`;
- G-245 — Elementor Normalization & Production Readiness: em andamento em branch dedicada e ainda não promovida.

O merge que promoveu G-240 para `main` é `32a696386bf2ab5574d4d7725db78636fa51f36c`.

## Regra de produto mais importante

O plugin **não substitui o WordPress/Elementor como fonte editorial**.

- O post continua sendo criado, editado e publicado no WordPress/Elementor.
- Elementor continua sendo a superfície editorial oficial.
- O knowledge plane não reescreve `_elementor_data` nem `post_content`.
- Projeções como Knowledge Document, índices, chunks e embeddings são derivados reconstruíveis.
- IA é assistiva: sugere, mas não recebe autoridade editorial automática.

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

## G-245 — fronteira atual

A próxima frente é preparar normalização Elementor e produção de forma explícita, auditável e reversível.

A branch ativa é:

`spec004-g245-production-readiness`

O PR #4 permanece **DRAFT**.

O Production Preflight T080 já foi executado read-only em homologação e não encontrou blocker para continuar o planejamento/projeção read-only. Isso **não autoriza writer ou migration editorial**.

Antes de qualquer escrita continuam obrigatórios, entre outros:

- matriz de compatibilidade;
- Projection Plan read-only;
- gateway Elementor version-gated;
- stale-source guard;
- journal/rollback;
- dry-run;
- canário controlado;
- autorização explícita posterior.

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
3. **Fonte editorial única.** `WP_Post` + Elementor permanecem canônicos.
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

🟡 **DESENVOLVIMENTO / HOMOLOGAÇÃO CONTROLADA** — SPEC-004 ativa, G-240 consolidado em `main`, G-245 ainda isolado em branch/PR draft.

**GO de desenvolvimento/homologação não equivale a GO de produção.**
