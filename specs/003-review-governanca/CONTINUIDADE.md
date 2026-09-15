# Continuidade — SPEC-003 Review & Governança

## Estado atual

- SPEC-001: concluída.
- SPEC-002: concluída, baseline `0.2.0-rc.1`.
- UX-001: baseline v1 congelada; UI as Code v0.2 é a referência executável.
- SPEC-003: **R-001 PASS / R-010 PASS / G-001 PASS / G-030 PASS / DS-010 PASS / G-070 PASS**.
- etapa ativa: **G-110 — Knowledge Workspace / Browser Acceptance**.
- build ativo: **`0.3.0-dev.6` — W-001/W-002 implementados, aguardando smoke ambiental**.

## G-070 fechado

Evidência final real:

`evidencias/bdc-kb-review-http-security-20260915-165537.json`

Ambiente:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- plugin `0.3.0-dev.5`;
- multisite: não.

Resultado:

- **22 PASS / 0 FAIL**;
- `overall=PASS`;
- `residual_posts=0`;
- `residual_terms=0`;
- `residual_review_events=0`.

Documento: `evidencia-g070-dev5-pass.md`.

**G-070: PASS determinístico + ambiental.**

## Contrato de Review preservado

- owner: Review & Governança;
- estado inicial implícito: `unreviewed`;
- estados: `unreviewed`, `in_review`, `needs_changes`, `approved`, `excluded`;
- fonte canônica: eventos append-only via Comments API;
- `comment_type=bdc_kb_review_event`;
- estado atual = último evento válido;
- actor = `user_id`;
- data = `comment_date_gmt`;
- sem meta paralela de current state;
- sem `_reviewed_by`/`_reviewed_at`;
- sem tabela customizada;
- `AI Ready` fora do domínio.

## G-110 — plano ativo

Plano formal: `g110-workspace-browser-acceptance-plan.md`.

Autoridade UX:

- Context Header;
- tabs horizontais;
- Main Work Area;
- Context Panel somente quando real/acionável;
- uma coluna em `<=782px`;
- permanência no contexto do artigo após save.

Review não pode ser anexado como terceiro bloco vertical.

## Build `0.3.0-dev.6`

Documento: `package-dev6-workspace-review.md`.

ZIP instalável SHA-256:

`f97d5ec1f170babbc120d7ee2674b9276ca87d6c3cb9024c255381514d605e01`

PHP lint: **PASS 13/13**.

### W-001 implementado

`class-admin-page.php` agora compõe o Knowledge Workspace com:

- Visão geral;
- Summary;
- Classificação;
- Review & Governança.

A tela deixou de empilhar Summary/Classificação como arquitetura principal. A listagem abre o artigo no Workspace.

Summary e Classificação permanecem com stores/writers existentes.

### W-002 implementado

`class-review-admin.php` agora possui UI server-rendered usando somente contratos aprovados:

- estado atual;
- última decisão, actor e data;
- nota da última decisão;
- targets derivados da máquina de estados;
- targets de reviewer filtrados visualmente por `edit_others_posts`;
- formulário independente;
- nonce post-bound;
- PRG de volta à tab Review.

Segurança permanece server-side no handler/store.

### Design System

Novo asset:

`assets/css/workspace.css`

`admin.css` continua sendo a foundation validada no DS-010.

## Ainda NÃO implementado/aceito

- Histórico completo read-only;
- Browser Acceptance automatizado;
- validação final de teclado/foco;
- 1440/1024/782/~492;
- zero overflow comprovado no browser real;
- cleanup dos runners G-070;
- package RC.

Portanto, **G-110 continua ACTIVE, não PASS**.

## Próximo passo exato

1. substituir `0.3.0-dev.5` por `0.3.0-dev.6`;
2. abrir **Base de Conhecimento**;
3. abrir um artigo em **Abrir Workspace**;
4. capturar **Visão geral**;
5. capturar **Summary**;
6. capturar **Classificação**;
7. capturar **Review & Governança**;
8. confirmar save de Summary e permanência na tab;
9. confirmar save de Classificação e permanência no domínio;
10. em artigo de homologação, executar `unreviewed -> in_review` via Review e confirmar feedback/estado;
11. retornar screenshots/resultado para fechamento ambiental de W-001/W-002.

Somente depois entram W-003 Histórico e Browser Acceptance completo.

## Artefatos temporários ainda presentes

Remover apenas no G-130, após G-110 PASS:

- `class-review-http-diagnostics.php`;
- `class-review-http-cache-coherence.php`;
- flag `BDC_KB_REVIEW_HTTP_DIAGNOSTICS_BUILD`.

## Proibições mantidas

- não alterar `post_status` por governança;
- não escrever `post_content` ou `_elementor_data` de posts reais;
- não criar score;
- não criar `AI Ready`;
- não duplicar estado em meta + histórico;
- não criar tabela própria sem necessidade comprovada;
- não criar writer próprio para Histórico;
- não recuperar stores KB2Ops vazios;
- não ampliar o Workspace antes do smoke do `dev.6`.

## Gates

- R-001: **PASS**.
- R-010: **PASS**.
- G-001: **PASS**.
- G-030: **PASS**.
- DS-010: **PASS**.
- G-070: **PASS — 22/22, cleanup zero resíduos**.
- G-110: **ACTIVE — W-001/W-002 implementados / aguardando smoke ambiental**.
- G-130: **BLOQUEADO até G-110 PASS**.
