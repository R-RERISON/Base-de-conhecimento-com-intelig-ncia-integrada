# Prompt de Continuidade — SPEC-002 Classificação de Conhecimento

## Estado

- SPEC-001: CONCLUÍDA para desenvolvimento/homologação.
- Baseline funcional: `0.1.0-rc.1`.
- SPEC ativa: **SPEC-002 — Classificação de Conhecimento**.
- S001 profiling real: **PASS**.
- C-001: **PASS**.
- C-010: **PASS documental**.
- S002 contratos físicos/segurança: **PASS documental**.
- Runtime permanente de classificação: **IMPLEMENTADO em 0.2.0-dev.1**.
- Smoke visual real do `0.2.0-dev.1`: **PASS**.
- Próximo gate: diagnóstico técnico real do `0.2.0-dev.2`.

## Evidência de profiling

Ambiente real: WordPress 6.9.4 / PHP 8.5.10 / plugin `0.2.0-profile.1`.

- 622 posts no escopo;
- 36 linhas de postmeta classificatório entre 11 stores históricos;
- stores KB2Ops perfilados sem dados neste ambiente;
- stores GRE com cobertura ~0,96%–1,45% e forte heterogeneidade/freeform;
- nenhum merge autorizado entre service/affected_service ou technologies/systems_involved;
- decisão: não migrar automaticamente legado.

## Slice canônico autorizado

Taxonomias WordPress namespaced:

- `audience` -> `bdc_kb_audience` — multi;
- `responsible_team` -> `bdc_kb_responsible_team` — multi;
- `knowledge_type` -> `bdc_kb_knowledge_type` — single;
- `catalog_item` -> `bdc_kb_catalog_item` — multi.

Legado é somente referência read-only; sem dual-write e sem auto-promoção a termo.

## Smoke `0.2.0-dev.1`

Comprovado visualmente no WordPress real:

- Summary da SPEC-001 permanece legível e íntegro;
- painel de Classificação aparece no mesmo shell wp-admin;
- quatro conceitos renderizam;
- nenhum valor legado é selecionado automaticamente;
- vocabulários inicialmente vazios;
- links de gerenciamento aparecem para administrador;
- sem fatal error observado.

## Homologação `0.2.0-dev.2`

Build temporário com runner onclick autossuficiente.

O runner usa somente fixtures próprias e cobre:

- contrato das quatro taxonomias;
- read side-effect free;
- update parcial/omitidos;
- NO_CHANGE zero relação escrita;
- allowlist;
- termo inexistente;
- termo de taxonomia errada;
- cardinalidade single/multi;
- empty/remove;
- capability por objeto;
- post type fora do escopo;
- B-006 por inconsistência controlada de read-after-write;
- preservação editorial;
- legado read-only;
- regressão de write do Summary da SPEC-001;
- cleanup de post e termos temporários.

## Próximo passo exato

1. instalar por substituição `0.2.0-dev.2`;
2. abrir Base de Conhecimento;
3. clicar **Executar diagnóstico Classificação e gerar JSON**;
4. enviar `bdc-kb-classification-diagnostics-*.json`;
5. exigir `summary.overall=PASS`, `cleanup.residual_posts=0` e `cleanup.residual_terms=0`;
6. somente depois preparar negativos HTTP/browser da Classificação.

## Regra

Não criar dados canônicos reais para “testar”. Homologação usa fixtures próprias até os gates técnicos estarem PASS.
