# T100E — Runtime Inventory Baseline v1

**Status:** BASELINE FROZEN  
**Branch:** `spec004-g245-production-readiness`  
**Trigger:** T100D PASS ambiental

## Resumo quantitativo

- 65 arquivos PHP no pacote atual (bootstrap + includes).
- 48 `require_once` incondicionais no bootstrap.
- 4 assets CSS e 1 asset JS.
- T100D executor permanece presente/habilitado no build imediatamente pós-PASS.
- `Production_Preflight` permanece habilitado por flag.
- múltiplos smokes/diagnósticos permanecem fisicamente dentro do ZIP mesmo com flags OFF.

## Classificação arquitetural inicial

### Product

Runtime diretamente associado ao produto final:
- meta / Summary / Classification / Review;
- Content Source / adapters / extractor;
- Knowledge Document e estrutura semântica;
- Migration Fidelity Source;
- Core Block lossless serializer;
- Core Block parity;
- Post Activity Registry;
- Post Management Context / Activities;
- Post Core Blocks Activity;
- Admin Page / Visual Foundation / Plugin.

### Defensive product services

Contratos que sustentam writes post-scoped:
- Block Migration Journal + Store;
- Block Migration Lock;
- Block Migration Stale Source Guard;
- Block Migration Dry Run;
- Block Migration Batch Plan (uso futuro deve ser revisado; nenhum batch writer autorizado).

### Engineering / test_only

Arquivos de smoke, diagnóstico, acceptance e perfil não devem constituir a UX final:
- `*-smoke.php`;
- `class-final-structure-diagnostic.php`;
- `class-pipeline-structure-diagnostic.php`;
- `class-real-content-acceptance*.php`;
- `class-content-profile.php`;
- `class-production-preflight.php`.

### legacy_compat / historical engineering

Família Elementor preservada por compatibilidade e histórico:
- Elementor Adapter permanece necessário enquanto houver conteúdo Elementor;
- Elementor Projection Plan / Gateway / Migration Journal / Store / Stale Guard / Dry Run / Batch Plan / Canary Readiness / Lock precisam ser reavaliados individualmente;
- nenhum deles pode ser removido por nome apenas; cada dependência deve ser comprovada.

## Achados prioritários

### F-001 — Test tooling dentro do artefato de produto

Smokes e diagnósticos continuam no ZIP de instalação. Mesmo desligados, ampliam superfície, tamanho e complexidade de release.

**Direção:** separar `engineering/` do artefato final somente após runner equivalente e paridade.

### F-002 — 48 requires incondicionais

Grande parte das classes é carregada em todo bootstrap do plugin, inclusive contratos admin/históricos.

**Direção:** classificar e migrar para carregamento por capability/contexto sem alterar comportamento.

### F-003 — Duas famílias defensivas

Há paralelos Elementor/Core Blocks para journal, store, stale guard, dry-run, batch plan e lock.

**Direção:** mapear equivalência antes de qualquer consolidação; nenhum rename/refactor sem teste de contrato.

### F-004 — Production Preflight ativo

`BDC_KB_SPEC004_G245_PREFLIGHT_BUILD=true` mantém ferramenta de engenharia habilitada no runtime atual.

**Direção:** provar que a Workspace + runner substituem a necessidade e então desligar.

### F-005 — T100D one-shot executor ainda habilitado

O executor T100D continua hard-scoped ao post 358 e authorization_id já consumido.

**Direção:** no primeiro build de consolidação, desligar a flag e tornar a classe condicional/test-only, preservando evidência e contrato.

### F-006 — Histórico de gates virou parte do source tree

T09x/T100x foram essenciais para descobrir a arquitetura, mas não devem ditar a estrutura permanente do produto.

**Direção:** manter evidências e contratos; migrar runners para camada de engenharia.

## Invariantes da consolidação

- zero alteração editorial em T100E;
- zero batch writer;
- post 358 permanece Core Blocks aplicado;
- Workspace continua canônica;
- Summary/Classificação/Review não mudam sem gate próprio;
- source mixed continua humano;
- Elementor Adapter permanece enquanto houver dependência;
- PR #4 permanece DRAFT.

## Próximo artefato

`tools/t100e/regression_runner.py` será o primeiro runner único e inicialmente fará somente verificações estáticas/local.
