# Matriz de Compatibilidade — SPEC-004 / G-245

**Versão:** `1.0.0`  
**Estado:** congelada para o ambiente local de homologação em 2026-09-16  
**Escopo:** diagnóstico read-only; não autoriza migration editorial ou writer Elementor.

## Baseline comprovado

| Componente | Versão observada | Estado | Evidência |
|---|---:|---|---|
| WordPress | `7.0` | `compatible` para extractor, Knowledge Document e preflight | G-240/G-245 browser acceptance |
| PHP | `8.5.10` | `compatible` para os testes automatizados disponíveis | lint + suíte unitária em homologação |
| Elementor | `4.1.0` | `compatible` para leitura; writer `blocked` até gateway e round-trip | G-240 + contrato Elementor |
| MariaDB | `12.3.3-MariaDB-ubu2404-log` | `compatible` para o runtime atual; schema de migration não avaliado | G-245 preflight |
| Plugin BdC | `0.4.0-smoke.2` | `compatible` para G-220/G-230/G-240; build temporário | testes e browser acceptance |

## Contratos por capacidade

| Capacidade | Homologação | Produção | Condição de avanço |
|---|---|---|---|
| Content Extractor read-only | `PASS` | `NOT_VERIFIED` | repetir smoke no ambiente alvo |
| Knowledge Document in-memory | `PASS` | `NOT_VERIFIED` | repetir smoke e confirmar ausência de storage |
| Projection Plan read-only | `NOT_RUN` | `NOT_RUN` | implementar T084 |
| Elementor Gateway | `NOT_RUN` | `BLOCKED` | implementar T085 e matriz de API Document |
| Writer Elementor | `BLOCKED` | `BLOCKED` | gateway, journal, rollback e canário aprovados |
| Migration editorial | `BLOCKED` | `BLOCKED` | dry-run, stale-source guard, batches e rollback |

## Regras de compatibilidade

1. A versão Elementor `4.1.0` é baseline de leitura, não autorização de escrita.
2. Versão Elementor ausente, divergente ou desconhecida deve falhar fechado para migration.
3. WordPress/PHP fora desta matriz exige novo preflight e nova evidência; não há compatibilidade implícita.
4. O banco MariaDB foi identificado pelo banner real do servidor. Nenhuma migration de schema foi executada.
5. Plugins de conteúdo, shortcodes e widgets ativos são contexto de leitura; sua compatibilidade com writer ainda é `NOT_VERIFIED`.
6. O plugin pode ser instalado/atualizado sem percorrer ou alterar conteúdo editorial.

## Critérios para ampliar a matriz

Uma nova linha só pode sair de `NOT_VERIFIED` quando houver:

- versão exata capturada por preflight;
- teste automatizado ou browser acceptance aplicável;
- contrato de integração explícito;
- comportamento de falha e rollback documentados;
- nenhuma escrita editorial implícita.

## Decisão atual

**T083: PASS para congelamento da baseline de homologação.**

Isso não representa GO de produção. O próximo slice é o `Projection Plan` read-only por post; o `Elementor_Gateway` e qualquer writer permanecem bloqueados.