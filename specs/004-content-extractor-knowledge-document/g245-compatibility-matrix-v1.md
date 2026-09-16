# G-245 Compatibility Matrix v1 — SPEC-004

**Status:** FROZEN — baseline de homologação  
**Data:** 2026-09-16  
**Origem:** `0.4.0-g245-preflight.1` em homologação  
**Evidência:** `evidence/g245-preflight-summary-20260916T215612Z.json`

## 1. Objetivo

Congelar a primeira matriz factual de compatibilidade para o G-245 sem autorizar escrita editorial.

Esta matriz habilita apenas o próximo subgate **Projection Plan read-only**. Ela não autoriza `Elementor_Gateway` writer, migration, persistência, dry-run mutável ou canário.

## 2. Runtime homologado

| Componente | Versão/estado observado | Classificação G-245 | Regra |
|---|---|---|---|
| WordPress | `6.9.4` | compatible | Deve continuar atendendo ao mínimo declarado pelo plugin. |
| PHP | `8.5.10` | compatible | Deve continuar atendendo ao mínimo declarado pelo plugin. |
| Elementor | `4.1.0` | compatible | Única versão Elementor homologada nesta matriz inicial. |
| DOMDocument | disponível | compatible | Obrigatório para validações estruturais contratadas. |
| MariaDB | `12.2.2-MariaDB-ubu2404-log` | compatible for read-only planning | Nenhuma escrita de migration foi homologada ainda. |
| WP-Cron | habilitado | compatible | Jobs assíncronos ainda não estão autorizados. |
| Loopback | not_tested | review_required | Resolver antes de qualquer gate que dependa de jobs/requests internos. |
| Multisite | false | compatible | Matriz atual não homologa multisite. |

## 3. Elementor

### Homologado

- `4.1.0` — compatible para leitura, classificação e construção futura de Projection Plan.

### Regra fail-closed

- versão Elementor diferente de `4.1.0` => `review_required` até teste explícito;
- Elementor ausente => `blocking` para migration Elementor;
- nenhuma versão, inclusive `4.1.0`, implica `writer_allowed=true` nesta fase.

## 4. Dependências de shortcode observadas

### Registradas e atribuídas

| Shortcode | Provider observado | Estado |
|---|---|---|
| `bdc_resumo_executivo` | `plugin:gerenciador-resumo-executivo` | compatible as dependency-present |
| `caption` | `wordpress-core` | compatible |
| `dbc_table` | `plugin:dbc-table-custom` | compatible as dependency-present |
| `table` | `plugin:tablepress` | compatible as dependency-present |
| `video` | `wordpress-core` | compatible |

A presença do handler não autoriza execução do shortcode durante extração ou Projection Plan.

### Legado sem handler ativo

| Shortcode | Estado | Política |
|---|---|---|
| `faq_wd` | review_required / legacy orphan | Nunca executar ou substituir automaticamente. Projection Plan do post deve carregar warning explícito. Evidência externa indica origem histórica no plugin 10WebFAQ/FAQ WD (`faq-wd`), ausente do runtime ativo. |
| `wpt` | review_required / unknown legacy dependency | Origem não comprovada. Nunca inferir provider, executar ou substituir automaticamente. Projection Plan do post deve carregar warning explícito. |

## 5. Plugins ativos relevantes à projeção

Baseline observada no preflight:

- Elementor `4.1.0`;
- TablePress `3.3.1`;
- DBC Table Custom `1.0.1`;
- Gerenciador Resumo Executivo `0.8.0`;
- Ultimate FAQs `2.4.11`;
- Royal Elementor Addons `1.7.1062`;
- Sticky Header Effects for Elementor `2.1.9`;
- Ultimate Post Kit `4.1.10`.

A lista completa permanece na evidência bruta do preflight. Mudança de versão/provider relevante entre planejamento e execução deve gerar stale/review antes de writer.

## 6. Segurança comprovada

O preflight executado em homologação comprovou:

- corpus `622 -> 622`;
- fingerprint editorial antes/depois idêntico;
- `writer_allowed=false`;
- `migration_execution_allowed=false`;
- sem escrita em `post_content`;
- sem escrita em `_elementor_data`;
- sem execução de shortcodes;
- sem rede externa;
- sem persistência de resultado.

## 7. Decisão T080J

A matriz inicial está congelada com **0 blockers** para o próximo subgate read-only e dois itens explícitos de revisão:

1. shortcodes legados sem handler (`faq_wd`, `wpt`);
2. loopback não testado.

Esses itens **não bloqueiam Projection Plan read-only**, mas continuam bloqueando qualquer promoção que dependa de execução dinâmica, jobs ou writer.

## 8. Regra para Projection Plan

O próximo subgate deve:

- produzir plano determinístico sem persistência;
- preservar `source_hash_before`;
- nunca executar shortcode;
- promover `faq_wd`/`wpt` para `requires_review=true`;
- não converter dependência desconhecida em HTML materializado;
- manter `writer_allowed=false` globalmente;
- produzir hash canônico do plano para repetibilidade.
