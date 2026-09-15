# SPEC-003 — Package `0.3.0-dev.6` / G-110 W-001 + W-002

## Objetivo

Abrir a primeira slice real do G-110 após o PASS do G-070, convergindo a tela do artigo para o Knowledge Workspace e integrando Review & Governança como domínio próprio.

## Gate de entrada

G-070 aprovado no ambiente real com:

- `22 PASS / 0 FAIL`;
- `overall=PASS`;
- cleanup zero resíduos.

Evidência: `evidencia-g070-dev5-pass.md`.

## Build

- versão: `0.3.0-dev.6`;
- SHA-256 do ZIP instalável: `f97d5ec1f170babbc120d7ee2674b9276ca87d6c3cb9024c255381514d605e01`;
- PHP lint: PASS 13/13;
- ZIP: `base-conhecimento-inteligencia-integrada-0.3.0-dev.6.zip`.

## W-001 — Knowledge Workspace

`class-admin-page.php` agora compõe:

- Context Header;
- tabs horizontais;
- Main Work Area;
- Visão geral;
- Summary;
- Classificação;
- Review & Governança.

A listagem abre o artigo em `Visão geral` dentro do Workspace.

Summary e Classificação continuam usando stores/writers existentes. A mudança é de composição/navegação.

## W-002 — Review & Governança

`class-review-admin.php` agora possui projection server-rendered permanente:

- estado atual canônico;
- última decisão, actor e data quando disponíveis;
- nota da última decisão quando existir;
- targets derivados de `Review_Contract::allowed_targets()`;
- filtro visual de targets que exigem `edit_others_posts`;
- formulário independente usando o mesmo `Review_Admin::ACTION` já aprovado no G-070;
- nonce post-bound;
- nota limitada a `Review_Contract::MAX_NOTE_BYTES`;
- PRG retorna à tab Review.

A UI não substitui segurança server-side; o handler/store revalidam post, nonce, capabilities, target, transição e nota.

## Design System

Novo asset:

`assets/css/workspace.css`

Responsável somente pela composição visual do Workspace, tabs, overview e Review. `admin.css` permanece como foundation do Design System validado no DS-010.

## Não implementado neste build

Deliberadamente fora do `dev.6`:

- tab Histórico completa;
- Browser Acceptance automatizado;
- arrow-key semantics específicas de tab widget;
- fechamento de reflow/foco;
- remoção dos runners G-070;
- package RC.

Esses itens continuam no G-110/G-130.

## Proibições preservadas

O build NÃO cria:

- score;
- `AI Ready`;
- progresso percentual;
- meta paralela de current state;
- `_reviewed_by`/`_reviewed_at`;
- tabela customizada;
- writer próprio para histórico;
- alteração de `post_status` por Review;
- alteração de `post_content` ou `_elementor_data`.

## Smoke ambiental solicitado

Após instalar `0.3.0-dev.6`:

1. abrir **Base de Conhecimento**;
2. abrir um artigo em **Abrir Workspace**;
3. retornar screenshot da **Visão geral**;
4. retornar screenshot da tab **Summary**;
5. retornar screenshot da tab **Classificação**;
6. retornar screenshot da tab **Review & Governança**;
7. confirmar que Summary continua salvando e retorna à própria tab;
8. confirmar que Classificação continua salvando e permanece no domínio correspondente;
9. em um artigo de homologação, executar `unreviewed -> in_review` pela tab Review;
10. confirmar feedback, estado atualizado e permanência na tab Review.

## Critério desta slice

`dev.6` só fecha W-001/W-002 ambientalmente após smoke real sem regressão. G-110 permanece ACTIVE e NÃO deve ser marcado PASS apenas pela existência do código.