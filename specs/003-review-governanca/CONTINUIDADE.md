# Continuidade — SPEC-003 Review & Governança

## Estado atual

- SPEC-001: concluída.
- SPEC-002: concluída, baseline `0.2.0-rc.1`.
- UX-001: baseline v1 congelada; UI as Code v0.2 é a referência executável.
- addendum de consumo: `ux/001-product-experience-knowledge-workspace/heritage-addendum-public-summary-v1.md`.
- SPEC-003: **R-001 PASS / R-010 PASS / G-001 PASS / G-030 PASS / DS-010 PASS**.
- etapa ativa: **G-070 — Writer HTTP e Segurança de Review / rerun `0.3.0-dev.5`**.

## Evidência do ambiente real

Profiler `0.3.0-profile.1` executado em WordPress 6.9.4 / PHP 8.5.10:

- corpus: 622 posts;
- seis stores históricos de review analisados;
- meta rows encontradas: 0;
- posts com qualquer dado histórico de review: 0;
- writes do profiler: 0;
- conteúdo editorial lido: não;
- notas/IDs de usuários exportados: não.

Documento: `evidencia-profiling-s001.md`.

Conclusão: não existe passivo real de migração de Review/Governança no ambiente analisado.

## Domain Contract aprovado

Documento: `domain-contract.md`.

Decisões principais:

- owner: Review & Governança;
- estado inicial implícito: `unreviewed`;
- estados: `unreviewed`, `in_review`, `needs_changes`, `approved`, `excluded`;
- fonte canônica: eventos append-only via WordPress Comments API;
- `comment_type`: `bdc_kb_review_event`;
- estado atual = último evento válido;
- actor = `user_id` do evento;
- data = `comment_date_gmt`;
- sem meta paralela de current state;
- sem `_reviewed_by`/`_reviewed_at` duplicados;
- sem tabela customizada;
- sem migração/dual-read/dual-write legado;
- `AI Ready` e `_kb2ops_include_ai` permanecem fora do domínio.

## Runtime mínimo e integração real

Arquivos permanentes:

- `class-review-contract.php`;
- `class-review-store.php`.

Evidências:

- unitários determinísticos: **PASS 19/19**;
- smoke `0.3.0-dev.1`: **PASS**;
- integração Comments API `0.3.0-dev.2`: **PASS 17/17**;
- cleanup do runner: `residual_posts=0`, `residual_terms=0`, `residual_review_events=0`;
- preservação de editorial/Summary/Classificação: PASS;
- corrupção do último evento: erro explícito de integridade comprovado.

**G-001: PASS.**  
**G-030: PASS determinístico + ambiental.**

## Design System Runtime Foundation

Documento: `evidencia-design-system-runtime-dev3.md`.

Build validado: `0.3.0-dev.3`.

As capturas do ambiente real comprovaram:

- tokens/surfaces/hierarquia do Design System presentes no runtime;
- Knowledge List mais legível;
- contexto do artigo, Summary e Classificação visualmente coerentes;
- nenhuma regressão funcional reportada.

Também ficou registrado um achado de arquitetura UX: o runtime ainda empilha `Summary -> Classificação`. Esse formato NÃO será ampliado com um terceiro bloco Review. A convergência para Workspace/tabs será feita no G-110 após aprovação do writer HTTP.

**DS-010: PASS — Runtime Foundation.**

Reflow/foco definitivo em `<=782px` e `~492px` permanece dentro do G-110 Browser Acceptance, quando o Workspace final existir.

## Writer HTTP permanente

Arquivo permanente: `class-review-admin.php`.

Contrato HTTP preservado:

- POST only;
- nonce vinculado ao post;
- `edit_post(post_id)`;
- allowlist `target_state`/`note`;
- reviewer capability continua validada pelo `Review_Store`;
- PRG;
- `NO_CHANGE`, `FAIL_SAFE`, `PARTIAL_FAILURE_CRITICAL` preservados.

## Evidência real do G-070 — `0.3.0-dev.4`

Arquivo bruto:

`evidencias/bdc-kb-review-http-security-20260915-155801.json`

Resultado:

- `15 PASS / 7 FAIL`;
- `overall=FAIL`;
- `residual_posts=0`;
- `residual_terms=0`;
- `residual_review_events=0`.

Os testes H01-H13 passaram. As falhas ficaram concentradas em H14-H20, isto é, nas asserções que dependem da visão pós-write no processo pai do runner.

Observações decisivas do próprio relatório:

- `object_capability`: `302 / forbidden`;
- `valid_submit`: `302 / saved`;
- `reviewer_capability`: `302 / forbidden`.

Isso indica que o request filho atravessou o `admin-post.php` e o writer retornou os statuses esperados, enquanto as verificações posteriores do runner permaneceram incompatíveis com a mutação recém-realizada.

Diagnóstico documentado em `evidencia-g070-dev4-cache-coherence.md`: incoerência de cache de Comments API entre o request PHP filho do loopback e o processo pai que executa `Review_Store::read()` / `event_count()`.

**G-070 NÃO está aprovado.** O resultado `0.3.0-dev.4` foi preservado como evidência de FAIL real; ele não foi reinterpretado como PASS.

## Build ativo para rerun — `0.3.0-dev.5`

Alteração deliberadamente limitada ao harness temporário:

- novo `class-review-http-cache-coherence.php`;
- carregado somente com `BDC_KB_REVIEW_HTTP_DIAGNOSTICS_BUILD=true`;
- após loopback destinado ao writer Review, o processo pai avança `wp_cache_set_comments_last_changed()` antes das releituras do harness;
- `class-review-admin.php` permanece inalterado;
- `class-review-store.php` permanece inalterado;
- nenhuma regra de segurança foi relaxada.

Documento: `package-dev5-http-cache-coherence.md`.

Artefatos temporários a remover antes do RC:

- `class-review-http-diagnostics.php`;
- `class-review-http-cache-coherence.php`;
- flag `BDC_KB_REVIEW_HTTP_DIAGNOSTICS_BUILD`.

## Próximo passo exato

1. instalar/substituir o build atual por `0.3.0-dev.5`;
2. abrir **Base de Conhecimento** como administrador;
3. clicar **Executar segurança HTTP Review e gerar JSON**;
4. retornar o novo `bdc-kb-review-http-security-*.json`;
5. exigir `22 PASS / 0 FAIL / overall=PASS`;
6. exigir novamente `residual_posts=0`, `residual_terms=0`, `residual_review_events=0`;
7. somente depois alterar G-070 para PASS e abrir G-110;
8. G-110 deve integrar Review ao Knowledge Workspace com tabs, sem criar terceiro bloco vertical.

## UX / valor preservado

A tela histórica de artigo com **Resumo Executivo lateral** foi registrada como patrimônio de produto. No futuro Resolvedor, esse painel será uma projection read-only composta por owners canônicos, não um novo writer.

## Proibições mantidas

- não alterar `post_status` por decisão de governança;
- não escrever `post_content` ou `_elementor_data` de posts reais;
- não criar score;
- não criar `AI Ready`;
- não duplicar estado em meta + histórico;
- não criar tabela própria sem necessidade comprovada;
- não recuperar stores KB2Ops vazios por nostalgia arquitetural;
- não expor UI funcional de Review antes de G-070 PASS;
- não adicionar Review como terceiro bloco vertical;
- não modificar writer/store permanentes para mascarar falha do harness.

## Gates

- R-001: **PASS**.
- R-010: **PASS**.
- G-001: **PASS**.
- G-030: **PASS**.
- DS-010: **PASS**.
- G-070: **NÃO APROVADO — `0.3.0-dev.4` = 15 PASS / 7 FAIL; `0.3.0-dev.5` aguardando execução real**.
- G-110: **BLOQUEADO**.
- G-130: **BLOQUEADO pela sequência normal**.
