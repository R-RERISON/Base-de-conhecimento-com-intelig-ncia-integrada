# SPEC-003 — Review & Governança do Conhecimento

**Status:** R-001 PASS / R-010 PASS / S003 RUNTIME MÍNIMO AUTORIZADO  
**Baseline funcional:** `0.2.0-rc.1`  
**Baseline UX:** `ux/001-product-experience-knowledge-workspace/ux-baseline-v1.md`  
**Domain Contract:** `specs/003-review-governanca/domain-contract.md`

## Mantra

> Quem não sabe onde está, não sabe para onde quer ir.

A SPEC-003 começou por inventário real de stores, writers, estados, capabilities e fluxos históricos. O profiling real encontrou zero rows de Review/Governança nos seis stores KB2Ops candidatos em um corpus de 622 posts. A implementação segue, portanto, como domínio greenfield controlado, sem migração ou dual-read legado.

## 1. Problema

Summary e Classificação já possuem owners canônicos. Review & Governança precisa responder, de forma auditável:

- qual é o estado de governança do conhecimento;
- quem tomou a última decisão válida;
- quando ocorreu a decisão;
- de qual estado para qual estado houve transição;
- quais mudanças de estado são permitidas;
- como manter governança separada do estado editorial WordPress.

## 2. Objetivo

Criar um domínio mínimo, explícito e auditável de Review & Governança sem:

- substituir `post_status` editorial do WordPress;
- alterar `post_content` ou `_elementor_data`;
- criar score ornamental;
- criar `AI Ready` por implicação;
- importar automaticamente estados legados;
- manter dual-write permanente;
- transformar hipótese UX em regra de negócio.

## 3. Fonte da verdade e fronteiras

### Continua fora do domínio

- conteúdo editorial: `WP_Post` + Elementor;
- Summary: contrato SPEC-001;
- Classificação: contrato SPEC-002;
- Search/IA: SPECs posteriores.

### Owner desta SPEC

Review & Governança é owner das decisões humanas de governança.

A fonte canônica da primeira slice é um event log append-only usando WordPress Comments API, `comment_type=bdc_kb_review_event`. O estado atual é derivado do último evento válido; não existe post meta paralela de `current_state`.

## 4. Estados canônicos da primeira slice

- `unreviewed` — estado inicial implícito, quando não existe evento;
- `in_review` — submetido/reaberto para revisão;
- `needs_changes` — revisão exige correções;
- `approved` — decisão humana explícita de aprovação;
- `excluded` — decisão humana explícita de retirar do conjunto governado, sem apagar ou despublicar o post.

`approved` não implica publish, Search, indexação, IA ou AI Ready.

## 5. Princípios obrigatórios

1. **Editorial != governança.** `publish/draft/private/...` não substitui o estado de governança.
2. **Decisão humana explícita.** IA futura pode sugerir; não aprova conteúdo por inferência.
3. **Histórico é fonte da verdade.** Estado e auditoria não são dois stores concorrentes.
4. **WordPress-first.** Usar Comments API antes de tabela customizada para o volume atual.
5. **Sem migração implícita.** O ambiente real não possui rows legadas; adapters não serão inventados.
6. **Capability por objeto.** Toda transição revalida `edit_post(post_id)`.
7. **Reviewer real para decisão.** Aprovar, pedir ajustes, excluir e reabrir estados finais exigem também `edit_others_posts` na primeira slice.
8. **PRG e read-after-write.** Fluxos administrativos seguem a disciplina das SPECs anteriores.
9. **Falha parcial é crítica.** Evento recém-inserido cuja releitura diverge deve ser compensado; se a restauração falhar, estado crítico explícito.
10. **Sem score até haver fórmula + owner + ação.**
11. **UX-001 é contrato de experiência, não de dados.**

## 6. Máquina de transições

Permitidas:

- `unreviewed -> in_review|approved|excluded`;
- `in_review -> approved|needs_changes|excluded`;
- `needs_changes -> in_review|approved|excluded`;
- `approved -> in_review|needs_changes|excluded`;
- `excluded -> in_review`.

Mesmo estado -> mesmo estado = `NO_CHANGE`, zero write.

Demais transições = inválidas, zero write.

Notas são obrigatórias para `needs_changes` e `excluded`, opcionais para `in_review` e `approved`, com máximo de 2000 bytes antes da sanitização.

## 7. Actor e timestamp

Não haverá meta canônica duplicada de reviewer/data.

- actor = `user_id` do evento;
- instante = `comment_date_gmt` do evento;
- histórico = sequência de eventos `bdc_kb_review_event`.

## 8. Evidência R-001

O profiler `0.3.0-profile.1` executado no ambiente real confirmou:

- WordPress 6.9.4;
- PHP 8.5.10;
- 622 posts no escopo;
- 0 meta rows nos seis stores históricos;
- 0 actors/timestamps/history;
- 0 posts com qualquer dado legado de review;
- `writes_performed=false`.

Evidência: `evidencia-profiling-s001.md`.

**R-001: PASS.**

## 9. Política de legado

- migração: não necessária;
- dual-read: não criar;
- dual-write: proibido;
- `_kb2ops_include_ai`: fora do domínio;
- `AI Ready`: fora do domínio;
- estados históricos KB2Ops: referência semântica, não dados importados.

## 10. UX

A baseline UX v1 reserva Review & Governança no Knowledge Workspace.

A primeira UI real só pode mostrar fatos contratados:

- estado atual;
- última decisão, ator e data quando existirem;
- transições disponíveis ao usuário;
- nota da decisão;
- histórico real.

A tela histórica do artigo com Resumo Executivo lateral foi registrada em `heritage-addendum-public-summary-v1.md`. Essa superfície pertence ao futuro Resolvedor e será projection read-only dos owners canônicos, não um novo writer.

## 11. Segurança do writer

- POST only;
- nonce vinculado ao post e à ação;
- `current_user_can('edit_post', post_id)`;
- `edit_others_posts` para decisões de reviewer conforme contrato;
- allowlist exata de estado/transição;
- note tipada e limitada;
- validação integral antes da inserção;
- um único evento canônico por transição;
- read-after-write;
- compensation somente do evento recém-criado quando necessário;
- logs sem conteúdo editorial bruto.

## 12. Gates

### R-001 — Current-state evidence

**PASS.**

### R-010 — Domain Contract

**PASS.** Contrato detalhado em `domain-contract.md`.

### G-001 — Bootstrap/registration

Registro mínimo sem side effects e sem regressão SPEC-001/002.

### G-030 — Deterministic domain/store

Unitários para transições, no-op, note, autorização, insert failure, read-after-write, compensação e integridade do último evento.

### G-070 — HTTP Security

GET, nonce, nonce-post binding, mass assignment, capability/IDOR, payload inválido e PRG real.

### G-110 — Browser Acceptance

Workspace, estados, feedback, teclado, viewport estreito e regressão Summary/Classificação.

### G-130 — Lifecycle/Clean package

Sem fixture/runner residual, deactivate/activate limpo e package RC reproduzível.

## 13. Critério de saída

SPEC-003 só é concluída quando:

- o estado de governança possui owner único;
- transições são determinísticas e autorizadas;
- decisões são auditáveis;
- editorial/Summary/Classificação permanecem íntegros;
- legado não é promovido silenciosamente;
- UX respeita a baseline UX-001;
- todos os gates MUST estão PASS.

**GO de desenvolvimento/homologação != GO de produção.**
