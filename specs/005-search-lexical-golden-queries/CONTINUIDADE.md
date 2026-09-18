# Continuidade — SPEC-005 Search Lexical e Golden Queries

## 1. Prompt pronto para novo chat

```text
Você é o Orquestrador Principal do projeto Base de Conhecimento com Inteligência Integrada.
Idioma: português do Brasil.
Mantra: Quem não sabe onde está, não sabe para onde quer ir.

ANTES DE ALTERAR
1. Leia AGENTS.md, .specify/PROJECT_MANIFEST.md e .specify/memory/constitution.md.
2. Leia a SPEC-005 completa, ADR-005-001 e os contratos vigentes.
3. Leia docs/DEFINITION-OF-DONE.md e este CONTINUIDADE.md.
4. Confirme branch/HEAD no GitHub e eventuais mudanças posteriores.
5. Investigue divergências antes de implementar; repositório/Constituição prevalecem sobre memória de chat.

REPOSITÓRIO E ESTADO
- Repo: R-RERISON/Base-de-conhecimento-com-intelig-ncia-integrada.
- Branch: spec005-search-lexical-golden-queries.
- Base: main @ 07f877b2978429dc6b31fbe172e6ce8fca7ee634.
- Commit de código revisado: 78d4bbce5135abdedea7c86deb380cb04a569f78.
- A consolidação ambiental/documental T511.2 sucede esse commit; confirmar HEAD remoto.
- SPEC-004 CLOSED/main; SPEC-005 ATIVA/DISCOVERY.
- R-500 PASS/CLOSED; T510 PASS AMBIENTAL; T511.2 PASS LOCAL + PASS AMBIENTAL.
- R-510 OPEN; T513.1 ambiental analisado; T514.2 PASS LOCAL / ambiental pendente; T515/T516 e G-520 NOT_RUN.
- Engine bloqueada até R-500 + R-510 + G-520.

OBJETIVO EXATO
Executar o T514.2 Automated Golden Validator v2 antes de congelar dataset/hash T515 e fechar R-510/T516. Não repetir T511/T513.1 nem discovery ASI. Ambiguidades passam para `AMBIGUOUS_QUARANTINED`; não há escolha manual obrigatória no gate bootstrap.

BASELINE COMPROVADA
- A única pesquisa funcional atual é a Knowledge List com WP_Query s + modified DESC.
- Não existe Search Retrieval canônico, índice/tabela Search ou Golden Suite produtiva aprovada.
- Content Extractor/KD 2.1.0 preservados da SPEC-004.
- R-500: corpus 623, publish 606; gap semântico 91/610; Summary 14/18.
- Superfície inicial decidida: ADMIN-FIRST / Knowledge List.
- Search Document semântico post-level é requisito conceitual; persistência/FULLTEXT dependem de G-520.
- T510 copiou seis expectativas post-level para fixture própria; era a última leitura deliberada do storage ASI.

T511.2 AMBIENTAL — 2026-09-18 21:45:45 UTC
- Build declarado: 0.5.0-r510-t511.2; WordPress 6.9.4; PHP 8.5.10.
- JSON: evidence/r510-t5112-independent-environmental-20260918T214545Z.json.
- SHA-256 do JSON: 3bcf3bd48c4144286fac9de7e648f1b69930907be16cab0a1e7ca5a1c41bc26a.
- t511_read_only_safety_pass=true; legacy_search_independence_pass=true declarado pelo runner.
- Fingerprint before/after igual; corpus 623 -> 623; posts alterados=0; errors=[]; r510_ready=false.
- Seis candidates, 18 medições: admin_current 2/6; admin_relevance 6/6; publish_native 6/6 até max_rank=3.
- Ranks atual/relevância/publish:
  pendrive -> 527: 10/1/1;
  MSTeams -> 579: 1/1/1;
  Windows 11 -> 583: fora Top-20/1/1;
  Termo de assinatura -> 45855: 1/1/1;
  Estrutura -> 36620: 5/2/2;
  SCCM -> 412: 7/1/1.
- Quatro gaps de ordenação; dois já atendidos. Estrutura: post 516 é primeiro no nativo, sem autorização para substituir expected 36620.
- p50/p95 ms: atual 117.0800/151.4130; relevância 117.6209/182.8961; publish 163.2829/182.0939.
- Seis observações/modo, sem repetições: p95 é o máximo da amostra, não SLA.

TRABALHO CONCLUÍDO NESTA ETAPA
- JSON original preservado byte a byte; análise ambiental e limites registrados.
- Revisão offline: 41/41 verificações PASS (fixture/seed, ranks/flags, contagens, percentis e integridade).
- Source scan limitado a runner/seed: zero identificadores técnicos ASI proibidos.
- Pacote de revisão T513/T514 preparado, sem aceite inventado.
- Estado/Tasks/matriz de evidências/Manifesto/Roadmap/README/continuidade alinhados.
- PHP, fixtures, build, UI, schema, hooks, endpoints e dados editoriais inalterados.
- Nenhum novo ZIP; PHP/WordPress não foram executados nesta revisão documental.

ARTEFATOS CENTRAIS
- current-state.md: estado consolidado.
- r510-t5112-environmental-findings-v1.md: análise e limitações.
- evidence/r510-t5112-evidence-review-20260918.json: verificações offline.
- r510-golden-candidate-review-v1.md: tabela e instruções humanas T513/T514.
- fixtures/golden-candidates-legacy-v1.json: seed histórico, human_review_status=pending.
- adr-005-001-zero-runtime-dependency-asi.md: independência canônica.
- tasks.md e evidence-matrix.md: gates.
Todos os caminhos acima são relativos à pasta da SPEC-005.

DECISÕES E INVARIANTES
- Lexical e Golden antes de semantic/vector/IA.
- WordPress é autoridade editorial e de acesso.
- WP_Post.post_content + Core Blocks são destino editorial; Elementor é adapter legado temporário.
- Content Extractor é origem semântica dos derivados; projection é reconstruível.
- modified DESC não é ranking aprovado; WP_Query permanece referência/fallback.
- Não criar tabela antes de decisão G-520 com benchmark/Golden.
- Nenhum item/deep-link, queue, analytics, vetor, IA ou interceptação global do tema nesta etapa.
- Golden vazia != PASS; blocking FAIL = NO-GO; expectativa vem de humano.
- Nenhum dado/persistência/runtime novo foi autorizado pela análise do JSON.

INDEPENDÊNCIA ASI E LIMITES
- ASI 4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1 é referência histórica, não dependência.
- T511.2 usa seed próprio; hashes source_* são proveniência, não set_hash final T515.
- legacy_search_independence_pass é declarativo; WP_Query usa suppress_filters=false.
- O JSON não prova ASI desativado nem ausência de filtros de terceiros. G-585 continua NOT_RUN.
- Antes do RC: ASI ausente/desativado, scan produtivo completo, Search/Golden PASS e rebuild próprio.
- Build 0.5.0-r510-t511.1 SUPERSEDED / NÃO INSTALAR.
- SHA-256 histórico ZIP T511.2: f34cb1bdffb369efdfbdd886d86cd2798835b41829466da278436b002df7ffcb.

PRÓXIMO PASSO EXATO
1. Instalar `0.5.0-r510-t514.2` (SHA-256 `9fb9e20828b1e5162db5fa924ed3b2a3b76d85531d1c61f2847205ebdd00ae2c`).
2. Executar `Base de Conhecimento -> Golden Auto Validator -> Executar validação automática completa e baixar JSON`.
3. Confirmar AUTO_PASS / AMBIGUOUS_QUARANTINED / AUTO_FAIL.
4. Confirmar Technical Challenge Discovery para natural_language, Summary e Elementor gap.
5. Confirmar Diversity PASS e synthetic robustness PASS; typo/alias reais podem permanecer PENDING_TELEMETRY.
6. Se `r510_ready=true`, executar T515: congelar Golden Relevance Set e Technical Challenge Set com hashes/versionamento separados.
7. T516 fecha R-510 explicitamente.
8. G-520 continua obrigatório antes de runtime Search.

CRITÉRIO DE CONCLUSÃO DO PRÓXIMO PASSO
O JSON v2 deve comprovar safety, classificar todos os candidates, produzir/avaliar Technical Challenge e expor real-world enrichment. AUTO_PASS entra no blocking set; AMBIGUOUS_QUARANTINED fica preservado fora do blocking; AUTO_FAIL bloqueia. Nenhuma expectativa é trocada pelo próprio ranking.

ROLLBACK
Reverter a consolidação documental se necessário. Não há alteração editorial ou de runtime para desfazer. Não promover SPEC/branch a main nem fechar gates posteriores por inferência.
```

## 2. Estado resumido para humanos

- Código de referência: `78d4bbce5135abdedea7c86deb380cb04a569f78`.
- Último diagnóstico aceito: T511.2 PASS AMBIENTAL, com limites documentados.
- Próximo gate: T513, seguido de T514.
- Blockers: aceite humano, diversidade e versão/hash finais; G-520 continua aberto.
- Evidências e análise: [T511.2](r510-t5112-environmental-findings-v1.md).
- Revisão pronta: [T513/T514](r510-golden-candidate-review-v1.md).


T513/T514 AUTO VALIDATOR — PASS LOCAL
- build: `0.5.0-r510-t513.1`;
- SHA-256: `e5c10eba2f831584536e0f3e0c2ab50102c87c504424117530c1eafd64cbe0bc`;
- 9/9 unit tests;
- 43/43 PHP lint pré/pós ZIP;
- 42/42 active requires;
- Git blob parity 5/5;
- deterministic rebuild PASS;
- zero dependência técnica ASI;
- zero writer/network no runner;
- execução ambiental: NOT_RUN.


T513.1 AMBIENTAL — 2026-09-18 22:36:54 UTC
- source JSON SHA-256: `9faf205aed1c4024e2105128728444c7735bb12e301b80a643311c7735afd908`;
- safety PASS; 623 -> 623; errors=[];
- 5 AUTO_PASS / 1 REVIEW_REQUIRED / 0 AUTO_FAIL;
- Estrutura: 36620 rank2 vs 516 rank1, ambos score100;
- T514 INCOMPLETE;
- synthetic 16/16 PASS;
- dois defects no validator identificados: D-513-01 product_token overmatch e D-513-02 exact phrase substring overmatch;
- evidence preservada byte-a-byte no repo.

T514.2 — PASS LOCAL
- build `0.5.0-r510-t514.2`;
- SHA-256 `9fb9e20828b1e5162db5fa924ed3b2a3b76d85531d1c61f2847205ebdd00ae2c`;
- ADR-005-002 ativo;
- D-513-01/D-513-02 corrigidos;
- ambiguity quarantine fail-safe;
- Technical Challenge Discovery;
- 14/14 unit;
- 44/44 PHP lint pré/pós ZIP;
- 43/43 active requires;
- Git parity 7/7;
- deterministic rebuild PASS;
- zero ASI runtime identifier / zero writer-network;
- ambiental NOT_RUN.
