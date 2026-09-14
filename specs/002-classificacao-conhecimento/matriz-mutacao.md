# Matriz de Mutação — SPEC-002

## Fase S001 — profiling

| Superfície | Read | Write | Regra |
|---|---:|---:|---|
| `WP_Post` | SIM, apenas filtro de escopo | NÃO | não alterar título/conteúdo/status |
| postmeta histórico classificatório | SIM | NÃO | profiling somente |
| `_elementor_data` | NÃO | NÃO | fora do profiling |
| taxonomias | NÃO nesta fase | NÃO | primitive ainda não decidida |
| options/transients | NÃO | NÃO | relatório não persiste |
| schema/tabelas | NÃO | NÃO | proibido |
| arquivos | runtime do package | NÃO no servidor | JSON é resposta HTTP |

## Fase futura de runtime

Somente após DoR:

- write permitido exclusivamente ao owner canônico aprovado;
- allowlist de conceitos exata;
- capability por objeto;
- POST + nonce;
- diff mínimo;
- read-after-write;
- sem dual-write permanente;
- nenhum efeito editorial;
- compatibilidade legada é leitura/bridge explicitamente versionada.
