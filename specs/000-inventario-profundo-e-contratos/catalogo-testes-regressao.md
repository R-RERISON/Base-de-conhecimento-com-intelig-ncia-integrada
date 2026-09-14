# Catálogo de Testes e Regressão — SPEC-000

> Documento incremental. Blocos ASI e Gerenciador de Resumo Executivo concluídos; KB2Ops ainda será incorporado antes do gate final.

## 1. Advanced Search Intelligence 4.6.8

Baseline: `R-RERISON/Advanced-search-Intelligence@c0ddff89caad529ce1bcdc645eb795e4a9b187a1`

### Gate de release observado

`tools/validate-release.sh` executa lint PHP, syntax check JavaScript, suíte de regressão PHP/Node/Python, validação de package layout/contract e, quando existe, integridade SHA-256 de `release-manifest.json`.

Não há `.github` no baseline fixado; o gate versionado é local e reproduzível.

### Inventário da suíte

| Teste/gate | Comportamento protegido | Portar? |
|---|---|---|
| `test-source-contracts.php` | clean runtime, ownership de hooks, ausência de legado, contratos públicos/admin | SIM, mas substituir parte dos string-tests por comportamento |
| `test-query-context.php` | normalização/plano de retrieval | SIM |
| `test-relevance-coverage.php` | cobertura/ranking | SIM + Golden |
| `test-table-metadata-cache.php` | cache de metadata/schema | somente se mecanismo sobreviver |
| `test-item-identity.php` | identidade estável de trecho | SIM |
| `test-item-extractor.php` | extração conservadora | SIM, contra Content Extractor novo |
| `test-item-relevance.php` | ranking de trechos | SIM |
| `test-anchor-navigation-467.php` | anchor exactness/fail-closed | SIM se anchors sobreviverem |
| `test-public-item-navigation-contract-467.php` | navegação pública de trechos | SIM |
| `test-search-response-quality.php` | estados/metadados de qualidade | SIM |
| `test-post-context.php` | Objective canônico; sem resumo inventado | SIM |
| `test-executive-summary-objective.php` | contrato `Objective_Provider` | SIM, adaptar ao store interno final |
| `test-base-reconciler.php` | reconciliação/cutover histórico | NÃO como implementação; preservar princípios se houver migração |
| `test-external-preflight-resume.php` | preflight retomável de consumidores | SIM se houver migração/coexistência |
| `test-search-intelligence-report.php` | analytics gerencial/privacy | SIM para comportamentos escolhidos |
| `test-telemetry-outcomes-46.php` | privacidade, journey, outcomes, HMAC, idempotência | SIM, alta prioridade |
| `test-public-search-journey-46.js` | live typing/journey/revision | SIM |
| `test-acceptance-guidance-467.php` | critérios de aceite manual | REDESENHAR |
| `test-search-outcomes-sql-46.php` | semântica SQL de outcomes | SIM se schema relacional sobreviver |
| `test-assisted-knowledge-46.php` | sugestões assistidas | SIM |
| `test-search-knowledge-workflow-026.php` | workflow de curadoria | SIM |
| `test-knowledge-curation-46.php` | capability/state/mutations | SIM |
| `test-golden-queries-46.php` | release gate de ranking | SIM, obrigatório |
| `test-quality-diagnostics-46.php` | health/redaction | SIM, adaptar Site Health |
| `test-migrations-46.php` | migrations ASI históricas | NÃO; criar testes do upgrade próprio no futuro |
| `test-partial-upgrade-46.php` | disponibilidade durante upgrade aditivo | SIM como princípio se houver schema próprio |
| `test-performance-bounds-46.php` | limites estruturais de workload | SIM + benchmark real |
| `test-admin-security-46.php` | capability/nonce/HMAC/trust boundaries | SIM, obrigatório |
| `test-post-install-orchestrator-461.php` | state machine de preparação/validação | REDESENHAR; não portar integralmente |
| `test-queue-lifecycle-466.php` | retry budget/recovery/audit | SIM se fila própria existir |
| `test-post-install-admin-461.php` | UI operacional pós-instalação | REDESENHAR |
| `test-activation-bootstrap-463.php` | activation/bootstrap seguro | SIM |
| `test-wordpress-package-layout.py` | ZIP/layout instalável | SIM |
| `test-package-contract.php` | composição do pacote | SIM |

### Contratos de regressão ASI herdados conceitualmente

#### Busca e ranking

- mudanças de normalização, parser, pesos, FULLTEXT/fallback, vocabulary, bindings, rules, vetor ou rerank não podem regressar Golden Queries silenciosamente;
- suíte Golden vazia não é PASS;
- evidência Golden fica stale se versão do ranker de posts **ou** itens mudar;
- warning não é blocker; expectativa `blocking` falha release.

#### Conteúdo e Objective

- uma única representação canônica de conteúdo alimenta retrieval/trechos/IA;
- ausência do Objective canônico permanece ausência, sem síntese silenciosa a partir de corpo editorial;
- nenhuma lógica derivada pode escrever `_elementor_data` ou reescrever `post_content`.

#### Segurança

- toda mutação administrativa: capability + CSRF nonce;
- tracking público: nonce + rate limit + target HMAC;
- cliente não define fatos autoritativos que o servidor já conhece;
- replay exato de interação é idempotente;
- journey inválida/oversized falha fechada;
- cache de resposta nunca carrega tracking token/event identity compartilhado.

#### Privacidade

- modo mínimo não deve persistir identidade/session/IP/UA;
- journey é HMAC, nunca valor cru;
- export de qualidade é redigido;
- a política de **query text** deve ser redesenhada, pois ASI minimal ainda persiste o termo pesquisado.

#### Operação

- activation não executa trabalho destrutivo/pesado indiscriminado;
- indexação assíncrona é idempotente e observável;
- job concluído, quando reaberto por novo evento, ganha novo orçamento de retry;
- estados impossíveis são recuperados sem reabrir dead/error indevidamente;
- telemetria falha sem derrubar a busca;
- workloads gerenciais têm hard limits e truncamento é informado.

---

## 2. Gerenciador de Resumo Executivo 0.6.0

Baseline: `R-RERISON/Gerenciador-de-Resumo-Executivo-da-Base-de-Conhecimento@1120a534d8eb2288460c2c675730deef0d67c365`

### Gate local/release

`tools/verify_local.py` é o gate canônico observado e executa:

1. `composer validate --strict`;
2. instalação de dependências (ou valida `vendor/` se skip explícito);
3. lint PHP;
4. WordPress Coding Standards;
5. PHPUnit;
6. smoke tests de package;
7. build determinístico;
8. descoberta de exatamente um ZIP;
9. SHA-256 do pacote;
10. relatório `dist/local-verification.json` com PASS/FAIL e evidências.

O build `tools/build_plugin.py` usa arquivos ordenados, timestamp ZIP fixo e valida a raiz/shape do pacote. O smoke test gera o pacote duas vezes e exige SHA idêntico.

Não há workflow de GitHub Actions no baseline fixado; o gate é deliberadamente local.

### Unitários GRE

| Teste | Contrato protegido | Portar? |
|---|---|---|
| `MetaContractTest.php` | oito metas exatas, sem título meta, registration args, sanitizer/auth | SIM, obrigatório |
| `SummaryStoreTest.php` | read/write, allowlist, capability, partial update, empty-delete, read-after-write | SIM, obrigatório |
| `AdminMenuTest.php` | menu/capability/estrutura admin | SIM, adaptar UI unificada |
| `AdminPageTest.php` | nonce, payload, server-side editor, segurança | SIM |
| `CoverageDashboardTest.php` | 0/8–8/8, published-only, prioridades, read-only | SIM + performance bound novo |
| `FrontendRendererTest.php` | shortcode current-post-only, escaping, no duplicate panel, no JS | SIM para contratos escolhidos |
| `PluginBootstrapTest.php` | single boot hook, Meta registration, `bdc_es_loaded` | SIM, adaptar bootstrap unificado |
| `ArchitectureGuardrailTest.php` | sem `_elementor_data`, sem tabela, sem REST aberto, oito metas exatas | SIM como guardrail secundário |

### Integração em WordPress real

#### `tests/Integration/spec001-wp-cli.php`

Protege:

- registro real das oito metas;
- `show_in_rest = false` e revisions off no baseline;
- leitura sem write;
- `post_title` como título;
- unicode/multiline/backslash;
- sanitização;
- partial update;
- no-op;
- empty => delete;
- post type;
- capability real de editor/subscriber.

#### `tests/Integration/spec002-wp-cli.php`

Protege:

- `admin_menu` e `admin_post` autenticado;
- ausência de handler nopriv;
- nonce vinculado ao post;
- edição por editor;
- progresso;
- rejeição de payload inválido/title injection;
- capability real.

**Direção:** testes de integração WordPress real devem ser referência prioritária no plugin unificado para metadata, capabilities, nonces e hooks.

### Package smoke

`tests/smoke/test_package.py` protege:

- versão consistente entre header/constant/readme;
- layout instalável;
- presença dos runtime files esperados;
- ausência de development-only dirs;
- ausência de JS que não existe no runtime;
- reprodutibilidade byte-a-byte;
- falha em version mismatch.

### Lacunas de teste GRE detectadas

#### REG-GRE-001 — atomicidade de persistência multi-campo

A suíte prova que **erros de validação** não produzem partial write, pois todos os campos são validados antes da mutação. Ela também prova falha de persistência em update/delete individual.

Não foi encontrada evidência de teste que force:

1. campo A persistir com sucesso;
2. campo B falhar na persistência/verificação;
3. verificar se A é revertido ou permanece.

Se o contrato futuro exigir atomicidade lógica de um submit multi-campo, este teste será obrigatório e a implementação deverá compensar writes anteriores.

#### REG-GRE-002 — evento pós-persistência/invalidação

O GRE não emite evento de mudança de Objective. Assim, não há teste de integração garantindo que alteração de Objective invalida/reindexa projections consumidoras.

No plugin unificado, deve existir regressão explícita: `Summary Store update confirmado -> evento de domínio -> projection marcada stale/enfileirada`, sem qualquer write editorial adicional.

#### REG-GRE-003 — bounded workload do Coverage Dashboard

Os testes cobrem correção funcional, mas não limitam o número total de posts lidos. O runtime usa `posts_per_page = -1`.

O futuro teste deve impor uma estratégia de workload limitado/paginado ou benchmark explícito, sem materializar tabela agregada antes de necessidade comprovada.

---

## 3. Contratos combinados ASI + GRE já comprovados

### Metadata/Objective

- título continua `post_title`;
- oito valores GRE são dados editoriais canônicos conhecidos;
- ausência do Objective não pode ser preenchida silenciosamente com corpo do artigo;
- read não pode criar metadata;
- change de Objective precisa invalidar projections derivadas;
- a integração ASI antiga por `Objective_Provider` está quebrada e deve virar contrato interno testado.

### Segurança

- `edit_post` por objeto é gate de metadata;
- nonce deve estar ligado ao objeto/ação;
- payload é allowlisted e sanitizado antes de write;
- frontend público não aceita `post_id` arbitrário só porque um shortcode existe;
- REST não deve ser aberto sem consumidor/requisito explícito.

### WordPress-first

- ausência de tabela/REST/AJAX/cron no GRE é um guardrail positivo, não uma limitação a ser “modernizada” automaticamente;
- infraestrutura própria só nasce quando comportamento, volume ou durabilidade exigirem.

### Release

- lint + coding standards + unit tests + integração WordPress + package smoke + build determinístico são contratos fortes;
- CI hospedado pode ser evolução, não pré-requisito para qualidade local reproduzível.

---

## 4. Estratégia para o novo plugin

Prioridade de teste futura:

1. **unitários puros** para normalização, scoring, identity, Summary Store e state transitions;
2. **integração WordPress real** para hooks, metadata/taxonomies, indexação, capabilities, nonces e Elementor extractor;
3. **Golden Queries** contra base controlada;
4. **E2E admin/público** para busca, curadoria, resumo e acessibilidade;
5. **performance** com corpus realista, inclusive dashboard/extractor;
6. **package/install/upgrade/rollback** determinísticos;
7. **contratos cross-module** para evento de metadata -> invalidação de índice.

Testes por inspeção textual podem existir como guard simples de invariantes arquiteturais, mas não substituem behavior/integration tests.

## Status

O inventário de testes do GRE é suficiente para fechar T045. O novo repositório ainda não possui runtime, portanto esta SPEC inventaria e classifica os testes; não há suite de runtime nova a executar.