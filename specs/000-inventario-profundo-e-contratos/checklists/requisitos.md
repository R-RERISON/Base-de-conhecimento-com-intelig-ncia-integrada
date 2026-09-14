# Checklist de Requisitos — SPEC-000

## Revisões finais
- [x] T090 WordPress-first.
- [x] T091 Simplicidade.
- [x] T092 Segurança.
- [x] T093 QA/Regressão.
- [x] T094 Produto/Conhecimento.
- [x] T095 blockers/unknowns por slice.
- [ ] T096 relatório final.
- [ ] T097 GO/NO-GO SPEC-001.

## Evidência T095
- [x] B-001 classificado fora da SPEC-001.
- [x] B-002 classificado fora da SPEC-001.
- [x] B-003 não bloqueia dev/homolog; permanece para cutover/removal.
- [x] B-004 classificado fora da SPEC-001.
- [x] B-005 classificado fora da SPEC-001.
- [x] B-006 fechado conceitualmente para Summary.
- [x] B-007 classificado fora da SPEC-001.
- [x] storage inicial Summary definido nos três meta keys GRE existentes.
- [x] capability baseline definida por objeto (`edit_post`).
- [x] superfície técnica mínima: wp-admin server-rendered + POST/nonce.
- [x] sem REST/AJAX/SPA/settings/event bus/history/audit/migration/schema no primeiro slice.
- [x] post types reais devem ser enumerados na baseline da SPEC-001 antes do código.
- [x] rollback por deactivation sem destruição de postmeta.
- [x] `BLOCKER_SPEC001` aberto = zero.

## Gate final restante
- [ ] T096 relatório final consolidado.
- [ ] T097 decisão formal.

## Próximo passo
**T096 — Relatório Final da SPEC-000.**