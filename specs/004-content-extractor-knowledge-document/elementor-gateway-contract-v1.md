# Elementor Gateway Contract v1 — G-245 / T082

**Status:** FROZEN PARA T082  
**Contrato:** `1.0.0`  
**Matriz:** `g245-compatibility-matrix-v1`  
**Build:** `0.4.0-g245-gateway.1`

## 1. Objetivo

Criar uma fronteira única, fail-closed e auditável para qualquer futura mutação Elementor sem implementar writer real.

T082 existe para impedir que código posterior alcance um caminho mutável sem atravessar gates explícitos. Este contrato não autoriza persistência editorial.

## 2. Invariantes

- WordPress/Elementor continuam fonte editorial.
- Knowledge Document e Projection Plan permanecem derivados/read-only.
- `writer_allowed=false` em T082.
- `migration_execution_allowed=false` em T082.
- nenhum método de escrita em `_elementor_data` ou `post_content` é implementado.
- nenhuma execução de shortcode ou chamada externa é introduzida.
- nenhuma alteração visual é introduzida; UX-002 permanece autoridade integral.

## 3. Version gate

Versão homologada no baseline G-245: **Elementor `4.1.0`**.

Decisão:

| Runtime Elementor | Estado | Política |
|---|---|---|
| ausente | `blocking` | writer proibido |
| `4.1.0` | `compatible_read_only` | ainda sem writer |
| diferente de `4.1.0` | `review_required` | writer proibido até homologação explícita |

O Gateway não usa faixa semântica ampla. A primeira versão é intencionalmente exata para impedir compatibilidade presumida.

## 4. Quatro gates cumulativos

Qualquer futura ação mutável dependerá cumulativamente de:

1. version gate compatível;
2. feature flag `BDC_KB_ELEMENTOR_WRITER_ENABLED` explicitamente habilitada;
3. capability `manage_options`;
4. autorização de fase em código.

Em T082, o quarto gate é hard-coded como `false`. Portanto, mesmo que os três primeiros sejam satisfeitos, `writer_allowed` continua `false`.

## 5. Contrato de segurança futuro

A futura ação administrativa fica reservada como:

- action: `bdc_kb_elementor_write`;
- capability: `manage_options`;
- nonce field: `_bdc_kb_elementor_nonce`;
- nonce action prefix: `bdc_kb_elementor_write_`;
- método HTTP futuro: POST;
- nonce nunca substituirá capability.

T082 não registra handler para essa action.

## 6. Contrato com Projection Plan / stale-source

Qualquer futura execução deverá receber, no mínimo:

- `post_id`;
- `source_hash_before`;
- `projection_hash`.

`source_hash_before` é obrigatório para o stale-source guard de T084. Nenhum writer futuro poderá operar somente por `post_id`.

## 7. API do Gateway

`Elementor_Gateway::inspect_runtime()` lê somente o ambiente e retorna avaliação.

`Elementor_Gateway::assess()` é puro/determinístico e classifica versão, feature flag, capability e hard gate da fase.

`Elementor_Gateway::assert_writer_allowed()` é o guard central. Em T082 ele sempre retorna `WP_Error` para qualquer tentativa de writer.

Código futuro não deve duplicar regras de compatibilidade fora do Gateway.

## 8. Safety contract

O estado retornado pelo Gateway deve manter:

- `persists_state=false`;
- `writes_post_content=false`;
- `writes_elementor_data=false`;
- `calls_external_network=false`;
- `executes_shortcodes=false`.

## 9. Testes T082

Teste local: `tests/unit/spec004-elementor-gateway.php`.

Cobertura mínima:

- versão homologada;
- versão desconhecida;
- Elementor ausente;
- feature flag off/on;
- capability deny/allow;
- impossibilidade de bypass do hard gate T082;
- contrato de nonce/capability/action;
- `source_hash_before` obrigatório para gates futuros;
- zero-write safety;
- guard retorna `WP_Error`.

Resultado local da implementação inicial: **45 assertions PASS + PHP lint PASS**.

## 10. Fora de escopo T082

- journal/rollback — T083;
- stale-source enforcement — T084;
- dry-run mutável — T085;
- batches retomáveis — T086;
- canário/rollback — T087;
- runbook — T088;
- autorização explícita do writer — T089.

**PASS de T082 não autoriza writer.**
