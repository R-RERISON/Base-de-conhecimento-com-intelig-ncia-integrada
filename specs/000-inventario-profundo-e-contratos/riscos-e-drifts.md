# Riscos, Drifts e Dívidas — SPEC-000

> Estado após **T092**. Fontes centrais: `matriz-paridade-futura.md`, `revisao-wordpress-t090.md`, `revisao-simplicidade-t091.md` e `revisao-seguranca-t092.md`.

## 1. Drifts e blockers preservados

D-001–D-008 permanecem válidos. B-001–B-007 continuam contextuais:

- B-001 — Search/RAG/embedding produtivos;
- B-002 — profiling/cutover classificatório;
- B-003 — retirada de plugins/aliases/adapters;
- B-004 — Analytics/query logging;
- B-005 — deep-link público de item;
- B-006 — write path composto definitivo;
- B-007 — durable queue/async indexing.

T092 não criou blocker global.

## 2. Riscos ativos anteriores

Continuam relevantes: extração parcial, projection stale, fallback lexical ilimitado, Golden stale/vazia, adapter permanente, evento antes da persistência, supercoleta Analytics, queue prematura, activation pesada, benchmark fictício, UI fragmentada, LLM no caminho crítico, provider lock-in, prompt injection, secrets em logs, SSRF e big-bang.

## 3. Riscos de simplicidade T091 preservados

- X-048 fundação antes de produto;
- X-049 extractor sem consumidor;
- X-050 DS como projeto paralelo;
- X-051 classificação big-bang;
- X-052 abstração sem segundo caso;
- X-053 Search Knowledge antecipado;
- X-054 item/deep-link antecipado;
- X-055 Golden UI excessiva;
- X-056 Operations Center sem operação;
- X-057 provider seam genérico antecipado.

## 4. Novos riscos/guardrails T092

### X-058 — nonce tratado como autorização

**Risco:** CSRF protegido, mas usuário sem privilégio consegue mutar objeto.  
**Tratamento:** capability no handler e no objeto; nonce é controle separado.

### X-059 — IDOR por `post_id`

**Risco:** trocar ID no request para ler/alterar outro post.  
**Tratamento:** normalizar ID, reler objeto e aplicar capability/scope server-side.

### X-060 — mass assignment de metadata

**Risco:** payload injeta meta/taxonomia fora do owner.  
**Tratamento:** allowlist estrita e validação por campo; campos inesperados não são persistidos.

### X-061 — XSS armazenado/refletido

**Risco:** meta/termo/query/provider renderizados sem escaping.  
**Tratamento:** escaping tardio e contextual; HTML só por allowlist explícita.

### X-062 — projection autorizar acesso

**Risco:** índice stale expõe conteúdo privado/despublicado.  
**Tratamento:** WordPress canônico revalida status/scope/capability antes da exposição.

### X-063 — SSRF por endpoint configurável

**Risco:** provider URL arbitrária alcança host interno/metadata service.  
**Tratamento:** endpoint preferencialmente fixo; URL variável exige HTTPS, host allowlist, HTTP API segura e validação de redirects.

### X-064 — secret/data egress em logs

**Risco:** key, payload, conteúdo ou PII vazam em diagnostics/exports.  
**Tratamento:** minimização, mascaramento e proibição de secrets em repo/log/export/prompt.

### X-065 — replay confundido com nonce one-time

**Risco:** duplicate submit/ação repetida produz efeito indevido.  
**Tratamento:** expected-state/hash/idempotência de domínio quando necessária; nonce não é exactly-once.

### X-066 — ação destrutiva acoplada a lifecycle

**Risco:** activation/upgrade/uninstall remove dados ou inicia operação pesada.  
**Tratamento:** lifecycle mínimo; purge separado, autorizado e deliberado.

### X-067 — exposição pública acidental de classificação

**Risco:** taxonomy interna cria archive/rewrite/REST sem produto pedir.  
**Tratamento:** exposição fail-closed e reabertura só por jornada pública aprovada.

### X-068 — telemetria oportunista

**Risco:** query text, IP/UA/identidade coletados “para futuro”.  
**Tratamento:** B-004; zero persistência por default antes de finalidade/retention/access.

### X-069 — prompt/tool authority via conteúdo

**Risco:** conteúdo recuperado instrui modelo a usar tool ou alterar owner.  
**Tratamento:** conteúdo é dado; allowlist/least privilege; humano + handler canônico para mutação.

## 5. Resultado T092

- threat model: PASS;
- blocker global novo: 0;
- candidato Summary: compatível com segurança;
- vulnerabilidade implementada: N/A, runtime inexistente;
- capacidade postergada reaberta: 0;
- runtime criado: não.

## 6. Próximo passo — T093

QA/Regressão deve converter os riscos/NO-GO de T092 em testes negativos e evidências obrigatórias por slice, mantendo Golden/benchmark apenas quando Search for afetada.

## Status

**T092 concluída documentalmente.** Próximo: **T093**. SPEC-001 permanece bloqueada até T097.