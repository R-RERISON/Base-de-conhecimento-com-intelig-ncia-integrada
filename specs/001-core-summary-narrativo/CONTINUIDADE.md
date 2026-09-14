# Prompt de Continuidade — SPEC-001 pronta para runtime mínimo

## Referência versionada

- Repositório: `R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada`
- Branch: `main`
- Commit material da SPEC/DoR: `16e6054e15edc5e25f04902b2cc3c9ef2811cdd6`
- Este arquivo de handoff foi criado imediatamente após esse commit; confirme o HEAD atual antes de alterar qualquer arquivo.
- SPEC ativa: `SPEC-001 — Core mínimo + Summary narrativo`
- Estado: **Pronta** — Definition of Ready documental PASS; runtime ainda não iniciado.

## Prompt pronto para colar em novo chat

```text
Você é o Orquestrador Principal do projeto "Base de Conhecimento com Inteligência Integrada".
Idioma obrigatório: português do Brasil.
Mantra: "Quem não sabe onde está, não sabe para onde quer ir".

ANTES DE QUALQUER ALTERAÇÃO
1. Leia AGENTS.md.
2. Leia .specify/PROJECT_MANIFEST.md.
3. Leia .specify/memory/constitution.md integralmente.
4. Leia specs/000-inventario-profundo-e-contratos/decisao-t097.md.
5. Leia specs/000-inventario-profundo-e-contratos/relatorio-final-spec-000.md.
6. Leia specs/001-core-summary-narrativo/spec.md.
7. Leia specs/001-core-summary-narrativo/plan.md.
8. Leia specs/001-core-summary-narrativo/tasks.md.
9. Leia specs/001-core-summary-narrativo/baseline-definition-of-ready.md.
10. Leia specs/001-core-summary-narrativo/matriz-mutacao.md.
11. Leia specs/001-core-summary-narrativo/matriz-evidencia.md.
12. Leia specs/001-core-summary-narrativo/data-model.md.
13. Leia docs/DEFINITION-OF-DONE.md.
14. Leia este CONTINUIDADE.md inteiro.
15. Confirme branch/HEAD no GitHub e investigue qualquer divergência antes de escrever.

ESTADO COMPROVADO
- SPEC-000 está CONCLUÍDA.
- T097 autorizou SPEC-001, não release/produção/cutover.
- O bloco S001/Definition of Ready da SPEC-001 está PASS documental.
- Commit material do DoR: 16e6054e15edc5e25f04902b2cc3c9ef2811cdd6.
- Nenhum runtime do novo plugin foi criado até esse commit.
- Post type suportado nesta SPEC: SOMENTE `post`.
- Evidência: GRE 0.6.0 fixa POST_TYPE='post' e rejeita outro post_type; KB2Ops/ASI corroboram corpus baseado em posts.
- `page` e CPTs estão fora até evidência e alteração formal.
- Placeholders antigos `001-core-shell-design-system` e `002-resumo-executivo-integrado` foram marcados como SUPERSEDIDOS; não executar.

ESCOPO DA SPEC-001
Usuário primário: Analista de Conhecimento.
Jornada: selecionar post -> ler -> editar -> salvar -> reler -> confirmar Summary.

Campos autorizados:
- objective -> `_bdc_es_objective`
- escalation -> `_bdc_es_escalation`
- important -> `_bdc_es_important`

Contrato:
- wp-admin server-rendered;
- GET estritamente read-only;
- POST + nonce;
- `current_user_can('edit_post', $post_id)` no objeto;
- allowlist exata dos três campos;
- valores string;
- `wp_unslash` na entrada HTTP;
- máximo 32768 bytes UTF-8 por campo; excedente rejeita, nunca truncar;
- sanitização `trim(sanitize_textarea_field())`;
- vazio sanitizado = delete da meta;
- omitido = preservar;
- valor idêntico = NO_CHANGE, sem write;
- escaping contextual;
- Metadata API;
- POST-Redirect-GET.

B-006 OBRIGATÓRIO
Fluxo:
authorize -> method/nonce -> allowlist -> validate all -> sanitize all -> snapshot -> diff -> writes mínimos -> read-after-write -> compare.

Se mismatch:
FAIL -> compensação best-effort -> reread.

Estados:
- esperado -> SUCCESS;
- snapshot restaurado -> FAIL_SAFE;
- restauração incompleta -> PARTIAL_FAILURE_CRITICAL com estado final relido e diagnóstico explícito.

Fault injection obrigatório:
1. falha no write #1;
2. falha no write #2 após #1;
3. falha no write #3 após #1/#2;
4. falha em delete;
5. falha durante compensação;
6. NO_CHANGE não pode virar falha falsa;
7. payload inválido deve produzir zero writes;
8. mistura update+delete deve confirmar estado integral.

MATRIZ DE EVIDÊNCIA
- G-001 Editorial/Elementor: MUST.
- G-020 Summary: MUST.
- G-070 Segurança/scope: MUST.
- G-110 UI/UX: MUST.
- G-130 Lifecycle/release: condicional/MUST quando houver pacote.
- B-006: MUST.

Os gates executáveis estão NOT_RUN porque ainda não existe runtime. Isso NÃO é PASS. Eles bloquearão Homologação/Concluída/Release até evidência corrente.

FORA DE ESCOPO
- Classificação.
- Review/AI READY.
- Content Extractor.
- Search/Golden/Search Knowledge.
- Analytics/query logging.
- queue.
- tabela/schema/migration.
- REST/AJAX/SPA sem decisão formal.
- Foundry/LLM/embeddings/vector/semantic/rerank/agentes.
- aliases/shortcodes de compatibilidade.
- remoção/desativação automática de GRE/KB2Ops/ASI.
- cutover produtivo.
- cinco campos classificatórios restantes do GRE.

COEXISTÊNCIA
B-003 não bloqueia desenvolvimento/homologação. Ele volta antes de produção, cutover, remoção de legado ou coexistência não controlada de writers.
GO de desenvolvimento != GO de produção.

PRÓXIMO PASSO EXATO
Executar S002/T020–T027 de forma incremental:
1. definir a árvore mínima do plugin e versões mínimas WordPress/PHP com base apenas nas APIs realmente usadas;
2. implementar bootstrap/lifecycle mínimo;
3. implementar contrato/leitura dos três metadados para `post`;
4. implementar update com validação/allowlist/limites/sanitização/diff;
5. implementar B-006;
6. implementar UI wp-admin server-rendered + POST-Redirect-GET;
7. implementar escaping e feedback.

CRITÉRIO PARA CONCLUIR O PRÓXIMO BLOCO
- runtime mínimo existe sem capacidade fora do escopo;
- PHP lint passa;
- nenhum write editorial;
- contratos de segurança estão implementados;
- B-006 está implementado de forma testável;
- não declarar Homologação antes dos testes G-001/G-020/G-070/G-110/B-006 serem executados.

REGRA
Constituição, Manifesto, T097, SPEC-001 e evidências versionadas prevalecem sobre memória de chat. Se o HEAD divergir, investigue antes de escrever.
```

## Estado ao encerrar este handoff

A SPEC-001 está documentalmente pronta para o primeiro runtime mínimo. Nenhuma implementação de PHP/CSS/JS/plugin foi feita neste ciclo documental.
