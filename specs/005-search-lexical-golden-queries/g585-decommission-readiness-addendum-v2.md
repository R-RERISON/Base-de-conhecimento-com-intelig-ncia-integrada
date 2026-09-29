# G-585 — Decommission Readiness Addendum v2

**Status:** FROZEN PARA IMPLEMENTAÇÃO/HOMOLOGAÇÃO  
**Data:** 2026-09-29  
**Base:** G-590 PASS/CLOSED / RC12  
**Supersede:** complementa `g585-asi-independence-contract-v1.md`.

## 1. Problema

O contrato v1 prova independência do engine Search, mas o estado pós-G-590 exige distinguir três decisões diferentes:

1. **engine independence** — Search/Golden/rebuild/lifecycle funcionam sem ASI;
2. **surface dependency zero** — nenhum shortcode/página/template conhecido exige runtime ASI;
3. **cutover/decommission authorization** — somente um gate posterior, com Master Functional Parity Ledger aplicável sem blockers, pode autorizar retirada física.

G-585 não pode inferir a etapa 3 a partir das etapas 1 e 2.

## 2. Extensão T585.1 — Surface dependency zero

O runner deve registrar, de forma read-only:

- shortcodes registrados cujo identificador corresponda ao legado ASI;
- marcadores ASI presentes no conteúdo da página configurada como Home;
- nomes de hooks de template explicitamente legados detectados pelo contrato;
- presença física de tabelas/options legadas não é falha por si só.

PASS de T585.1 exige ausência de dependência observável nesses consumidores.

## 3. Evidence schema v2

O JSON passa a usar `schema_version=2.0.0` e inclui:

- `legacy_surface_dependencies`;
- `gate_result.t585_1_surface_dependency_zero`;
- `decommission_authorization`;
- `gate_result.cutover_authorized=false`.

## 4. Regra de cutover

Mesmo com `t589_2_g585_pass=true`:

- `cutover_authorized` permanece `false`;
- razão: `MASTER_LEDGER_PREFLIGHT_REQUIRED`;
- próximo passo: `SPEC005_BOUNDARY_REVIEW`.

Isso é intencional. G-585 prova independência técnica; não executa retirada física do ASI e não substitui o Master Functional Parity Ledger.

## 5. Segurança

Mantém-se:

- POST;
- nonce;
- `manage_options`;
- nenhuma desativação automática;
- nenhuma remoção de arquivo/plugin;
- nenhuma limpeza de storage legado;
- zero network;
- zero write editorial;
- rebuild somente da Projection BDC derivada.

## 6. Aceite local

A implementação local deve provar estaticamente:

- schema v2;
- T585.1 presente;
- surface probe read-only;
- cutover explicitamente false;
- next gate pós-PASS não volta para G-590;
- parent ranker permanece congelado.

## 7. Aceite ambiental

Com ASI manualmente desativado:

- T585=true;
- T585.1=true;
- T586=true;
- T587=true;
- T588=true;
- T589=true;
- T589.2=true;
- errors/throwables vazios;
- cutover_authorized=false.

Somente então G-585 pode ser fechado como **INDEPENDENCE PASS / DECOMMISSION NOT AUTHORIZED**.
