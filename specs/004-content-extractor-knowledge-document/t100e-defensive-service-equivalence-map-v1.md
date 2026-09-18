# T100E-E5 — Defensive Service Equivalence Map v1

**Status:** PASS ANALÍTICO / CONSOLIDAÇÃO CONSERVADORA AUTORIZADA PELO GATE  
**Base:** build `0.4.0-g245-consolidation-t100e.1` + source branch

## Conclusão executiva

As famílias Block e Elementor compartilham padrões, mas **não são drop-in equivalents**. A família Core Blocks é a linha canônica do produto. A família de migração Elementor não possui consumidor externo ativo no runtime atual e pode sair do carregamento ativo, permanecendo no source tree como histórico/engenharia.

O `Elementor_Adapter` **não faz parte dessa retirada**: ele continua necessário para leitura de conteúdo legado enquanto houver dependência Elementor.

## Dependência observada no runtime candidate

Consumidores da família histórica Elementor:

- `Elementor_Migration_Journal` → somente `Elementor_Migration_Journal_Store`;
- `Elementor_Migration_Journal_Store` → nenhum consumidor de produto;
- `Elementor_Migration_Lock` → nenhum consumidor;
- `Elementor_Stale_Source_Guard` → somente `Elementor_Migration_Dry_Run`;
- `Elementor_Migration_Dry_Run` → nenhum consumidor;
- `Elementor_Projection_Plan` → somente `Elementor_Migration_Dry_Run`;
- `Elementor_Gateway` → somente `Elementor_Migration_Dry_Run`;
- `Elementor_Canary_Readiness` → nenhum consumidor;
- `Elementor_Migration_Batch_Plan` → nenhum consumidor.

Portanto, a família forma uma ilha histórica carregada pelo bootstrap, mas não alcançada pela Workspace nem pelo fluxo Core Blocks.

## Matriz de equivalência

| Serviço | Similaridade | Diferenças materiais | Veredito |
|---|---|---|---|
| Journal | estado/lifecycle semelhante | Block usa `fidelity_hash + serialization_hash + source_kind` e acompanha hashes finais de `post_content` e `_elementor_data`; Elementor usa `source_hash + projection_hash` e um `source_hash_after` | **Não unificar agora**. Block é canônico; há hardening a absorver do legado. |
| Journal Store | append-only postmeta, readback, cadeia | Elementor valida transição pai→filho e envelope externo com mais detalhe; Block usa family marker e identidade Core Blocks, porém possui validações menos explícitas | **Não substituir**. Criar hardening específico no Block antes de qualquer extração genérica. |
| Lock | acquire/inspect/release, TTL 300/900, token, manage_options | Block adiciona `family=core_blocks` e meta-key própria; comportamento restante é praticamente equivalente | **Equivalente funcional**. Em vez de abstrair agora, aposentar o Lock Elementor do runtime ativo. |
| Stale Guard | bloqueio fail-closed | Block compara `fidelity_hash`, `source_kind`, `post_content_sha256` e `elementor_data_sha256`; Elementor compara apenas Knowledge Document `source_hash` | **Não equivalente**. Block é materialmente mais forte para fidelity editorial. |
| Dry Run | zero-write + hash determinístico | Block usa Migration Fidelity Source + lossless serializer + `parse_blocks` + editorial parity + stale guard; Elementor usa Projection Plan + Gateway + KD source hash | **Não equivalente**. Block supersede o caminho antigo para o destino Core Blocks. |
| Batch Plan | algoritmo de cohort/cursor praticamente igual | identidade Block = fidelity/serialization/dry-run; Elementor = source/projection/dry-run | **Algoritmicamente equivalente, semanticamente diferente**. Nenhum batch writer é autorizado; manter fora de decisões de write. |

## Hardening gap identificado — HE5-001

O `Elementor_Migration_Journal` / Store histórico contém validações explícitas que o contrato Block atual não replica integralmente:

- validação explícita de `run_id`, `post_id` e estado permitido no record;
- validação explícita pai→filho no store;
- conferência mais ampla do envelope externo (`state`, `journal_id`, `run_id`);
- readback matcher mais completo.

Isso **não invalida T099/T100D**, cujas execuções passaram com hash/readback/journal/lock e evidência ambiental. Porém, antes do próximo writer persistente, o Block Journal/Store deve ser endurecido e testado contra os eventos já existentes do post 358.

## Decisão E5.1

Retirar do **runtime ativo** os seguintes contratos históricos, preservando arquivos no source tree:

- `class-elementor-projection-plan.php`;
- `class-elementor-gateway.php`;
- `class-elementor-migration-journal.php`;
- `class-elementor-migration-journal-store.php`;
- `class-elementor-stale-source-guard.php`;
- `class-elementor-migration-dry-run.php`;
- `class-elementor-migration-batch-plan.php`;
- `class-elementor-canary-readiness.php`;
- `class-elementor-migration-lock.php`.

### Não retirar

- `class-elementor-adapter.php` — dependência real para leitura de legado;
- família Block Migration — contrato canônico vigente;
- `Production_Preflight` — permanece até substituto ambiental equivalente.

## Guardrails

- zero write editorial;
- zero delete de source histórico;
- nenhum rename de meta-key;
- nenhum merge de journals existentes;
- nenhum rewrite de audit trail;
- nenhum batch writer;
- PR #4 permanece DRAFT.

## Próximo passo

1. bootstrap deixa de carregar a ilha histórica Elementor;
2. runner passa a falhar se qualquer um desses arquivos voltar ao runtime ativo;
3. builder determinístico gera candidate T100E.2;
4. depois, **HE5-001**: hardening do Block Journal/Store com read-only compatibility check antes de novo writer.
