# Tarefas — SPEC-000

## Preparação

- [x] T000 — Registrar repositórios e SHAs baseline.
- [x] T001 — Criar Constituição, Manifesto e regras de agentes.
- [x] T002 — Criar roadmap e SpecKit base.

## Inventários individuais

- [x] T010–T019 — KB2Ops completo.
- [x] T020–T034 — ASI completo.
- [x] T040–T047 — Resumo Executivo completo.

## Cruzamento

- [x] T050 — Catálogo unificado de persistência.
- [x] T051 — Catálogo unificado de hooks/rotas/integrações.
- [x] T052 — Mapa de ownership.
- [x] T053 — Matriz de sobreposição.
- [x] T054 — Drifts/compatibilidade/blockers.
- [x] T055 — Catálogo de regressão e Golden Queries.
- [x] T056 — Matriz WordPress-first.
- [x] T057 — Infraestrutura própria mínima.
- [x] T058 — IA/vetor priorizados.
- [x] T059 — Matriz de paridade futura FINAL.

## Revisões finais

- [x] T090 — Revisão do Arquiteto WordPress. _(`revisao-wordpress-t090.md`; PASS, 0 bloqueantes, 2 simplificações, 3 investigações)_
- [ ] T091 — Revisão do Crítico de Simplicidade.
- [ ] T092 — Revisão de Segurança.
- [ ] T093 — Revisão de QA/Regressão.
- [ ] T094 — Revisão de Produto/Conhecimento.
- [ ] T095 — fechar unknowns/blockers aplicáveis por slice.
- [ ] T096 — emitir relatório final da SPEC-000.
- [ ] T097 — autorizar ou bloquear SPEC-001.

## Resultado T090

- [x] WordPress-first passou sem finding bloqueante.
- [x] Search Retrieval Projection própria permaneceu justificada como única exceção persistente.
- [x] Summary/Review continuam em Metadata API.
- [x] classificações continuam em Taxonomy/Metadata conforme T056/B-002.
- [x] Site Health permanece primitive de diagnóstico.
- [x] WP-Cron permanece trigger, nunca durable queue.
- [x] admin-post permanece baseline; AJAX só por live UX; REST sem consumidor continua negado.
- [x] histórico de review recebeu regra de simplificação: não manter bounded history + meta revisions concorrentes sem requisito.
- [x] Search Knowledge/Golden devem permanecer entidades internas WordPress-first, sem tabela/admin CRUD próprio.
- [x] versão mínima do WordPress para `revisions_enabled` ficou para T095/SPEC aplicável.
- [x] taxonomias não ganham archive/rewrite público automaticamente.
- [x] provider endpoint configurável deverá ser revisto em T092 para SSRF/allowlist.
- [x] nenhum runtime/schema/provider/vector foi criado.

## Próximo passo exato

**T091 — Revisão do Crítico de Simplicidade.**

Aplicar o princípio de negação à arquitetura final e tentar remover qualquer camada/capacidade ainda não estritamente necessária. Findings: `MANTER | SIMPLIFICAR | POSTERGAR | DESCARTAR | BLOQUEAR`.

## Estado

T050–T059 + T090 concluídos documentalmente. **Nenhum runtime novo foi criado.** Próximo bloco autorizado: **T091**. SPEC-001 continua bloqueada até T097.
