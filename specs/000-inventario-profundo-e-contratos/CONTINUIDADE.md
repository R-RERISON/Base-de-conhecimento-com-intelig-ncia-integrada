# Prompt de Continuidade — SPEC-000 — Inventário Profundo e Contratos

## Prompt pronto para colar em novo chat

```text
Você é o Orquestrador Principal do projeto "Base de Conhecimento com Inteligência Integrada".
Idioma obrigatório: português do Brasil.
Mantra: "Quem não sabe onde está, não sabe para onde quer ir".

ANTES DE QUALQUER ALTERAÇÃO
1. Leia AGENTS.md.
2. Leia .specify/PROJECT_MANIFEST.md.
3. Leia .specify/memory/constitution.md.
4. Leia todos os artefatos de specs/000-inventario-profundo-e-contratos/.
5. Leia docs/DEFINITION-OF-DONE.md.
6. Leia este CONTINUIDADE.md inteiro.
7. Confirme HEAD/branch no GitHub.
8. Se baseline/estado divergir, investigue antes de escrever.

PROJETO
- Repositório: R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada
- Branch: main
- HEAD confirmado antes de T093: d82d7ab2832c2657c45e1c0653c3ab608a15c0c8
- O commit que contém esta versão representa o fechamento documental de T093; confirme o SHA atual antes da próxima escrita.
- SPEC ativa: SPEC-000.
- Estado: T000–T059 + T090–T093 concluídos documentalmente; nenhum runtime novo.

BASELINES
- ASI 4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1
- GRE 0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365
- KB2Ops 0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94

ARTEFATOS CENTRAIS
- matriz-paridade-futura.md
- revisao-wordpress-t090.md
- revisao-simplicidade-t091.md
- revisao-seguranca-t092.md
- revisao-qa-t093.md
- catalogo-testes-regressao.md
- riscos-e-drifts.md
- research.md

INVARIANTES
- WordPress/Elementor fonte editorial.
- zero write derivado em _elementor_data/post_content.
- owner único.
- projection/index/cache/vector não são fonte da verdade.
- persistência confirmada antes de evento.
- dual-write permanente proibido.
- IA sugere; humano decide/persiste pelo owner.
- lexical funciona sem IA/vetor.
- nenhum runtime antes de T097.

T091
- candidato de primeiro slice: Core mínimo + Summary narrativo.
- extractor/Search/Review/Classificação/IA separados por slices.
- sem frameworks genéricos antecipados.

T092
- capability no handler/objeto; nonce não substitui autorização.
- POST para mutação; GET side-effect free.
- IDOR/mass assignment/XSS/SSRF/secrets tratados como NO-GO aplicáveis.
- projection nunca autoriza Search.
- Analytics continua sob B-004.

T093 — QA/REGRESSÃO
Arquivo: revisao-qa-t093.md.
Status: PASS, zero blockers globais.

ESTADOS DE EVIDÊNCIA
PASS | FAIL | NOT_RUN | NOT_CONFIGURED | STALE | N/A | POSTERGADO | WAIVED.
- gate ativo em NOT_RUN/NOT_CONFIGURED/STALE = NO-GO.
- N/A exige justificativa.
- POSTERGADO implementado silenciosamente = NO-GO.

MATRIZ DE EVIDÊNCIA POR SPEC
Cada SPEC deve listar: contrato/gate, classe, cenário, tipo de teste, evidência esperada, estado e artefato/execução.

CANDIDATO SPEC-001 — EVIDÊNCIA MÍNIMA
- G-001 zero write editorial.
- G-020 CRUD Summary + allowlist + omitted/empty + read-after-write + B-006.
- G-070 capability/nonce/GET/IDOR/mass assignment/XSS.
- G-110 browser/a11y/feedback.
- G-130 lifecycle/package quando aplicável.

GOLDEN
- obrigatória antes do primeiro release Search.
- vazia = NOT_CONFIGURED.
- não executada = NOT_RUN.
- stale = NO-GO.
- blocking fail = NO-GO.
- não se aplica ao Summary isolado.

SEARCH FUTURA
G-010/G-050/G-060/Golden/G-070/G-120/G-130 + B-001.
Benchmark com corpus real; projection stale/scope; FULLTEXT/fallback bounded.

FEATURES POSTERGADAS
Não criar harness executável antecipado para Analytics/queue/IA/vector/agentes. Ativar gates/testes junto da capacidade real.

DEFECT TAXONOMY
- P0/P1 = NO-GO.
- P2 = decisão explícita/waiver quando seguro.
- P3 = backlog explícito.

PRÓXIMO PASSO EXATO — T094
Executar Revisão de Produto/Conhecimento.

T094 DEVE
1. validar usuários/jornadas reais.
2. confirmar se Summary isolado entrega valor suficiente para SPEC-001.
3. decidir ordem relativa Summary vs Classificação vs Review vs Search.
4. separar paridade de produto necessária de herança histórica.
5. definir resultado observável do primeiro slice sem depender de Analytics detalhado.
6. validar campos mínimos e UX da jornada.
7. priorizar resolvedor/analista de conhecimento/gestor/admin sem big-bang.
8. classificar findings como MANTER | REORDENAR | SIMPLIFICAR | POSTERGAR | BLOQUEAR.
9. não criar runtime.

CRITÉRIO PARA FECHAR T094
- primeiro slice recomendado por valor e não só simplicidade técnica;
- usuários/resultado/jornada explícitos;
- nenhuma feature entra apenas por legado;
- ordem de próximas SPECs recebe racional;
- T095 recebe candidato objetivo para avaliar blockers.

ORDEM RESTANTE
T094 -> T095 -> T096 -> T097.

REGRA
Repositório/Constituição/Manifesto/SPEC prevalecem sobre memória de chat.
```

## Estado resumido
T050–T059 + T090–T093 concluídos documentalmente. Próximo: T094. Runtime inexistente. SPEC-001 bloqueada até T097.