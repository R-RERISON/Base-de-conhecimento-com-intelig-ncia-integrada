# Riscos, Drifts e Dívidas — SPEC-000

> Estado após T093.

## Riscos anteriores
D-001–D-008 e B-001–B-007 permanecem contextuais. T090–T092 adicionaram guardrails de WordPress-first, simplicidade e segurança. T093 adiciona riscos de evidência/regressão.

## Novos riscos T093

### X-064 — PASS vazio
**Risco:** ausência de casos executados ser apresentada como sucesso.  
**Tratamento:** estados explícitos; NOT_RUN/NOT_CONFIGURED nunca equivalem a PASS.

### X-065 — N/A usado para esconder gap
**Risco:** gate aplicável ser omitido por conveniência.  
**Tratamento:** N/A exige justificativa versionada.

### X-066 — evidência stale
**Risco:** teste antigo permanecer “verde” após mudança de commit/build/dataset/ranker/extractor.  
**Tratamento:** evidência deve referenciar estado material testado; mudança relevante invalida.

### X-067 — happy path sem cenários negativos
**Risco:** fluxo funciona para admin feliz, mas capability/nonce/IDOR/mass assignment/XSS falham.  
**Tratamento:** cenários negativos obrigatórios quando a superfície existir.

### X-068 — UI considerada validada por unit test
**Risco:** foco, escaping, feedback, bypass e renderização real não são comprovados.  
**Tratamento:** browser/manual acceptance nos fluxos críticos.

### X-069 — benchmark fictício
**Risco:** meta de performance sem corpus/configuração real.  
**Tratamento:** benchmark só em caminho crítico, com corpus e ambiente registrados.

### X-070 — pacote diferente do código testado
**Risco:** working tree passa, artefato distribuído diverge.  
**Tratamento:** release futuro testa o ZIP/build distribuído e registra checksum.

### X-071 — harness prematuro para feature postergada
**Risco:** construir infraestrutura de teste cara para IA/vector/queue inexistentes.  
**Tratamento:** gates documentais agora; suite executável junto da capacidade real.

### X-072 — cobertura percentual substituir contratos
**Risco:** alta cobertura de linhas com gaps comportamentais críticos.  
**Tratamento:** cobertura por risco/contrato; nenhum percentual global inventado.

## NO-GO QA futuro
- gate MUST/CONDICIONAL ativo em FAIL/NOT_RUN/NOT_CONFIGURED/STALE;
- Golden blocking falhando;
- browser acceptance obrigatório pendente;
- migration/cutover sem rollback quando aplicável;
- pacote testado diferente do publicado;
- P0/P1 aberto sem decisão formal permitida;
- contrato crítico sem evidência.

## Estado do primeiro slice sugerido
Summary continua testável sem Search/IA. B-006 permanece gate próprio do write composto.

## Próximo passo
T094 — Produto/Conhecimento.