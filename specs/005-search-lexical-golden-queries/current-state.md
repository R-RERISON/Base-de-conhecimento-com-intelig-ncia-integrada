# Estado atual — SPEC-005

**ATIVA / DISCOVERY — R-500 PASS/CLOSED; R-510 OPEN.**

**Gate atual:** T513/T514 Automated Golden Validation — execução ambiental pendente.

**Branch:** `spec005-search-lexical-golden-queries`.

**Base:** `main @ 07f877b2978429dc6b31fbe172e6ce8fca7ee634`.

**Código revisado nesta consolidação:** `78d4bbce5135abdedea7c86deb380cb04a569f78`.

## Runtime e fronteiras

Existem Content Extractor/KD 2.1.0, Workspace, Knowledge List e pesquisa administrativa por `WP_Query s`. SPEC-004 permanece CLOSED/main e Visual Contract v2 permanece obrigatório.

A SPEC-005 possui seed próprio e agora um Auto Validator temporário T513/T514. Isso ainda não é uma Golden Suite congelada nem Search Retrieval canônico. Não há índice/tabela Search, Golden management produtivo, query logging, IA/vetor ou superfície pública do novo Search.

**Engine bloqueada até R-500 + R-510 + G-520.** Trabalho permitido nesta fase: evidências, benchmark, dataset, contratos e diagnóstico read-only.

## R-500 — PASS/CLOSED

Evidência: [r500-t502-environmental-20260918T200421Z.json](evidence/r500-t502-environmental-20260918T200421Z.json).

- Corpus: 623; publish: 606.
- Gap semântico: 91/610 (14,92%); gap Summary: 14/18 (77,78%).
- Top-1 admin atual: 33,33%; nativo sem override: 85%.
- Ausências Top-20: admin 8/60; nativo 0/60.
- Fingerprint editorial preservado; erros: 0.

Decisões: **ADMIN-FIRST / Knowledge List**; `modified DESC` rejeitado como ranking de Search; `WP_Query` permanece referência/fallback; Search Document semântico post-level é requisito conceitual. Schema/FULLTEXT dependem de G-520.

## R-510/T510 — PASS AMBIENTAL / histórico encerrado

Evidência: [r510-t510-environmental-20260918T211840Z.json](evidence/r510-t510-environmental-20260918T211840Z.json).

Seis candidates manuais, ativos, post-level, com expected posts existentes/publicados no T510; todos `warning` no legado. Último run legado: PASS 6/6, algorithm 4.5.0. Sem erros e sem mudança de fingerprint.

Fixture própria: [golden-candidates-legacy-v1.json](fixtures/golden-candidates-legacy-v1.json). T510 foi a última leitura deliberada do storage ASI; não repetir discovery.

## R-510/T511.2 — PASS LOCAL + PASS AMBIENTAL

Build: `0.5.0-r510-t511.2`.

SHA-256 histórico do ZIP: `f34cb1bdffb369efdfbdd886d86cd2798835b41829466da278436b002df7ffcb`.

Evidência local anterior: 41/41 PHP lint, 40/40 active requires, rebuild determinístico PASS; legacy discovery fora do ZIP; scan técnico ASI sem hits; T502/T510/Elementor writer OFF e T511 ON. Esses testes não foram reexecutados na consolidação documental.

Execução ambiental fornecida em 2026-09-18 21:45:45 UTC:

- `t511_read_only_safety_pass=true`;
- `legacy_search_independence_pass=true` declarado pelo runner;
- 623 -> 623 posts, zero alterações detectadas no snapshot, zero erros;
- seis candidates medidos em três modos, todos consistentes com a fixture/seed;
- admin atual: 2/6 até max_rank; admin relevance: 6/6; publish native: 6/6;
- quatro gaps de ordenação; dois casos já atendidos;
- `r510_ready=false` preservado.

Análise, percentis e limites: [r510-t5112-environmental-findings-v1.md](r510-t5112-environmental-findings-v1.md). JSON original e revisão offline de 41/41 verificações estão em `evidence/`.

**Não instalar `0.5.0-r510-t511.1`: SUPERSEDED.** T511.2 já foi executado; não há novo ZIP nem necessidade de repetir o baseline para iniciar T513.

## Independência ASI — ADR-005-001

ASI é referência histórica, não dependência. O runner independente consome seed próprio e não consulta diretamente storage/classe/função ASI. Hashes de proveniência não são o futuro set_hash T515.

O JSON não atesta ASI desativado: o flag é declarativo e `WP_Query` executa com filtros do ambiente. G-585 continua NOT_RUN; antes do RC são obrigatórios ASI ausente/desativado, Search/Golden PASS, scan produtivo completo e rebuild próprio.

## T513/T514 — Automated Golden Validation

Contrato: `r510-automated-golden-validation-contract-v1.md`.

Build: `0.5.0-r510-t513.1`.  
SHA-256: `e5c10eba2f831584536e0f3e0c2ab50102c87c504424117530c1eafd64cbe0bc`.

Validação local:
- 9/9 testes unitários PASS;
- um defeito real foi encontrado e corrigido antes da homologação: `MSTeams` não era reconhecido como `product_token`;
- 43/43 PHP lint pré-ZIP e pós-ZIP;
- 42/42 active requires;
- Git blob parity 5/5;
- deterministic rebuild PASS;
- ASI technical dependency scan = 0;
- forbidden write/network calls no auto-runner = 0;
- T502/T510/T511 OFF; Auto Validator ON; Elementor writer OFF.

Política:
- `AUTO_PASS`: confirma continuidade de expectativa já originada de curadoria humana/histórica quando a evidência objetiva é inequívoca;
- `REVIEW_REQUIRED`: somente ambiguidade/inconclusão vai para humano;
- `AUTO_FAIL`: quebra objetiva, NO-GO;
- o validator nunca cria ou troca `expected_post_id`;
- synthetic robustness nunca é tratado como consulta real.

## Próximo passo e blockers

1. instalar/executar somente o build `0.5.0-r510-t513.1`;
2. baixar o JSON do `Golden Auto Validator`;
3. registrar T513 AUTO_PASS/REVIEW_REQUIRED/AUTO_FAIL por candidate;
4. registrar cobertura T514 e synthetic robustness;
5. revisar manualmente **somente** itens `REVIEW_REQUIRED`, se existirem;
6. classes reais ainda ausentes permanecem lacunas explícitas; não inventar consultas;
7. somente depois T515 congela dataset/version/set_hash e T516 fecha R-510.

R-510 permanece OPEN. Nenhum resultado ambiental do Auto Validator foi presumido.