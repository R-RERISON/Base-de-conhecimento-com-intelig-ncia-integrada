# Pesquisa Consolidada — SPEC-000

## Objetivo

Registrar fatos comprovados e decisões documentais do cruzamento das baselines. Hipótese não vira fato sem evidência versionada; provider/feature atual não vira contrato permanente.

## 1. Baselines fixadas

- KB2Ops `0.2.1 @ f2d2aa659240b0c2ee86cebd3cc5bd0c00f9fc94`.
- ASI `4.6.8 @ c0ddff89caad529ce1bcdc645eb795e4a9b187a1`.
- GRE `0.6.0 @ 1120a534d8eb2288460c2c675730deef0d67c365`.

## 2. Estado arquitetural T050–T057/T055

- um conceito canônico = um owner;
- WP/Elementor são fonte editorial;
- projection nunca é fonte da verdade;
- Content Extractor único alimenta downstream;
- persistir -> confirmar -> emitir;
- dual-write permanente proibido;
- Search lexical independente de IA;
- uma Search Retrieval Projection futura `post|item` é a única família própria aprovada no baseline;
- Analytics detalhado e durable queue estão postergados;
- Golden é QA governada e release evidence;
- G-001–G-130 protegem editorial, extractor, domínio, Search, segurança, performance, compatibilidade e lifecycle.

## 3. Evidência das baselines relevante para T058

### KB2Ops

A pré-análise local (`facts/suggestions/checklist/scores`) prova que regras determinísticas podem entregar valor de curadoria sem provider externo. A IA futura deve complementar esse baseline, não substituí-lo.

AI READY histórico permanece `publish + approved + Summary 8/8 + include_ai`.

### ASI

O ASI prova:

- lexical/FULLTEXT/ranking explicável antes de semantic;
- Golden como gate para qualquer alteração de retrieval/ranking;
- item identity/fail-closed;
- IA/vetor apenas como evolução complementar;
- simulação/humano-no-loop como contrato forte de curadoria.

### GRE

O Summary é pequeno, WordPress-first e determinístico. IA pode sugerir conteúdo, mas o owner/persistência continuam em Metadata API e handlers canônicos.

## 4. Skills/agentes internos consultados

### Governança de custo

`.github/skills/ai-cost-governance/SKILL.md` exige volume, provider/modelo, tokens/unidades, custo, budget, NO_CHANGE e interrupção ao atingir limite. Processamento em massa sem estimativa/limite é NO-GO.

### Foundry Integration

`.github/skills/foundry-integration/SKILL.md` exige caso de uso, provider contract, WordPress HTTP API por padrão, secrets seguros, timeout/retry/idempotência, versionamento de prompt/modelo/config, custo e fallback sem IA.

### Hybrid Retrieval

`.github/skills/hybrid-retrieval/SKILL.md` exige medir lexical primeiro, comparar semantic isolado, escolher fusão somente por evidência, Golden, fallback lexical e medição de latência/custo.

### MariaDB Vector

`.github/skills/mariadb-vector/SKILL.md` exige capability real do ambiente, modelo/dimensão/métrica explícitos, store reconstruível, hash/model version, benchmark e fallback. Plugin não depende de VECTOR para instalar/funcionar.

## 5. Evidência pública Microsoft Foundry — snapshot 2026-09-14

Documentação Microsoft analisada demonstra que o Agent Service/File Search opera com vector stores e pipeline gerenciado de ingestão/chunking/embedding. Esses detalhes e defaults são provider-specific e podem mudar.

Conclusão arquitetural: File Search pode ser útil para um agente específico, mas não deve virar Search/RAG canônico do plugin porque introduziria uma segunda pipeline de identidade, chunking, freshness e lifecycle paralela à projection WordPress-first.

Também foi confirmada a disponibilidade de cenários vector/hybrid no ecossistema Azure AI Search/Foundry. Isso prova capacidade tecnológica, não necessidade do projeto.

**Regra:** preço, limite, modelo ou default atual do provider não é contrato arquitetural.

## 6. T058 — decisões

Artefato canônico: `matriz-ia-vetor.md`.

### P0 — determinístico

**MANTER:** extractor, fatos/regras locais, domain stores, lexical/ranking/Golden.

### P1 — IA assistiva

**APROVADO OPCIONAL:**

- Assistente de Classificação;
- Assistente de Summary.

Somente sob ação explícita. Sugestão estruturada/evidenciada; Apply humano separado; provider nunca escreve owner diretamente.

`include_ai` continua elegibilidade de corpus downstream e não deve ser reaproveitado como autorização de assistência editorial.

### P2 — RAG/síntese

**APROVADO OPCIONAL POSTERIOR:** retrieval-first, com fontes e abstenção. Pode nascer sobre lexical; vetor não é requisito.

Quando gate de confiança estiver ativo, corpus produtivo respeita AI READY. Falha de geração degrada para retrieval/evidências.

### P3 — embeddings/semantic/rerank

**POSTERGADO COM GATE.** Reabrir apenas depois de:

- lexical operacional e medido;
- B-001 fechado;
- casos Golden semânticos/lacunas conhecidos;
- hipótese e critério de sucesso pré-definidos;
- custo/latência/fallback/storage avaliados.

Hybrid é preferível a vector-only como hipótese inicial. Lexical permanece fallback.

### P4 — agentes

**POSTERGADO/NEGADO NO BASELINE.** Não há jornada multi-step comprovada que justifique tool orchestration.

## 7. Chunking/embedding

Chunking não vira parser paralelo. Preferir documentos `post|item` estruturais; subchunks só por necessidade de contexto/embedding comprovada.

Embedding futuro deve carregar lineage/fingerprint suficiente para invalidar quando mudarem origem, source hash, extractor, chunk contract, provider/model/deployment/dimensão/configuração.

Re-embed total sem diff/budget é NO-GO.

T058 não aprova vector table, MariaDB VECTOR, Azure AI Search ou vector store de Foundry.

## 8. Foundry

Microsoft Foundry é **provider preferencial candidato**, não owner nem arquitetura do domínio.

O primeiro provider seam só deve nascer junto com um caso real, mínimo e testável. Evitar factory/SDK abstraction antecipada.

WordPress HTTP API é primeira opção quando adequada. Erros precisam ser tipados; retry bounded; secrets não logados; failover/provider switch não silencioso.

Foundry Agent File Search fica fora do core canônico. Se surgir em agente específico, será projection derivada do corpus canônico com lineage/hash/freshness/custo.

## 9. AI Operation Receipt

Toda chamada externa futura deve produzir evidência suficiente para operação/custo:

- tipo de operação;
- provider/model/deployment;
- prompt/config version;
- objeto/contexto;
- input/source fingerprint;
- volume;
- tokens/unidades;
- custo estimado/real quando possível;
- duração;
- status/error class;
- responsável quando aplicável;
- timestamp.

T058 não escolhe storage. Receipt não autoriza Search Analytics/query logging B-004.

## 10. Segurança/privacidade

- contexto enviado ao provider é minimizado/documentado;
- secrets nunca entram em prompt/log/export;
- query enviada para síntese não implica persistência local;
- conteúdo recuperado é dado não confiável, nunca instrução/tool authorization;
- agentes futuros são read-only por default;
- mutação sempre volta ao handler canônico com confirmação humana.

## 11. G-140 após T058

Definidos em `matriz-ia-vetor.md`:

- G-140A independência/degradação;
- G-140B provider/rastreabilidade/data egress;
- G-140C human-in-the-loop;
- G-140D custo/budget/NO_CHANGE;
- G-140E embedding/semantic/hybrid;
- G-140F RAG/síntese;
- G-140G agentes/tools;
- G-140H provider-managed knowledge/File Search.

## 12. Primeiro runtime

Pode nascer com **zero IA externa**. Primeiro estabilizar P0.

Quando IA for autorizada, começar por uma única jornada P1: Classificação assistida **ou** Summary assistido.

## 13. Próximo passo — T059

Consolidar a matriz de paridade futura final com prioridade temporal, owners, primitives, gates, blockers, fallback e cutover, preparando T090–T097.

## Estado

T050–T058 concluídas documentalmente. Nenhum runtime/IA/vector foi autorizado ou criado. Próximo: **T059**.
