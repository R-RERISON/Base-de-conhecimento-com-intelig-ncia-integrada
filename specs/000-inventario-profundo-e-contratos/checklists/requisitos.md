# Checklist de Requisitos — SPEC-000

## Revisões finais
- [x] T090 WordPress-first.
- [x] T091 Simplicidade.
- [x] T092 Segurança.
- [x] T093 QA/Regressão.
- [ ] T094 Produto/Conhecimento.
- [ ] T095 Unknowns/blockers por slice.
- [ ] T096 Relatório final.
- [ ] T097 GO/NO-GO SPEC-001.

## Evidência T093
- [x] estados PASS/FAIL/NOT_RUN/NOT_CONFIGURED/STALE/N/A/POSTERGADO/WAIVED definidos.
- [x] gate ativo sem execução/evidência corrente = NO-GO.
- [x] N/A exige justificativa versionada.
- [x] Matriz de Evidência obrigatória por SPEC definida.
- [x] candidato Summary recebeu gates G-001/G-020/G-070/G-110/G-130 + B-006.
- [x] testes negativos de capability/nonce/GET/IDOR/mass assignment/XSS definidos.
- [x] browser/manual acceptance exigido quando unit/integration não prova interação.
- [x] benchmark só em caminho crítico medido.
- [x] Golden permanece obrigatória antes de release Search e não se aplica ao Summary isolado.
- [x] feature postergada não exige harness executável antecipado.
- [x] defect taxonomy P0–P3 definida.
- [x] package/source mismatch classificado como NO-GO futuro.
- [x] release evidence bundle conceitual definido.
- [x] zero blocker global novo.
- [x] nenhum runtime/teste executável criado.

## Gate final restante
- [ ] T094 validação de valor/ordem de produto.
- [ ] T095 fechamento formal dos blockers/unknowns aplicáveis à SPEC-001 candidata.
- [ ] T096 relatório final.
- [ ] T097 autorização formal.

## Próximo passo
**T094 — Revisão de Produto/Conhecimento.**