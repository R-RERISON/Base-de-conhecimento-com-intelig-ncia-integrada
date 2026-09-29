# G-585 — ASI Independence / Decommission Readiness Contract v1

**Status:** FROZEN para implementação/homologação  
**Data:** 2026-09-20  
**SPEC:** SPEC-005  
**ADR:** ADR-005-001 — Zero Runtime Dependency on ASI  
**Gate anterior:** G-580 PASS/CLOSED  
**Gate seguinte:** G-590 RC

## 1. Objetivo

Provar que a Search lexical BDC permanece integralmente operacional quando o Advanced Search Intelligence (ASI) está ausente/desativado, sem usar classes, funções, hooks, options, tabelas, ranking, índice, queue, telemetry, embeddings ou qualquer storage ASI como dependência runtime.

G-585 é uma prova de independência, não uma operação de desinstalação do legado.

## 2. Princípios

1. o runner **não pode desativar ou remover ASI automaticamente**;
2. se ASI estiver ativo, G-585 deve bloquear/falhar de forma explícita;
3. tabelas/options ASI legadas podem continuar fisicamente existentes; presença física não é dependência;
4. nenhuma tabela/option ASI pode ser consultada para Search, Golden, rebuild, lifecycle ou rollback;
5. remoção física do ASI e de seus dados é operação futura, separada e explicitamente autorizada;
6. WordPress permanece fonte editorial e autoridade de visibilidade;
7. nenhuma alteração em ranking/normalizer/document/result é autorizada por este gate.

## 3. T585 — Static runtime scan

Escopo: arquivos PHP BDC efetivamente carregados no request do runner, excluindo somente o próprio runner diagnóstico G-585.

Deve provar ausência de referências runtime a:
- prefixos legados `asi_*`;
- prefixos legados `asi4_*`;
- slug/símbolo Advanced Search Intelligence;
- SQL/tabelas/options/hooks/funções/classes com esses identificadores.

O bootstrap pode conter apenas o nome da build flag G-585; essa identificação do próprio gate não é dependência ASI e deve ser normalizada antes do scan.

Também deve inspecionar símbolos e hooks carregados no request e bloquear se houver símbolos/hook names legados ativos.

## 4. T586 — ASI desativado em homologação

O runner deve ler:
- `active_plugins`;
- `active_sitewide_plugins` quando multisite.

Se detectar o plugin ASI ativo, deve:
- registrar o(s) basename(s);
- não executar rebuild;
- não executar Golden;
- retornar G-585 bloqueado;
- instruir desativação manual e nova execução.

O runner é proibido de chamar `deactivate_plugins()`, remover arquivos ou alterar options de ativação.

## 5. T587 — Search + Golden sem ASI

Com T585/T586 válidos:
- executar probes Search BDC via `Search_Service`;
- exigir `projection_like` e ausência de technical/invalid state;
- executar `Golden_Gate_Runner_G550::run()`;
- exigir status PASS;
- blocking_failed=0;
- technical_failed=0;
- technical_errors=[];
- Golden/Challenge continuam usando resources versionados BDC.

## 6. T588 — Rebuild BDC sem ASI

Executar `Search_Rebuild_Service::rebuild()` explicitamente e exigir:
- PASS;
- Projection final ready;
- corpus_count = row_count;
- pass2 written=0;
- pass2 no_change=corpus_count;
- determinism mismatch=0;
- errors=[];
- throwables=[].

Nenhum rebuild pode consultar storage ASI.

## 7. T589 — Rollback/lifecycle sem ASI

Provar no mesmo ambiente:
- `Search_Lifecycle::prepare_schema()` não executa rebuild implícito;
- kill switch `bdc_kb_search_enabled=false` retorna `wordpress_fallback/search_module_disabled`;
- `Search_Lifecycle::deactivate()` é não destrutivo;
- Projection/state permanecem disponíveis após a simulação de deactivation;
- rollback não requer ASI.

## 8. T589.1 — Evidence dependency-zero

Relatório JSON deve registrar no mínimo:
- ambiente/version;
- static scan e arquivos examinados;
- status de ativação legacy;
- símbolos/hooks legacy detectados;
- Search probes;
- Golden summary;
- rebuild summary;
- lifecycle/rollback summary;
- versões normalizer/document/ranker/result;
- errors/throwables;
- gate_result T585–T589.2.

Não registrar identidade, IP, sessão ou queries de usuário. As probes usam apenas queries técnicas congeladas do projeto.

## 9. T589.2 — G-585 PASS

PASS somente quando:
- T585=true;
- T586=true;
- T587=true;
- T588=true;
- T589=true;
- errors=[];
- throwables=[];
- ranker permanece `lexical-ranker-v1.0.0`;
- nenhum write editorial é introduzido.

PASS autoriza preparação do G-590, mas não merge nem produção.

## 10. Segurança e mutações

Runner:
- POST;
- nonce;
- `manage_options`;
- sem rede externa;
- sem deactivation automática de plugin;
- sem DROP/TRUNCATE/DELETE de legado;
- writes permitidos somente na Search Projection BDC e Option de estado durante rebuild explícito.

## 11. Falhas esperadas

### ASI ativo
`BLOCKED_LEGACY_ACTIVE`, G-585 permanece OPEN.

### Static scan encontra dependência
`FAIL_DEPENDENCY_FOUND`, nenhuma evolução para RC.

### Golden/rebuild/lifecycle falha
`FAIL`, G-585 permanece OPEN.

## 12. Critério de fechamento

Somente evidência ambiental em homologação com ASI manualmente desativado e T585–T589.2 PASS fecha G-585.
