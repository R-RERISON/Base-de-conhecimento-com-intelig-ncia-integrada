# Pesquisa Consolidada — SPEC-000

## Objetivo

Registrar fatos comprovados e decisões documentais do cruzamento/revisões. Hipótese não vira fato sem evidência versionada; compatibilidade, provider e infraestrutura derivada não viram owner permanente.

## 1. Baselines fixadas

- KB2Ops `0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.
- ASI `4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.
- GRE `0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365`.

## 2. Estado final do cruzamento T050–T059

Artefato executivo: `matriz-paridade-futura.md`.

- WordPress/Elementor são fonte editorial.
- owner lógico único por conceito.
- Content Extractor único para downstream.
- Search Retrieval Projection `post|item` é a única família própria aprovada.
- Analytics e durable queue postergados.
- Golden é QA governada/release evidence.
- IA/vetor opcionais/degradáveis.
- nenhum runtime criado.

## 3. T090 — revisão WordPress-first

Artefato: `revisao-wordpress-t090.md`.

Resultado: **PASS; 0 findings bloqueantes**.

### Evidência oficial WordPress revalidada em 2026-09-14

- `register_meta()` suporta tipo, sanitização, autorização e `revisions_enabled` para post meta; o argumento de revisions existe desde WP 6.4.
- Taxonomy API continua primitive nativa para classificação/agrupamento reutilizável.
- WP-Cron é scheduler disparado por page load e não oferece semântica de fila durável.
- Site Health aceita checks próprios diretos/assíncronos.
- WordPress HTTP API (`wp_remote_post`) permanece primitive adequada para integração HTTP externa, retornando `WP_Error` em falha.

Referências:

- https://developer.wordpress.org/reference/functions/register_meta/
- https://developer.wordpress.org/plugins/taxonomies/
- https://developer.wordpress.org/plugins/cron/
- https://developer.wordpress.org/reference/hooks/site_status_tests/
- https://developer.wordpress.org/reference/functions/wp_remote_post/

## 4. Decisões confirmadas por T090

- Summary/Review não precisam tabela própria.
- Taxonomy/Metadata continuam suficientes para Classificação.
- Search Knowledge/Golden devem começar em entidades internas WordPress-first.
- Site Health vence dashboard técnico duplicado.
- admin-post continua baseline; AJAX somente com live UX; REST sem consumidor continua negado.
- WP-Cron não substitui queue.
- Search Retrieval Projection própria permanece justificada pela combinação Elementor-aware + item identity + FULLTEXT/ranking dedicado.
- Foundry/HTTP provider deve permanecer adapter; SDK não é necessário por default.

## 5. Simplificações novas

1. Não usar simultaneamente bounded review history e meta revisions para a mesma finalidade sem requisito explícito.
2. Não criar admin CRUD/tabela própria para Search Knowledge/Golden antes de esgotar `WP_Post` interno + metadata/revisions.

## 6. Investigações encaminhadas

- versão mínima WordPress quando algum slice depender de meta revisions;
- exposição/rewrite/archive de taxonomias sistêmicas deve começar fail-closed;
- endpoint de provider configurável deve ser revisado em T092 para SSRF/host allowlist e uso de HTTP API segura.

Nenhuma delas bloqueia o baseline sem a capacidade correspondente.

## 7. Blockers continuam contextuais

B-001–B-007 mantêm o mapeamento T059. T090 não converteu nenhum deles em blocker global.

## 8. Próximo passo

**T091 — Revisão do Crítico de Simplicidade.**

Objetivo: tentar remover qualquer capacidade/camada que ainda não seja indispensável e confirmar que a arquitetura T059 é a menor suficiente.

## Estado

T050–T059 + T090 concluídos documentalmente. Nenhum runtime/schema/provider/vector foi criado. SPEC-001 continua bloqueada até T097.
