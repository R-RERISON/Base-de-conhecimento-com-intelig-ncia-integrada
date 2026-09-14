# Catálogo de Testes e Regressão — SPEC-000

> Documento incremental. Bloco ASI concluído; KB2Ops e Gerenciador de Resumo Executivo serão incorporados depois.

## Baseline ASI

`R-RERISON/Advanced-search-Intelligence@c0ddff89caad529ce1bcdc645eb795e4a9b187a1`

## Gate de release observado

`tools/validate-release.sh` executa lint PHP, syntax check JavaScript, suíte de regressão PHP/Node/Python, validação de package layout/contract e, quando existe, integridade SHA-256 de `release-manifest.json`.

Não há `.github` no baseline fixado; o gate versionado é local e reproduzível.

## Inventário da suíte

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
| `test-executive-summary-objective.php` | contrato `Objective_Provider` | SIM, adaptar ao contrato final |
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

## Contratos de regressão obrigatórios herdados conceitualmente

### Busca e ranking

- mudanças de normalização, parser, pesos, FULLTEXT/fallback, vocabulary, bindings, rules, vetor ou rerank não podem regressar Golden Queries silenciosamente;
- suíte Golden vazia não é PASS;
- evidência Golden fica stale se versão do ranker de posts **ou** itens mudar;
- warning não é blocker; expectativa `blocking` falha release.

### Conteúdo e Objective

- uma única representação canônica de conteúdo alimenta retrieval/trechos/IA;
- ausência do Objective canônico permanece ausência, sem síntese silenciosa a partir de corpo editorial;
- nenhuma lógica derivada pode escrever `_elementor_data` ou reescrever `post_content`.

### Segurança

- toda mutação administrativa: capability + CSRF nonce;
- tracking público: nonce + rate limit + target HMAC;
- cliente não define fatos autoritativos que o servidor já conhece;
- replay exato de interação é idempotente;
- journey inválida/oversized falha fechada;
- cache de resposta nunca carrega tracking token/event identity compartilhado.

### Privacidade

- modo mínimo não deve persistir identidade/session/IP/UA;
- journey é HMAC, nunca valor cru;
- export de qualidade é redigido;
- a política de **query text** deve ser redesenhada, pois ASI minimal ainda persiste o termo pesquisado.

### Operação

- activation não executa trabalho destrutivo/pesado indiscriminado;
- indexação assíncrona é idempotente e observável;
- job concluído, quando reaberto por novo evento, ganha novo orçamento de retry;
- estados impossíveis são recuperados sem reabrir dead/error indevidamente;
- telemetria falha sem derrubar a busca;
- workloads gerenciais têm hard limits e truncamento é informado.

### Package/release

- ZIP WordPress instalável com raiz correta;
- lint PHP/JS;
- package contract;
- manifest/hash quando adotado;
- benchmark de staging é distinto de teste estrutural de limites.

## Estratégia para o novo plugin

Prioridade de teste futura:

1. **unitários puros** para normalização, scoring, identity, privacy e state transitions;
2. **integração WordPress** para hooks, metadata/taxonomies, indexação, capabilities, nonces, Site Health e Elementor extractor;
3. **Golden Queries** contra base controlada;
4. **E2E admin/público** para busca, curadoria, simulação/Apply e acessibilidade;
5. **performance** com corpus realista;
6. **package/install/upgrade/rollback**.

Testes por inspeção textual podem existir apenas como guard simples para invariantes arquiteturais difíceis de observar, mas não devem ser o mecanismo principal de qualidade.

## Status

O inventário de testes do ASI é suficiente para fechar T032/T033. Nenhum teste foi executado no novo repositório porque ainda não existe runtime; nesta SPEC os testes foram **inventariados como contratos de comportamento**.