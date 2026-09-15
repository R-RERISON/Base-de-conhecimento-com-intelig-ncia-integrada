# Matriz de Evidência — SPEC-002

| Gate | Evidência mínima | Estado |
|---|---|---|
| C-001 Profiling | JSON real, cobertura/cardinalidade/representação/overlaps | **PASS** — 622 posts / 36 rows / read-only |
| C-010 Primitive | decisão Taxonomy vs Meta por conceito | **PASS** — Taxonomy API para 4 conceitos; sem migração automática |
| G-001 Editorial | zero write em título/post_content/_elementor_data | **PASS** — técnico + HTTP + browser |
| G-030 Classification | single/multi, empty/remove, diff, reread, legacy advisory | **PASS** — `0.2.0-dev.3`, 20/20 |
| B-006 Consistência | FAIL_SAFE + PARTIAL_FAILURE_CRITICAL | **PASS** — `0.2.0-dev.3` |
| G-070 Segurança | method, nonce, post-bound nonce, allowlist, XSS, scope, capability, PRG | **PASS** — `0.2.0-dev.7`, 18/18 |
| G-110 UI/UX | shell, labels, foco/teclado, narrow viewport, feedback | **PASS** — `0.2.0-dev.8`; min 492x660 |
| Regressão SPEC-001 | Summary preservado nos writes de Classificação | **PASS** — técnico + HTTP + browser |
| G-130 Lifecycle | package limpo + deactivate/reactivate + estado preservado | **PREPARADO / PENDENTE execução real do `0.2.0-rc.1`** |

## Evidências finais S004

### Diagnóstico técnico — `0.2.0-dev.3`

- schema `1.0.1`;
- 20 PASS / 0 FAIL;
- G-030 PASS;
- B-006 FAIL_SAFE PASS;
- B-006 PARTIAL_FAILURE_CRITICAL PASS;
- G-001 PASS;
- regressão Summary PASS;
- 8 termos + post fixture removidos;
- resíduos: 0/0.

SHA-256 do JSON: `92861b28cff90fb1595f11d292f780e4b54d07c54e738dedcdf1d802221101f8`.

### Segurança HTTP — `0.2.0-dev.7`

- schema `1.0.3`;
- 18 PASS / 0 FAIL;
- `GET` -> 405;
- nonce ausente/inválido/post-bound;
- payload e mass assignment;
- XSS em term ID;
- page/ID inexistente;
- capability no handler real por negação assinada exclusiva de homologação;
- POST válido -> 302 / `saved`;
- estado editorial, legado e Summary preservados;
- resíduos: 0 posts / 0 termos / 0 usuários.

SHA-256 do JSON: `6f739f4b13dd3c941587fece4d170b2781987b992bd7ee96fab64465afa00fa2`.

Observação ambiental: roles reduzidas eram interceptadas por `/acesso-restrito/` antes de `admin-post.php`; por isso o ramo capability foi exercitado com instrumentação assinada, limitada à fixture e removida do RC.

### Browser acceptance — `0.2.0-dev.8`

- Edge 153 / Windows;
- viewport final e mínimo observado: 492x660;
- 2 manual PASS / 0 manual FAIL;
- 0 auto FAIL;
- overall PASS;
- shell wp-admin sem sidebar secundária;
- quatro selects e cardinalidade correta;
- feedback de sucesso;
- PRG PASS;
- Summary/legado/editorial preservados;
- post + 7 termos removidos;
- resíduos: 0/0.

SHA-256 do JSON: `308fd4bde646e4963457d7e3605f5871827d7d800c3d4f21378a13108f7822ea`.

## RC limpo

`0.2.0-rc.1` contém somente runtime permanente. Foram removidos runners técnico/HTTP/browser, flags de homologação e instrumentação force-deny. O único gate restante é G-130 em WordPress real.
