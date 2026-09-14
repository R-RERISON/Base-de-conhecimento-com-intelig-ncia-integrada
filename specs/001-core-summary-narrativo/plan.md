# Plano — SPEC-001 Core mínimo + Summary narrativo

## Objetivo

Entregar o menor vertical slice homologável para o Analista de Conhecimento editar `objective`, `escalation` e `important` de posts da Base de Conhecimento, com WordPress Core, segurança por objeto e B-006.

## Baseline confirmado

- entrada: `main @ fced4a6015638b585d8817485fce8ef0fb8d7ccb`;
- SPEC-000 concluída;
- T097 autoriza SPEC-001, não release/cutover;
- target comprovado: `post`;
- três chaves existentes preservadas;
- runtime novo inexistente.

## Agentes convocados

- Orquestrador Principal;
- Arquiteto WordPress Core;
- Arquiteto de Produto/Conhecimento;
- Crítico de Simplicidade;
- Especialista de Segurança WordPress;
- Especialista de QA/Regressão;
- Arquiteto UI/UX WordPress.

## Dependências

- WordPress Core/Metadata API;
- baseline GRE 0.6.0 para semântica dos três campos;
- Design System do KB2Ops como referência, sem dependência de runtime;
- B-003 somente antes de produção/cutover.

## Sequência de implementação

1. **S001 — Definition of Ready documental**: concluída neste ciclo.
2. **S002 — Bootstrap mínimo**: criar plugin/shell somente para suportar o slice.
3. **S003 — Summary Store**: leitura, contrato, diff e B-006.
4. **S004 — UI server-rendered**: seleção paginada + editor + PRG.
5. **S005 — Segurança e fault injection**.
6. **S006 — Browser acceptance/regressão**.
7. **S007 — Package/DoD/handoff**, sem cutover produtivo.

## Vertical slice mínimo

`abrir wp-admin -> selecionar post -> ler 3 campos -> editar -> POST -> confirmar estado relido -> GET mostra resultado`.

## Decisões WordPress-first

Metadata API, WP_Query, capabilities, nonces, admin-post e server rendering. Nenhuma tabela, REST, AJAX, SPA ou serviço externo.

## Decisões submetidas ao princípio de negação

- não criar settings;
- não criar role/capability custom;
- não criar repository/interface genérica sem necessidade;
- não criar event bus;
- não criar histórico/audit genérico;
- não portar oito campos do GRE: apenas os três narrativos autorizados.

## Riscos

| Risco | Probabilidade | Impacto | Mitigação |
|---|---:|---:|---|
| write parcial | média | alto | B-006 + fault injection |
| IDOR/capability incorreta | baixa/média | crítico | `edit_post` por objeto + testes negativos |
| XSS em texto persistido | baixa | alto | sanitização + escaping contextual |
| interferência editorial | baixa | crítico | G-001 read-before/read-after |
| writer legado concorrente em produção | média | alto | B-003 antes de cutover; sem GO produtivo nesta SPEC |
| roadmap legado confundir execução | média | médio | placeholders marcados como supersedidos |

## Estratégia de testes

Matriz de Evidência própria com G-001/G-020/G-070/G-110/G-130 e B-006. Integração WordPress é obrigatória para metadata/capability/read-after-write; browser para fluxo real e UI.

## Estratégia de migração

Nenhuma. Reutilizar as três meta keys existentes.

## Estratégia de rollback

Desativação preserva postmeta. Nenhum purge automático.

## Evidências esperadas

Resultados versionados, logs de testes, browser acceptance, package/checksum quando aplicável e `CONTINUIDADE.md` atualizado.

## Gate de avanço

O runtime só pode começar porque S001/Definition of Ready está PASS. Homologação/release continuam proibidos enquanto gates ativos da Matriz de Evidência não estiverem PASS.
