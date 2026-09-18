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
- R-510 OPEN; T513/T514 pendentes; T515/T516 e G-520 NOT_RUN.
- Engine bloqueada até R-500 + R-510 + G-520.

OBJETIVO EXATO
Concluir revisão humana T513 e diversidade real T514 antes de congelar dataset/hash T515 e fechar R-510/T516. Não pedir nova instalação do T511 nem repetir discovery ASI.

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
1. Receber aceite/correções humanos da tabela T513: consulta, expected post, max_rank, severity e rationale. Proposta pendente: manter seis IDs/max_rank=3 e considerar blocking. Revisar intenção de Estrutura/36620 versus 516.
2. Em T514, coletar consultas realmente usadas: linguagem natural, variação/erro quando real, aliases se existentes, casos Summary e Elementor. Cada uma exige post/expectativa/justificativa humanos; não inventar casos.
3. Somente após revisão/diversidade, congelar dataset final e set_hash/version T515.
4. Fechar R-510/T516 explicitamente; depois concluir contratos G-520 antes de runtime.

CRITÉRIO DE CONCLUSÃO DO PRÓXIMO PASSO
Todas as seis expectativas têm decisão humana rastreável; casos de diversidade são reais e revisados; lacunas não são mascaradas; nenhuma expectativa é inferida do próprio ranking. T513/T514 continuam pendentes enquanto isso não ocorrer.

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
