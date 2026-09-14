# Tarefas — SPEC-000

## Preparação e inventários
- [x] T000–T002 preparação/governança.
- [x] T010–T019 KB2Ops.
- [x] T020–T034 ASI.
- [x] T040–T047 GRE.

## Cruzamento
- [x] T050–T059 consolidação arquitetural/documental.

## Revisões finais
- [x] T090 Arquiteto WordPress.
- [x] T091 Crítico de Simplicidade.
- [x] T092 Segurança.
- [x] T093 QA/Regressão.
- [x] T094 Produto/Conhecimento. _(`revisao-produto-t094.md`; PASS, SPEC-001 candidata mantida e ordem futura recomendada)_
- [ ] T095 unknowns/blockers por slice.
- [ ] T096 relatório final.
- [ ] T097 GO/NO-GO SPEC-001.

## Resultado T094
- [x] SPEC-001 candidata mantida: Core mínimo + Summary narrativo.
- [x] usuário primário explícito: Analista de Conhecimento.
- [x] primeiro slice limitado a `objective`, `escalation`, `important`.
- [x] cinco campos classificatórios GRE ficaram fora do owner Summary.
- [x] Classification futura recomendada começar por `knowledge_type` como eixo mínimo.
- [x] Review recomendado depois de Summary + classificação mínima; AI READY postergado até pré-requisitos completos.
- [x] Search reconhecida como primeira grande capacidade direta ao Resolvedor, porém posterior aos gates de extractor/Golden/security.
- [x] Search post-level permanece antes de item/deep-link.
- [x] Search Knowledge/Analytics/dashboards/IA continuam por evidência, não por legado.
- [x] sucesso de SPEC-001 pode ser homologado sem Analytics detalhado.
- [x] paridade necessária foi separada de layout/menus/shortcodes/estruturas históricas.
- [x] zero blocker global novo; nenhum runtime criado.

## Ordem recomendada após eventual T097
1. SPEC-001 Core mínimo + Summary narrativo.
2. SPEC-002 Classificação mínima (`knowledge_type`).
3. SPEC-003 Review mínimo.
4. SPEC-004 Content Extractor + Search post-level + Golden mínimo.
5. demais capacidades por evidência.

## Próximo passo exato
**T095 — fechar unknowns/blockers por slice.**

Classificar cada blocker/finding como `FECHAR_AGORA | NÃO_APLICÁVEL_A_SPEC001 | POSTERGAR_PARA_SLICE_CORRETO | BLOCKER_SPEC001`.

## Estado
T050–T059 + T090–T094 concluídos documentalmente. SPEC-001 continua bloqueada até T097.