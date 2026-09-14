# SPEC-002 — Classificação de Conhecimento

**Status:** S001 PROFILING PASS / S002 CONTRATOS FÍSICOS AUTORIZADOS  
**Baseline de abertura:** `main @ 8ec60e67c42afc6459ea6266c018c59730d86588`  
**Baseline funcional congelada:** plugin `0.1.0-rc.1`  
**Pré-requisito:** SPEC-001 concluída para desenvolvimento/homologação.

## 1. Problema

A plataforma possui Summary narrativo governado, mas ainda não possui owner canônico para classificação. Conceitos históricos aparecem em GRE/KB2Ops com semânticas e representações distintas; Search, Review, Analytics e IA não podem depender desses stores sem contrato.

## 2. Evidência real S001

O profiler read-only `0.2.0-profile.1` foi executado em WordPress 6.9.4 / PHP 8.5.10 sobre 622 posts.

Foram encontradas apenas 36 linhas nos 11 stores candidatos. Os stores KB2Ops perfilados estão vazios; os stores GRE possuem cobertura entre 0,96% e 1,45%, com valores quase todos únicos e sinais de texto livre/formatos inconsistentes.

Conclusão: o legado é evidência histórica, não um vocabulário confiável para promoção automática.

## 3. Decisão arquitetural

### 3.1 Migração

- migração automática do legado: **PROIBIDA neste slice**;
- seed automático a partir dos valores históricos: **PROIBIDO**;
- dual-write legado + canônico: **PROIBIDO**;
- stores antigos: **read-only/advisory**, explicitamente rotulados como referência não canônica.

### 3.2 Primeiro vertical slice

O primeiro runtime será limitado a quatro conceitos:

1. audiência;
2. equipe responsável;
3. tipo de conhecimento;
4. item de catálogo.

### 3.3 Primitive física

Os quatro conceitos são reutilizáveis, precisam ser facetáveis e devem possuir vocabulário governado. Portanto, a primitive canônica será **WordPress Taxonomy API**, com taxonomias namespaced próprias do plugin.

| Conceito | Taxonomy canônica | Cardinalidade | Termos no article form |
|---|---|---|---|
| audiência | `bdc_kb_audience` | multi | somente termos existentes |
| equipe responsável | `bdc_kb_responsible_team` | multi | somente termos existentes |
| tipo de conhecimento | `bdc_kb_knowledge_type` | single | somente termo existente |
| item de catálogo | `bdc_kb_catalog_item` | multi | somente termos existentes |

Nenhuma taxonomia editorial existente é reutilizada ou sequestrada.

## 4. Política de vocabulário

- artigo não cria termos implicitamente;
- termos são administrados por usuário com capability de gerenciamento de termos;
- assignment exige capability do objeto `edit_post`;
- nenhum termo histórico é criado automaticamente;
- normalização de display/slug fica a cargo das APIs WordPress e da curadoria, não de transformação destrutiva do valor legado;
- `show_in_rest=false` enquanto não existir consumidor aprovado;
- `public=false`, sem rewrite/front-end query.

## 5. Jornada autorizada

`selecionar artigo -> ler classificação canônica -> consultar referência legada read-only -> selecionar termos existentes -> salvar -> reler -> confirmar estado`

O Summary continua em formulário/handler separado. Um save classificatório nunca grava Summary nem conteúdo editorial.

## 6. Contrato de escrita

1. POST somente;
2. nonce específico ligado ao post;
3. `current_user_can('edit_post', $post_id)`;
4. allowlist exata dos quatro conceitos;
5. payload por conceito = array de term IDs inteiros existentes;
6. `tipo de conhecimento` aceita no máximo um termo;
7. campo omitido = preservar;
8. array vazio = remover todas as relações daquele conceito;
9. termo de outra taxonomy = rejeitar payload antes de qualquer write;
10. diff mínimo;
11. snapshot antes de mutação;
12. `wp_set_object_terms(..., false)` somente nas taxonomias alteradas;
13. read-after-write;
14. falha composta exige compensação e releitura, com `FAIL_SAFE`/`PARTIAL_FAILURE_CRITICAL` equivalentes ao padrão B-006.

## 7. Coexistência

Os stores históricos podem continuar recebendo writes dos plugins legados sem colisão física porque o owner novo usa taxonomias próprias. Eles não são fallback canônico e não são sincronizados.

Na UI, valores legados podem ser exibidos apenas como **Referência legada — não canônica**, escapados e sem preseleção automática.

## 8. Conceitos adiados

Continuam fora do primeiro slice:

- serviço;
- serviço afetado;
- tecnologias;
- sistemas envolvidos;
- keywords;
- versões.

`service` não é mesclado com `affected_service`; `technologies` não é mesclado com `systems_involved`.

## 9. Fora do escopo

Review/Governança/AI READY; Content Extractor; Search/Golden Queries; Analytics; jobs/queue/indexação; tabela própria; REST/AJAX/SPA; Foundry/LLM/embeddings/vector/rerank/agentes; migração destrutiva; remoção de GRE/KB2Ops/ASI; cutover produtivo.

## 10. Gates

- **C-001 Profiling:** PASS — evidência real coletada.
- **C-010 Primitive:** PASS — Taxonomy API para os quatro conceitos do slice, sem migração automática.
- **G-001 Editorial:** zero write editorial e regressão SPEC-001.
- **G-030 Classification:** contratos, cardinalidade, diff, read-after-write, compensação e referência legada.
- **G-070 Segurança:** capability, nonce, IDOR, allowlist, XSS, mass assignment.
- **G-110 UI/UX:** shell wp-admin, labels, teclado, viewport e feedback.
- **G-130 Lifecycle/package:** activation/deactivation/package sem ferramentas temporárias.

## 11. Critérios de NO-GO

- criação automática de termos a partir do legado;
- write em `_bdc_es_*`/`_kb2ops_*` pela nova camada;
- dual-write permanente;
- assignment de termo inexistente ou de taxonomy errada;
- bypass de capability do post;
- mutação por GET;
- write em `_elementor_data`, `post_content` ou `post_title`;
- alteração do comportamento aprovado do Summary.

## 12. Próximo passo

Fechar S002 em tarefas/matrizes e iniciar S003/T030 implementando somente as quatro taxonomias e a superfície classificatória integrada ao editor atual, preservando `0.1.0-rc.1` como baseline de regressão.