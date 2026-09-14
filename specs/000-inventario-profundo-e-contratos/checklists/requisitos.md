# Checklist de Requisitos — SPEC-000

## Preparação, inventários e cruzamento

- [x] Repositórios/SHAs baseline registrados.
- [x] Constituição, Manifesto, agentes e skills preparados.
- [x] KB2Ops, ASI e GRE inventariados.
- [x] T050–T059 concluídas documentalmente.
- [x] Nenhum runtime novo criado durante T000–T092.

## Revisões finais

- [x] T090 — Arquiteto WordPress.
- [x] T091 — Crítico de Simplicidade.
- [x] T092 — Segurança. _(`revisao-seguranca-t092.md`)_
- [ ] T093 — QA/Regressão.
- [ ] T094 — Produto/Conhecimento.
- [ ] T095 — unknowns/blockers.
- [ ] T096 — relatório final.
- [ ] T097 — GO/NO-GO SPEC-001.

## Evidência T092 — autorização e CSRF

- [x] nonce não substitui capability.
- [x] capability é revalidada no handler e no objeto.
- [x] mutações usam POST; GET de leitura é side-effect free.
- [x] `post_id`/scope/state do cliente são revalidados server-side.
- [x] role string não é contrato principal de autorização.

## Evidência T092 — entrada e saída

- [x] mass assignment é NO-GO; allowlist é obrigatória.
- [x] validação/sanitização e limites são por campo/contrato.
- [x] B-006 continua válido no write composto.
- [x] output usa escaping contextual e tardio.
- [x] dados do banco/projection/provider continuam não confiáveis na saída.

## Evidência T092 — Search e superfícies públicas

- [x] projection/index/cache/vector nunca autorizam visibilidade.
- [x] Search detail/results revalidam WordPress canônico.
- [x] query/result/fallback bounds e abuse control são obrigatórios quando a superfície existir.
- [x] taxonomias internas começam fail-closed para archive/rewrite/REST.
- [x] aliases/shortcodes continuam dependentes de B-003.

## Evidência T092 — IA, SSRF e privacidade

- [x] endpoint externo arbitrário é NO-GO.
- [x] URL variável futura exige HTTPS, host allowlist/validação e WordPress HTTP API segura.
- [x] secrets não entram em repo/log/export/prompt.
- [x] data egress precisa de finalidade/campos/provider/capability explícitos.
- [x] prompt injection não concede tool/capability.
- [x] query text/IP/UA/identidade continuam fora do baseline sob B-004.

## Evidência T092 — lifecycle/destrutivo

- [x] activation é mínima e não destrutiva.
- [x] uninstall preserva por default.
- [x] purge é ação separada, autorizada, explícita e com rollback/evidência quando aplicável.
- [x] nonce não foi tratado como proteção exactly-once; idempotência/expected-state entram quando o efeito exigir.

## Candidato de SPEC-001

- [x] `Core mínimo + Summary narrativo` é compatível com o threat model.
- [x] abrir Summary pode usar GET + capability de objeto sem mutação.
- [x] salvar Summary exige `edit_post` no objeto + POST + nonce + allowlist + Metadata API + read-after-write.
- [x] primeiro slice não precisa tabela/REST/AJAX/Search/provider/telemetria.

## Resultado T092

- [x] threat model cobrindo superfícies relevantes.
- [x] blockers globais novos = 0.
- [x] nenhuma capacidade postergada reaberta.
- [x] nenhum runtime/schema/provider/vector criado.

## Gate final SPEC-000

- [x] T059.
- [x] T090.
- [x] T091.
- [x] T092.
- [ ] T093.
- [ ] T094.
- [ ] T095.
- [ ] T096.
- [ ] T097.

## Próximo passo

**T093 — Revisão de QA/Regressão.**

Critério: produzir matriz de evidência/testes por slice, incluindo testes negativos de T092 e impedindo PASS vazio, `NOT_TESTED` ou evidência stale.

## Estado

T050–T059 + T090–T092 concluídos documentalmente. Runtime/Foundry/vector continuam inexistentes. SPEC-001 permanece bloqueada até T097.