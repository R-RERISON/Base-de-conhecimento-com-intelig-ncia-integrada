# R-510/T511.2 — Análise da evidência ambiental

**Estado:** T511.2 PASS AMBIENTAL; R-510 OPEN; T513/T514 pendentes.

**Execução fornecida:** 2026-09-18 21:45:45 UTC.

**Referência de código revisada:** `78d4bbce5135abdedea7c86deb380cb04a569f78`.

**Ambiente declarado:** WordPress 6.9.4, PHP 8.5.10, multisite=false.

**Build declarado:** `0.5.0-r510-t511.2`.

## 1. Evidência e rastreabilidade

- JSON original, preservado byte a byte: [r510-t5112-independent-environmental-20260918T214545Z.json](evidence/r510-t5112-independent-environmental-20260918T214545Z.json).
- Arquivo recebido: `bdc-kb-spec005-r510-golden-baseline-independent-20260918-214545.json` (18.391 bytes).
- SHA-256 do **JSON**: `3bcf3bd48c4144286fac9de7e648f1b69930907be16cab0a1e7ca5a1c41bc26a`.
- Conferência offline: [r510-t5112-evidence-review-20260918.json](evidence/r510-t5112-evidence-review-20260918.json), 41/41 verificações de consistência PASS, 18 pares consulta/modo.
- SHA-256 histórico do **ZIP** T511.2: `f34cb1bdffb369efdfbdd886d86cd2798835b41829466da278436b002df7ffcb`. O JSON não inclui checksum do pacote instalado; sua versão declarada foi conferida contra a evidência local já versionada.

Não houve nova execução WordPress nesta revisão. Os resultados ambientais são os do arquivo fornecido pelo usuário.

## 2. Safety e independência

| Verificação | Resultado |
|---|---|
| `t511_read_only_safety_pass` | `true` |
| `legacy_search_independence_pass` | `true`, declarado pelo runner |
| Fingerprint editorial antes/depois | Igual |
| Posts alterados durante a execução | 0 |
| Corpus antes/depois | 623 / 623 |
| Erros reportados | 0 |
| Candidates medidos | 6, distintos e iguais à fixture própria |
| Schema/write/persistência/hooks globais/network | Sem operação reportada |
| `r510_ready` | `false` |

A origem `spec005_owned_fixture`, a versão `1.0.0-candidate` e o hash de proveniência coincidem com a fixture T510. Os campos id/query/expected/max_rank/severity coincidem com os candidates e com o seed do runner. Nenhuma expectativa foi promovida por esta análise.

O source scan dos dois arquivos independentes (runner e seed) encontrou zero identificadores técnicos ASI proibidos. O scan integral do ZIP permanece a evidência local anterior; não foi reexecutado nesta etapa documental.

**Limite:** `legacy_search_independence_pass` é uma declaração constante do runner. O diagnóstico usa `WP_Query` com `suppress_filters=false` e não inventaria plugins/filtros ativos. Portanto o arquivo não comprova ASI desativado, ausência de interferência de terceiros, rebuild próprio ou G-585 PASS. A independência de acesso direto foi revisada no código; a prova operacional com ASI ausente continua obrigatória antes do RC.

O fingerprint cobre os campos/corpus definidos pelo snapshot, não toda a base de dados. A igualdade é avaliada dentro desta execução; não se exige igualdade com fingerprints de execuções anteriores.

## 3. Relevância observada

Critério medido: expected post até `max_rank=3` histórico. As seis severidades históricas são `warning`; a severidade da nova suíte continua pendente.

| Consulta | Expected post | Admin atual | Admin relevância | Publish nativo | Classificação |
|---|---:|---:|---:|---:|---|
| pendrive | 527 | 10 | 1 | 1 | Gap de ordenação |
| MSTeams | 579 | 1 | 1 | 1 | Já atende |
| Windows 11 | 583 | Fora do Top-20 | 1 | 1 | Gap de ordenação |
| Termo de assinatura | 45855 | 1 | 1 | 1 | Já atende |
| Estrutura | 36620 | 5 | 2 | 2 | Gap de ordenação |
| SCCM | 412 | 7 | 1 | 1 | Gap de ordenação |

`actual_rank=0` significa ausência na amostra de até 20 resultados, não posição zero nem prova de ausência em toda a base. O JSON exporta somente os primeiros 10 IDs de cada amostra.

| Modo | Atende max_rank | Expected no Top-20 | p50 (ms) | p95 (ms) |
|---|---:|---:|---:|---:|
| `admin_current` | 2/6 (33,33%) | 5/6 | 117,0800 | 151,4130 |
| `admin_relevance` | 6/6 (100%) | 6/6 | 117,6209 | 182,8961 |
| `publish_native` | 6/6 (100%) | 6/6 | 163,2829 | 182,0939 |

Os quatro desvios desaparecem ao retirar `modified DESC` mantendo o mesmo escopo administrativo. Isso reforça a classificação de gap de ordenação e a decisão R-500 de não promover a Knowledge List atual a engine de Search.

Para “Estrutura”, os dois modos de relevância colocam 516 em primeiro e 36620 em segundo. O max_rank histórico é atendido, mas o post oficial e a intenção da consulta precisam de revisão humana. Não substituir expected por 516 nem endurecer max_rank para 1 com base no ranking observado.

Nenhuma das seis consultas expõe falha de retrieval nos modos de relevância. Isso **não elimina** os gaps de Summary/Content Extractor/Elementor já medidos em R-500: esse seed não testa adequadamente essas classes. Search Document permanece requisito conceitual; forma de persistência depende de G-520.

## 4. Performance: alcance da medição

- Uma medição por consulta/modo, seis observações por modo, execução em ordem fixa, sem warm-up/repetições documentados.
- Percentis seguem nearest-rank (`ceil(p*n)-1`) do runner; com n=6, p95 é o maior valor observado.
- Delta p95 admin_relevance vs admin_current: +31,4831 ms nesta execução.
- Runtime total do diagnóstico: 3.509 ms; pico de memória PHP: 28 MiB. O total inclui snapshots e demais etapas, não apenas retrieval.

Esses dados são descritivos e não constituem SLA, teste de carga, benchmark isolado de caches/filtros ou prova de regressão de latência. Não comparar o delta diretamente com os 60 probes do R-500 como se fossem a mesma amostra.

## 5. Decisão e próximo gate

1. Registrar T511.2 PASS AMBIENTAL para o baseline read-only fornecido.
2. Manter R-500 PASS/CLOSED e R-510 OPEN.
3. Revisar os seis casos no [pacote T513/T514](r510-golden-candidate-review-v1.md).
4. Completar diversidade com consultas reais e respostas oficiais revisadas; não fabricar casos para aumentar contagem.
5. Gerar versão/hash final em T515 somente após aceite de T513/T514; o hash de proveniência atual não substitui esse passo.
6. Fechar R-510 em T516 somente com evidência humana e dataset final. G-520 continua necessário antes de runtime.

Nenhuma mudança de ranking, tabela, runtime, UI, endpoint, hook ou persistência foi implementada nesta etapa. Não há novo ZIP para instalar. Rollback documental: reverter o commit desta consolidação; nenhum rollback editorial é necessário.
