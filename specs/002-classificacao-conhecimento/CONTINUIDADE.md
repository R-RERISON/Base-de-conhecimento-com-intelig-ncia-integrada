# Prompt de Continuidade — SPEC-002 Classificação de Conhecimento

## Estado

- SPEC-001: CONCLUÍDA para desenvolvimento/homologação.
- Baseline funcional: `0.1.0-rc.1`.
- SPEC ativa: **SPEC-002 — Classificação de Conhecimento**.
- S001 profiling: **PASS**.
- S002 contratos físicos: **PASS**.
- S003 runtime mínimo: **PASS local**.
- Build atual: `0.2.0-dev.1`.
- Gates WordPress reais da SPEC-002: ainda NOT_RUN.

## Evidência S001

Profiler `0.2.0-profile.1` executado em WordPress 6.9.4 / PHP 8.5.10:

- 622 posts;
- 36 meta rows nos 11 stores candidatos;
- zero writes;
- stores KB2Ops perfilados sem dados;
- GRE com cobertura 0,96%–1,45%;
- legado inadequado para migração automática;
- `service↔affected_service` sem merge;
- `technologies↔systems_involved` sem merge.

Arquivo original: `bdc-kb-classification-profile-20260914-235424.json`.
SHA-256: `f11356632ee2f5720c6399c38af01bb4d0f4342af7e6e57b049fe0e07dda556b`.

## Decisão C-010

Primeiro slice canônico:

- `bdc_kb_audience` — multi;
- `bdc_kb_responsible_team` — multi;
- `bdc_kb_knowledge_type` — single;
- `bdc_kb_catalog_item` — multi.

Primitive: WordPress Taxonomy API.

Regras:

- sem seed/migração automática;
- sem dual-write;
- legado apenas referência read-only não canônica;
- artigo só seleciona termos existentes;
- gestão de vocabulário usa UI nativa WordPress;
- assignment via handler próprio com POST/nonce/`edit_post`;
- Summary permanece independente.

## Runtime `0.2.0-dev.1`

Adicionados:

- `Classification_Contract`;
- `Classification_Store`;
- `Classification_Admin`;
- integração visual na tela existente.

Evidência local:

- PHP lint: PASS 8/8;
- unitário Classification Store: PASS 15/15;
- fault injection cobre `FAIL_SAFE` e `PARTIAL_FAILURE_CRITICAL`.

## Próximo passo exato — T040

No WordPress de homologação:

1. substituir o build temporário de profiling por `0.2.0-dev.1`;
2. confirmar versão e ausência de fatal error;
3. abrir Base de Conhecimento e confirmar que Summary/listagem continuam normais;
4. abrir um artigo existente;
5. confirmar que a nova seção **Classificação de Conhecimento** aparece abaixo do Summary;
6. confirmar que nenhuma classificação histórica foi preselecionada automaticamente;
7. como administrador, confirmar que os links **Gerenciar vocabulários** abrem as telas nativas das quatro taxonomias;
8. não criar/migrar termos ainda se o objetivo for apenas smoke;
9. enviar screenshot/resultado.

Após smoke PASS: preparar build de homologação onclick para G-030/G-070 com termos e post fixture temporários, cleanup integral e JSON de evidência.

## Restrições

Serviço, serviço afetado, tecnologias, sistemas, keywords, versões, Review, Search, Analytics e IA permanecem fora do slice atual.