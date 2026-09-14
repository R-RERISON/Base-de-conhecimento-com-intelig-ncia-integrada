# Tarefas — SPEC-000

## Preparação e inventários

- [x] T000–T002 — preparação/governança.
- [x] T010–T019 — KB2Ops.
- [x] T020–T034 — ASI.
- [x] T040–T047 — Resumo Executivo.

## Cruzamento

- [x] T050 — persistência.
- [x] T051 — integrações.
- [x] T052 — ownership.
- [x] T053 — sobreposição.
- [x] T054 — drifts/blockers/compatibilidade.
- [x] T055 — regressão/Golden.
- [x] T056 — WordPress-first.
- [x] T057 — infraestrutura própria mínima.
- [x] T058 — IA/vetor.
- [x] T059 — paridade futura FINAL.

## Revisões finais

- [x] T090 — Arquiteto WordPress. _(`revisao-wordpress-t090.md`; PASS, zero bloqueantes)_
- [x] T091 — Crítico de Simplicidade. _(`revisao-simplicidade-t091.md`; PASS, zero bloqueantes)_
- [x] T092 — Segurança. _(`revisao-seguranca-t092.md`; PASS arquitetural, 0 blockers globais; controles NO-GO definidos)_
- [ ] T093 — QA/Regressão.
- [ ] T094 — Produto/Conhecimento.
- [ ] T095 — unknowns/blockers por slice.
- [ ] T096 — relatório final.
- [ ] T097 — GO/NO-GO SPEC-001.

## Resultado T092

- [x] capability deve ser validada no handler e no objeto; nonce não substitui autorização.
- [x] mutações usam POST + nonce específico; GET é side-effect free.
- [x] `post_id` é revalidado server-side contra objeto, tipo/scope e capability.
- [x] inputs usam allowlist/validação/sanitização; mass assignment é NO-GO.
- [x] saída usa escaping contextual tardio.
- [x] futura Search revalida WordPress canônico; projection nunca é autoridade de acesso.
- [x] taxonomias internas começam fail-closed para exposição pública.
- [x] aliases/shortcodes continuam postergados sob B-003.
- [x] provider endpoint arbitrário é NO-GO; SSRF/allowlist/HTTP API segura são obrigatórios quando IA nascer.
- [x] secrets não entram em repo/log/export/prompt.
- [x] Analytics/query/identidade continuam postergados sob B-004.
- [x] ações destrutivas exigem intenção, capability, POST/nonce e rollback; activation/uninstall default não destroem dados.
- [x] candidate SPEC-001 `Core mínimo + Summary` é compatível com o threat model.
- [x] nenhum runtime/schema/provider/vector foi criado.

## Próximo passo exato

**T093 — Revisão de QA/Regressão.**

Transformar T055 + T090–T092 em matriz de evidência executável por slice. `NOT_TESTED`, suíte vazia ou evidência stale nunca podem ser PASS.

## Estado

T050–T059 + T090 + T091 + T092 concluídos documentalmente. Próximo bloco autorizado: **T093**. SPEC-001 permanece bloqueada até T097.