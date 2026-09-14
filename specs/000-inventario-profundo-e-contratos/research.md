# Pesquisa Consolidada — SPEC-000

## Objetivo

Registrar fatos comprovados e decisões documentais do cruzamento e revisões. Hipótese não vira fato sem evidência versionada; simplicidade não remove garantias comprovadas.

## 1. Baselines fixadas

- KB2Ops `0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.
- ASI `4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.
- GRE `0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365`.

## 2. Arquitetura T059

- WordPress/Elementor = fonte editorial.
- um owner lógico por conceito.
- uma Search Retrieval Projection própria e reconstruível é a única família persistente própria aprovada.
- Analytics, durable queue, vetor/semantic/rerank/agentes permanecem postergados.
- IA é assistiva e retrieval-first.

## 3. T090 — WordPress-first

Resultado: PASS, zero bloqueantes.

Confirmou Metadata/Taxonomy/Options/Site Health/admin-post/WP-Cron trigger/HTTP API como primitives preferenciais e manteve Search Retrieval Projection como exceção justificada.

## 4. T091 — Crítico de Simplicidade

Artefato: `revisao-simplicidade-t091.md`.

Resultado: **PASS, zero bloqueantes após simplificações/postergações**.

### Achado principal

A arquitetura aprovada não deve ser construída como fundação antecipada. O primeiro runtime deve ser um único fluxo homologável, com infraestrutura somente quando consumida.

### Recomendação provisória de primeira SPEC

**Core mínimo + Summary narrativo** (`objective`, `escalation`, `important`).

Razões:

- menor bounded context comprovado;
- WordPress Metadata API atende;
- não depende de Search/IA/queue/Analytics;
- evita B-001/B-002/B-003/B-004/B-005/B-007 no primeiro slice;
- permite provar capability/nonce/sanitização/read-after-write/lifecycle/UI sem big-bang.

T094/T095/T097 ainda devem validar essa recomendação.

## 5. Simplificações T091

- Content Extractor somente junto do primeiro consumidor real.
- DS incremental por tela; sem biblioteca completa antecipada.
- Settings somente se consumidos.
- Review em slice separado.
- Classificação por eixo/slice; não todas as taxonomias juntas.
- histórico sem duplicação.
- Site Health somente para capacidades existentes.
- Search post-level antes de item/deep-link quando suficiente.
- Search Knowledge após ranker lexical mínimo, salvo Golden demonstrar necessidade.
- Golden obrigatório para Search, mas UI CRUD completa é opcional/posterior.
- IA P1 apenas após owner estável; sem multi-provider abstraction antecipada.

## 6. Complexidades descartadas no baseline

- event bus próprio;
- repository layer genérico;
- DI/service container genérico;
- cache service genérico;
- migration framework genérico;
- Operations Center genérico;
- compatibility adapter framework;
- provider factory multi-vendor sem segundo caso.

Esses itens só podem reaparecer com problema concreto e ADR.

## 7. Capacidades mantidas/postergadas

Mantidas quando houver consumidor:

- Search Retrieval Projection;
- Golden Queries;
- capability/nonce/security;
- rollback/coexistência;
- Content Extractor para Search/RAG.

Postergadas:

- item/deep-link até necessidade;
- Search Knowledge até problema observado;
- Analytics/queue;
- IA/RAG/vector/semantic/rerank/agentes.

## 8. Próximo passo

**T092 — Revisão de Segurança.**

A revisão deve transformar superfícies futuras em threat model/gates, especialmente capabilities, CSRF/IDOR, output escaping, shortcodes/compatibilidade, SSRF/provider/secrets/data egress, Search scope e ações destrutivas.

## Estado

T050–T059 + T090 + T091 concluídos documentalmente. Nenhum runtime/schema/provider/vector foi criado. SPEC-001 segue bloqueada até T097.