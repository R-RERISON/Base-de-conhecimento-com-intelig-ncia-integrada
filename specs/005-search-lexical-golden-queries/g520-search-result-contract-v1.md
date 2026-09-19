# G-520 / T523 — Search Result Contract v1

**Status:** FROZEN  
**Result contract version:** `search-result-v1.0.0`

## SearchResponse

```json
{
  "state": "success",
  "retrieval_mode": "projection_like",
  "query": {
    "original": "Windows 11",
    "normalized": "windows 11",
    "tokens": ["windows", "11"]
  },
  "versions": {
    "normalizer": "search-normalizer-v1.0.0",
    "document": "search-document-v1.0.0",
    "algorithm": "lexical-ranker-v1.0.0"
  },
  "count": 1,
  "results": []
}
```

## Estados

- `success`;
- `zero_results`;
- `invalid_query`;
- `degraded`;
- `technical_error`.

Zero results nunca é technical error.

## Retrieval mode

- `projection_like`;
- `wordpress_fallback`.

FULLTEXT não existe no v1.

### Fallback

Em `wordpress_fallback`:
- usar relevância nativa do `WP_Query`, sem `modified DESC`;
- `score=null`;
- `matched_signals=["wordpress_native_relevance"]`;
- response state obrigatoriamente `degraded`;
- o fallback não pode ser usado para declarar Golden/G-550 PASS.

## SearchResult

Campos mínimos:
- `post_id`;
- `title` canônico carregado do WordPress;
- `official_url` canônica;
- `rank`;
- `score` numérico em projection mode; `null` em wordpress_fallback;
- `matched_signals[]`;
- `source_kind`;
- `document_state`;
- `visibility_revalidated=true`.

Não retornar texto privado, Summary, fragments ou snippets apenas porque existem na Projection.

## Revalidação

Antes de retornar:
- post existe;
- post_type autorizado;
- status permitido no contexto;
- capability efetiva do objeto passa.

Projection nunca decide autorização.

## Degraded

Resposta é `degraded` quando:
- Projection não está ready e fallback foi usado; ou
- um erro parcial foi recuperável sem produzir resposta incorreta; ou
- documento relevante está marcado degraded.

A resposta pode conter resultados e ainda ser degraded.

## Error contract

Erro técnico deve possuir:
- `error_code` estável;
- mensagem segura;
- nenhuma query SQL;
- nenhum stack trace;
- nenhum conteúdo editorial bruto.

## Observabilidade

Pode incluir métricas técnicas agregadas de execução em diagnóstico autorizado:
- runtime_ms;
- candidate_count;
- retrieval_mode.

Não registrar identidade, IP, sessão nem query de usuário nesta SPEC.
