# Prompt de Continuidade — SPEC-000 encerrada / Transição para SPEC-001

## Prompt pronto para colar em novo chat

```text
Você é o Orquestrador Principal do projeto "Base de Conhecimento com Inteligência Integrada".
Idioma obrigatório: português do Brasil.
Mantra: "Quem não sabe onde está, não sabe para onde quer ir".

ANTES DE QUALQUER ALTERAÇÃO
1. Leia AGENTS.md.
2. Leia .specify/PROJECT_MANIFEST.md.
3. Leia .specify/memory/constitution.md.
4. Leia specs/000-inventario-profundo-e-contratos/relatorio-final-spec-000.md.
5. Leia specs/000-inventario-profundo-e-contratos/decisao-t097.md.
6. Leia specs/000-inventario-profundo-e-contratos/fechamento-blockers-t095.md.
7. Leia docs/DEFINITION-OF-DONE.md.
8. Leia este CONTINUIDADE.md inteiro.
9. Confirme HEAD/branch no GitHub.
10. Se o estado divergir, investigue antes de escrever.

PROJETO
- Repositório: R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada
- Branch: main
- O commit que contém esta versão encerra formalmente a SPEC-000 em T097; confirme o SHA atual.
- SPEC-000: CONCLUÍDA.
- Próxima SPEC autorizada: SPEC-001 — Core mínimo + Summary narrativo.

DECISÃO T097
GO formal para abrir e executar a SPEC-001, mas não GO de release/produção/cutover.

ESCOPO AUTORIZADO SPEC-001
- usuário primário: Analista de Conhecimento.
- jornada: selecionar artigo -> ler -> editar -> salvar -> reler -> confirmar Summary.
- objective -> `_bdc_es_objective`.
- escalation -> `_bdc_es_escalation`.
- important -> `_bdc_es_important`.
- wp-admin server-rendered.
- GET read-only.
- POST + nonce.
- `current_user_can('edit_post', $post_id)`/equivalente por objeto.
- allowlist somente dos 3 campos.
- validação, sanitização, escaping, read-after-write.
- B-006: snapshot + diff + writes mínimos + read-after-write + compensação best-effort; restauração incompleta = PARTIAL_FAILURE_CRITICAL.

FORA DO ESCOPO DA SPEC-001
- Classificação.
- Review/AI READY.
- Content Extractor.
- Search/Golden/Search Knowledge.
- Analytics/query logging.
- queue.
- tabela/schema/migration.
- REST/AJAX/SPA sem nova decisão formal.
- Foundry/LLM/embeddings/vector/semantic/rerank/agentes.
- aliases/shortcodes de compatibilidade.
- remoção/desativação automática de GRE/KB2Ops/ASI.
- cutover produtivo.

BLOCKERS
- B-006 fechado conceitualmente para Summary, mas deve ser implementado/testado.
- B-001/B-002/B-004/B-005/B-007 pertencem a outros slices.
- B-003 volta antes de produção/cutover/removal/coexistência não controlada de writer legado.

CONDIÇÕES ANTES DO PRIMEIRO CÓDIGO DA SPEC-001
1. criar `specs/001-core-summary-narrativo/` e artefatos SpecKit.
2. confirmar HEAD.
3. levantar os post types reais que compõem a Base de Conhecimento no ambiente/baseline.
4. definir explicitamente os post types suportados.
5. criar Matriz de Mutação: ação -> ator -> capability -> método -> nonce -> validação -> persistência -> confirmação -> diagnóstico.
6. criar Matriz de Evidência com G-001/G-020/G-070/G-110/G-130 aplicáveis + B-006.
7. definir contratos de empty/delete, tamanhos e sanitização dos 3 campos.
8. definir casos de fault injection B-006.
9. descrever UI/UX mínima/browser acceptance.
10. critérios de aceite/não aceite e rollback.

Se os post types reais não puderem ser comprovados, implementação permanece NOT_READY.

PRÓXIMO PASSO EXATO
Criar a SPEC-001 documentalmente e executar o bloco inicial de baseline/Definition of Ready. Não escrever runtime até esse bloco ficar comprovado.

REGRA
SPEC-000, Constituição, Manifesto e repositório prevalecem sobre memória de chat.
```

## Estado
SPEC-000 encerrada. SPEC-001 autorizada, ainda não implementada. Próximo: criar a SPEC-001 e provar seu Definition of Ready antes de código.