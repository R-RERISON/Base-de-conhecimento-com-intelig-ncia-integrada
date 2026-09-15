# Continuidade — SPEC-003 Review & Governança

## Estado atual

- SPEC-001: concluída.
- SPEC-002: concluída, baseline `0.2.0-rc.1`.
- UX-001: baseline v1 congelada; UI as Code v0.2 é a referência executável.
- addendum de consumo: `ux/001-product-experience-knowledge-workspace/heritage-addendum-public-summary-v1.md`.
- SPEC-003: **R-001 PASS / R-010 PASS / G-001 PASS / G-030 PASS / DS-010 PASS**.
- etapa ativa: **G-070 — Writer HTTP e Segurança de Review**.

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

## Writer HTTP permanente / build ativo

Package: `0.3.0-dev.4`  
SHA-256: `31564fb8bd0da0e0e501c0edeee434f75ce01a1a95410422065a219fa9b033cb`

Arquivos novos:

- permanente: `class-review-admin.php`;
- temporário: `class-review-http-diagnostics.php`.

Contrato HTTP:

- POST only;
- nonce vinculado ao post;
- `edit_post(post_id)`;
- allowlist `target_state`/`note`;
- reviewer capability continua validada pelo `Review_Store`;
- PRG;
- `NO_CHANGE`, `FAIL_SAFE`, `PARTIAL_FAILURE_CRITICAL` preservados.

Runner temporário:

- usa somente 2 posts, 1 page e 4 termos temporários;
- testa GET/nonce/mass assignment/payload inválido/post type/ID inexistente;
- testa `edit_post` e `edit_others_posts` com negação assinada restrita à fixture;
- testa POST válido, NO_CHANGE, nota obrigatória, limite de bytes, `needs_changes` e `approved`;
- verifica preservação de editorial/Summary/Classificação/legado;
- cleanup obrigatório.

Documento: `package-dev4-http.md`.

## Próximo passo exato

1. substituir `0.3.0-dev.3` por `0.3.0-dev.4`;
2. abrir **Base de Conhecimento** como administrador;
3. clicar **Executar segurança HTTP Review e gerar JSON**;
4. retornar `bdc-kb-review-http-security-*.json`;
5. exigir `22 PASS / 0 FAIL / overall=PASS`;
6. exigir `residual_posts=0`, `residual_terms=0`, `residual_review_events=0`;
7. somente depois abrir G-110 e integrar Review ao Knowledge Workspace com tabs.

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
- não adicionar Review como terceiro bloco vertical.

## Gates

- R-001: **PASS**.
- R-010: **PASS**.
- G-001: **PASS**.
- G-030: **PASS**.
- DS-010: **PASS**.
- G-070: **IMPLEMENTADO / AGUARDANDO EXECUÇÃO REAL**.
- G-110/G-130: bloqueados pela sequência normal.
