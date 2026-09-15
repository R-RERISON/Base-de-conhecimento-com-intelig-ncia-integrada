# Continuidade — SPEC-003 Review & Governança

## Estado atual

- SPEC-001: concluída.
- SPEC-002: concluída, baseline `0.2.0-rc.1`.
- UX-001: baseline v1 congelada; UI as Code v0.2 é a referência executável.
- addendum de consumo: `ux/001-product-experience-knowledge-workspace/heritage-addendum-public-summary-v1.md`.
- SPEC-003: **R-001 PASS / R-010 PASS / S003 LOCAL PASS**.

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

## Runtime mínimo implementado

Arquivos permanentes:

- `class-review-contract.php`;
- `class-review-store.php`.

Evidência:

- unitários: **PASS 19/19**;
- PHP lint: **PASS 10/10**;
- G-030 local: PASS;
- package: `0.3.0-dev.1`;
- SHA-256: `632d2e5e56a7d89abd513f3f4b0b7f75f1c20a8dbd05e6ee383869489cf00acd`.

O package ainda NÃO possui handler ou UI de Review. Esse comportamento é intencional: primeiro validamos que a nova base permanente não causa regressão na instalação real.

## Próximo passo exato

Executar smoke ambiental da `0.3.0-dev.1`:

1. substituir o build de profiling pelo `0.3.0-dev.1`;
2. ativar sem fatal;
3. abrir Base de Conhecimento;
4. abrir listagem e um artigo;
5. confirmar Summary e Classificação íntegros;
6. confirmar ausência de painel de profiling e ausência de UI Review nesta build.

Após smoke PASS:

1. promover G-001 ambiental;
2. preparar tooling temporário para exercitar Review Store na Comments API real com fixture e cleanup;
3. somente depois abrir handler HTTP G-070 e UI G-110.

## UX / valor preservado

A tela histórica de artigo com **Resumo Executivo lateral** foi registrada como patrimônio de produto. No futuro Resolvedor, esse painel será uma projection read-only composta por owners canônicos, não um novo writer.

## Proibições mantidas

- não alterar `post_status` por decisão de governança;
- não escrever `post_content` ou `_elementor_data`;
- não criar score;
- não criar `AI Ready`;
- não duplicar estado em meta + histórico;
- não criar tabela própria sem necessidade comprovada;
- não recuperar stores KB2Ops vazios por nostalgia arquitetural.

## Gates

- R-001: **PASS**.
- R-010: **PASS**.
- G-030: **PASS local**.
- G-001: **SMOKE AMBIENTAL PENDENTE**.
- G-070/G-110/G-130: bloqueados pela sequência normal.
