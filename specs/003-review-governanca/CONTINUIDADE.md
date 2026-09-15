# Continuidade — SPEC-003 Review & Governança

## Estado atual

- SPEC-001: concluída.
- SPEC-002: concluída, baseline `0.2.0-rc.1`.
- UX-001: baseline v1 congelada; UI as Code v0.2 é a referência executável.
- SPEC-003: **R-001 PASS / R-010 PASS / G-001 PASS / G-030 PASS / DS-010 PASS / G-070 PASS / G-110 PASS**.
- etapa ativa: **G-130 — Lifecycle / fechamento**.
- build ativo: **`0.3.0-rc.1` — runtime limpo, lifecycle ambiental pendente**.

## G-110 fechado

Evidência final real:

`evidencias/bdc-kb-g110-browser-acceptance-20260915-200043.json`

Documento:

`evidencia-g110-dev11-pass.md`

Ambiente:

- WordPress `6.9.4`;
- PHP `8.5.10`;
- plugin `0.3.0-dev.11`;
- multisite: não.

Resultado:

- browser: **22 PASS / 0 FAIL**;
- server: **7 PASS / 0 FAIL**;
- `overall=PASS`;
- `residual_posts=0`;
- `residual_terms=0`;
- `residual_review_events=0`.

O gate comprovou em browser real:

- Workspace com cinco tabs autorizadas;
- Summary e Classificação salvando e permanecendo em suas tabs;
- Review `unreviewed -> in_review -> needs_changes -> approved`;
- `NO_CHANGE` sem evento extra;
- nota obrigatória em `needs_changes`;
- UI filtrada por capability;
- Histórico read-only consistente com o event log;
- teclado, foco e Enter;
- reflow em 1440 / 1024 / 782 / 492 px;
- preservação de `post_status`, `post_content` e `_elementor_data`;
- cleanup integral.

**G-110: PASS determinístico + ambiental.**

## Contrato permanente preservado

- owner de Review & Governança permanece o domínio SPEC-003;
- estado inicial implícito: `unreviewed`;
- estados: `unreviewed`, `in_review`, `needs_changes`, `approved`, `excluded`;
- fonte canônica: Comments API append-only, `comment_type=bdc_kb_review_event`;
- estado atual = último evento válido;
- Histórico = projection read-only do event log;
- sem meta paralela de current state;
- sem tabela customizada;
- sem writer próprio para Histórico;
- `post_status` independente de governança;
- `AI Ready`, score e métricas artificiais fora do domínio.

## G-130 — cleanup executado

Após o PASS de G-110 foram removidos do runtime:

- `includes/class-review-http-cache-coherence.php`;
- `includes/class-review-http-diagnostics.php`;
- `includes/class-workspace-browser-diagnostics.php`;
- `assets/js/browser-acceptance.js`;
- flags `BDC_KB_REVIEW_HTTP_DIAGNOSTICS_BUILD` e `BDC_KB_WORKSPACE_BROWSER_DIAGNOSTICS_BUILD`;
- hooks condicionais dos runners G-070/G-110.

Busca no código atual não encontrou referências residuais às classes/flags de diagnóstico.

Nenhum Store/Contract permanente foi removido ou refatorado durante o cleanup.

## Package ativo — `0.3.0-rc.1`

Documento:

`package-0.3.0-rc.1-clean.md`

SHA-256:

`7f681a3f62d792d30ccb016ae64b03e83d5cc46c4b2b1e2c2d96d41e3dfd0db5`

Tamanho:

`31.036 bytes`

Validação local:

- PHP lint: **PASS 11/11**;
- `assets/js/workspace.js`: syntax PASS;
- `unzip -t`: PASS;
- estrutura instalável WordPress: PASS;
- zero artefatos temporários no ZIP;
- source parity: **15/15 arquivos permanentes do build coincidem exatamente com os blobs do `main` pós-cleanup**;
- zero arquivo excedente no diretório de build.

O RC foi reconstruído a partir do `main` limpo. Não foi produzido simplesmente removendo runners de um ZIP de desenvolvimento.

## Próximo passo exato — lifecycle ambiental

1. substituir o `0.3.0-dev.11` pelo `0.3.0-rc.1`;
2. confirmar versão `0.3.0-rc.1` na tela de plugins;
3. confirmar que os blocos de homologação G-070/G-110 não aparecem mais;
4. abrir Base de Conhecimento e um artigo no Workspace;
5. confirmar as cinco tabs: Visão geral, Summary, Classificação, Review & Governança e Histórico;
6. fazer smoke de renderização de Summary e Classificação sem alterar conteúdo desnecessariamente;
7. confirmar que Review e Histórico existentes continuam legíveis;
8. desativar o plugin;
9. ativar novamente;
10. repetir abertura da Knowledge List/Workspace e confirmar ausência de fatal error, warnings ou perda de dados.

Somente após essa evidência:

- T062 pode ser marcado PASS;
- G-130 pode ser promovido a PASS;
- T065 pode congelar a baseline final da SPEC-003.

## Proibições durante G-130

- não adicionar feature;
- não refatorar Store/Contract;
- não alterar schema;
- não criar migração;
- não reintroduzir runners de homologação no RC;
- não alterar `post_status` por governança;
- não escrever `post_content` ou `_elementor_data` por Review;
- não duplicar estado em meta + histórico;
- não criar tabela própria sem nova SPEC/necessidade comprovada.

## Gates

- R-001: PASS.
- R-010: PASS.
- G-001: PASS.
- G-030: PASS.
- DS-010: PASS.
- G-070: PASS — 22/22, cleanup zero.
- G-110: **PASS — dev.11 22/22 browser, 7/7 server, cleanup zero**.
- G-130: **ACTIVE — cleanup e RC concluídos; lifecycle ambiental pendente**.
